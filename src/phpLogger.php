<?php

//Declaring namespace
namespace LaswitchTech\phpLogger;

// Importing Dependencies
use DateTime;
use Exception;

class phpLogger {

  const LEVEL_DEBUG = 'DEBUG';
  const LEVEL_INFO = 'INFO';
  const LEVEL_SUCCESS = 'SUCCESS';
  const LEVEL_WARNING = 'WARNING';
  const LEVEL_ERROR = 'ERROR';

  private $logFiles = []; // An array to hold all added log files
  private $logFile = 'default'; // The name of the current log file to write logs to
  private $logRotation = false; // Whether log file rotation is enabled or not
  private $logIP = false; // Whether to log ip addresses or not

  /**
   * Create a new phpLogger instance.
   *
   * @param  string|array|null  $logFile
   * @return void
   * @throws Exception
   */
  public function __construct($logFile = null){
    if($logFile != null){
      if(is_string($logFile)){

        // If $logFile is a string, add it as a default log file with the name 'default'
        $this->add($this->logFile, $logFile);
      } else {
        if(is_array($logFile)){

          // If $logFile is an array, add each key-value pair as a log file
          foreach($logFile as $name => $file){
            $this->add($name, $file);
          }

          // Set the current log file to the first added log file
          $this->logFile = array_key_first($this->logFiles);
        } else {

          // If $logFile is neither a string nor an array, throw an exception
          throw new Exception("Could not configure phpLogger. Invalid argument.");
        }
      }
    } else {

      // If $logFile is null, add a default log file with the name 'default' and the filename 'default.log'
      $this->add($this->logFile, $this->logFile . '.log');
    }
  }

  /**
   * Enable or disable log file options.
   *
   * @param  string  $option
   * @param  bool  $bool
   * @return void
   * @throws Exception
   */
  public function config($option, $bool){
    if(is_string($option)){
      if(is_bool($bool)){
        switch($option){
          case"rotation":
            $this->logRotation = $bool;
            break;
          case"ip":
            $this->logIP = $bool;
            break;
          default:
            throw new Exception("unable to configure $option.");
            break;
        }
      } else{
        throw new Exception("2nd argument must be a boolean.");
      }
    } else{
      throw new Exception("1st argument must be as string.");
    }
  }

  /**
   * Retrieve the client IP address.
   *
   * @return string $ipaddress
   */
	public function ip(){
	  $ipaddress = '';
	  if(getenv('HTTP_CLIENT_IP')){
	    $ipaddress = getenv('HTTP_CLIENT_IP');
	  } elseif(getenv('HTTP_X_FORWARDED_FOR')){
	    $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	  } elseif(getenv('HTTP_X_FORWARDED')){
	    $ipaddress = getenv('HTTP_X_FORWARDED');
	  } elseif(getenv('HTTP_FORWARDED_FOR')){
	    $ipaddress = getenv('HTTP_FORWARDED_FOR');
	  } elseif(getenv('HTTP_FORWARDED')){
	    $ipaddress = getenv('HTTP_FORWARDED');
	  } elseif(getenv('REMOTE_ADDR')){
	    $ipaddress = getenv('REMOTE_ADDR');
    } elseif(defined('STDIN')){
      $ipaddress = 'LOCALHOST';
	  } else {
	    $ipaddress = 'UNKNOWN';
		}
    if(in_array($ipaddress,['127.0.0.1','127.0.1.1','::1'])){ $ipaddress = 'LOCALHOST'; }
	  return $ipaddress;
	}

  /**
   * Add a new log file.
   *
   * @param  string  $logName
   * @param  string  $logFile
   * @return void
   * @throws Exception
   */
  public function add($logName, $logFile){
    if(is_string($logName) && is_string($logFile)){
      if(!isset($this->logFiles[$logName])){
        $this->logFiles[$logName] = $logFile;
      } else {
        throw new Exception("This log file already exist.");
      }
    } else {
      throw new Exception("Both arguments must be strings.");
    }
  }

  /**
   * Set the current log file.
   *
   * @param  string  $logName
   * @return void
   * @throws Exception
   */
  public function set($logName){
    if(is_string($logName)){
      if(isset($this->logFiles[$logName])){
        $this->logFile = $logName;
      } else {
        throw new Exception("Could not find the requested log.");
      }
    } else {
      throw new Exception("Argument must be string.");
    }
  }

  /**
   * Get the name of the current log file.
   *
   * @return string
   */
  public function get(){
    return $this->logFile;
  }

