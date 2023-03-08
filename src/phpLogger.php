<?php

//Declaring namespace
namespace LaswitchTech\phpLogger;

// Import some php Classes
use DateTime;

class phpLogger {

  const LEVEL_DEBUG = 'DEBUG';
  const LEVEL_INFO = 'INFO';
  const LEVEL_SUCCESS = 'SUCCESS';
  const LEVEL_WARNING = 'WARNING';
  const LEVEL_ERROR = 'ERROR';

  private $logFiles = [];
  private $logFile = 'default';
  private $logRotation = true;

  public function __construct($logFile = null){
    if($logFile != null){
      if(is_string($logFile)){
        $this->add($this->logFile, $logFile);
      } else {
        if(is_array($logFile)){
          foreach($logFile as $name => $file){
            $this->add($name, $file);
          }
          $this->logFile = array_key_first($this->logFiles);
        } else {
          throw new Exception("Could not configure phpLogger");
        }
      }
    } else {
      $this->add($this->logFile, $this->logFile . '.log');
    }
  }

  public function rotate($bool = true){
    if(is_bool($bool)){
      $this->logRotation = $bool;
    } else{
      throw new Exception("Argument must be boolean");
    }
  }

  public function add($logName, $logFile){
    if(is_string($logName) && is_string($logFile)){
      if(!isset($this->logFiles[$logName])){
        $this->logFiles[$logName] = $logFile;
      } else {
        throw new Exception("This log file already exist");
      }
    } else {
      throw new Exception("Both arguments must be strings");
    }
  }

  public function set($logName){
    if(is_string($logName)){
      if(isset($this->logFiles[$logName])){
        $this->logFile = $logName;
      } else {
        throw new Exception("Could not find the requested log");
      }
    } else {
      throw new Exception("Argument must be string");
    }
  }

  public function get(){
    return $this->logFile;
  }

  public function list(){
    return $this->logFiles;
  }

  public function log($message, $level = self::LEVEL_INFO, $logName = null){

    // Validate log level
    if(!in_array($level,['DEBUG','INFO','SUCCESS','WARNING','ERROR'])){
      $level = self::LEVEL_DEBUG;
    }

    // Sanitize message
    if(!is_string($message)){
      $message = '[JSON] ' . PHP_EOL . json_encode($message, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    // Identify the log file
    if($logName == null || !isset($this->logFiles[$logName])){
      $logName = 'default';
    }
    $logFile = $this->logFiles[$logName];

    // Backtrace
    $trace = debug_backtrace();
    $caller = isset($trace[1]) ? $trace[1] : $trace[0];
    $file = isset($caller['file']) ? $caller['file'] : '';
    $line = isset($caller['line']) ? $caller['line'] : '';
    $class = isset($caller['class']) && count($trace) > 1 ? $caller['class'] : '';
    $function = isset($caller['function']) && count($trace) > 1 ? $caller['function'] : '';

    // Timestamp
    $timestamp = date("Y-m-d H:i:s");

    // Format Line
    $logLine = "[$timestamp] [$level] [$class::$function] ($file:$line) $message" . PHP_EOL;

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

    // Write Line to file
    file_put_contents($logFile, $logLine, FILE_APPEND);

    // Write Line to prompt
    if(defined('STDIN')){
      echo $logLine;
    }
  }
}