  /**
   * Get an array of all added log files.
   *
   * @return array
   */
  public function list(){
    return $this->logFiles;
  }

  /**
   * Write a log message to the current log file.
   *
   * @param  mixed  $message
   * @param  string  $level
   * @param  string|null  $logName
   * @return void
   */
  public function log($message, $level = self::LEVEL_INFO, $logName = null){

    // Validate log level
    if(!in_array($level,['DEBUG','INFO','SUCCESS','WARNING','ERROR'])){
      // If the specified log level is invalid, set it to 'DEBUG'
      $level = self::LEVEL_DEBUG;
    }

    // Sanitize message
    if(!is_string($message)){
      // If the message is not a string, encode it as a JSON string and prepend '[JSON]'
      $message = '[JSON] ' . PHP_EOL . json_encode($message, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    // Identify the log file
    if($logName == null || !isset($this->logFiles[$logName])){
      // If the log name is not specified or is not found in the added log files, use the default log file
      $logName = $this->logFile;
    }
    $logFile = $this->logFiles[$logName];

    // Backtrace
    $trace = debug_backtrace();
    $caller = count($trace) > 1 ? $trace[1] : $trace[0];
    $file = isset($caller['file']) ? $caller['file'] : '';
    $line = isset($caller['line']) ? $caller['line'] : '';
    $caller = count($trace) > 1 ? end($trace) : current($trace);
    $class = isset($caller['class']) && count($trace) > 1 ? $caller['class'] : '';
    $function = isset($caller['function']) && count($trace) > 1 ? $caller['function'] : '';

    // Validate trace
    $classTrace = '';
    if($class != ''){
      $classTrace .= $class . '::';
    }
    if($function != ''){
      $classTrace .= $function;
    }
    if($classTrace != ''){
      $classTrace = "[$classTrace]";
    }

    // Timestamp
    $timestamp = date("Y-m-d H:i:s");

    // Check if IPs should be logged
    $ip = '';
    if($this->logIP){
      $ip = '[' . $this->ip() . ']';
    }

    // Format Line
    $logLine = "[$timestamp]";
    $logLine .= $ip;
    $logLine .= "[$level]";
    $logLine .= $classTrace;
    $logLine .= "($file:$line)";
    $logLine .= " $message" . PHP_EOL;

    // Check if logFile should be rotated
    if(is_file($logFile)){

      // Get dates
      $today = new DateTime();
      $logDate = new DateTime();
      $logDate->setTimestamp(filemtime($logFile));

      // Evaluate Dates
      if($today->format("Y-m-d") > $logDate->format("Y-m-d")){
        $fileName = $logFile . '.' . strtotime($logDate->format("Y-m-d"));
        rename($logFile, $fileName);
      }
    }

    // Create the directory recursively
    if(!is_dir(dirname($logFile))){
      mkdir(dirname($logFile), 0777, true);
    }

    // Write Line to logFile
    file_put_contents($logFile, $logLine, FILE_APPEND);

    // Write Line to prompt
    if(defined('STDIN')){
      echo $logLine;
    }
  }

  /**
   * Write a level DEBUG log message to the current log file.
   *
   * @param  mixed  $message
   * @param  string|null  $logName
   * @return void
   */
  public function debug($message, $logName = null){
    return $this->log($message, $level = self::LEVEL_DEBUG, $logName);
  }

  /**
   * Write a level INFO log message to the current log file.
   *
   * @param  mixed  $message
   * @param  string|null  $logName
   * @return void
   */
  public function info($message, $logName = null){
    return $this->log($message, $level = self::LEVEL_INFO, $logName);
  }

  /**
   * Write a level SUCCESS log message to the current log file.
   *
   * @param  mixed  $message
   * @param  string|null  $logName
   * @return void
   */
  public function success($message, $logName = null){
    return $this->log($message, $level = self::LEVEL_SUCCESS, $logName);
  }

  /**
   * Write a level WARNING log message to the current log file.
   *
   * @param  mixed  $message
   * @param  string|null  $logName
   * @return void
   */
  public function warning($message, $logName = null){
    return $this->log($message, $level = self::LEVEL_WARNING, $logName);
  }

  /**
   * Write a level ERROR log message to the current log file.
   *
   * @param  mixed  $message
   * @param  string|null  $logName
   * @return void
   */
  public function error($message, $logName = null){
    return $this->log($message, $level = self::LEVEL_ERROR, $logName);
  }
}
