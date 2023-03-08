![GitHub repo logo](/dist/img/logo.png)

# phpLogger
![License](https://img.shields.io/github/license/LouisOuellet/php-logger?style=for-the-badge)
![GitHub repo size](https://img.shields.io/github/repo-size/LouisOuellet/php-logger?style=for-the-badge&logo=github)
![GitHub top language](https://img.shields.io/github/languages/top/LouisOuellet/php-logger?style=for-the-badge)
![Version](https://img.shields.io/github/v/release/LouisOuellet/php-logger?label=Version&style=for-the-badge)

## Description
The `phpLogger` class is a PHP package for logging messages to files. It supports logging at different levels of severity, including DEBUG, INFO, SUCCESS, WARNING, and ERROR. It also provides support for rotating log files, which can help manage file sizes and ensure that logs don't become too large.

## Features
  - Supports logging at different severity levels: DEBUG, INFO, SUCCESS, WARNING, and ERROR.
  - Rotates log files to manage file sizes.
  - Provides the ability to add, set, and list log files.
  - Supports logging to multiple files.

## Why you might need it?
Logging is an essential part of debugging and monitoring software applications. It can help developers identify issues and bugs, as well as provide insight into how users are interacting with the software. The `phpLogger` class provides a simple and flexible way to log messages to files in PHP applications. It can help developers quickly set up logging functionality and manage log files, making it an essential tool for any PHP project.

## Can I use this?
Sure!

## License
This software is distributed under the [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.en.html) license. Please read [LICENSE](LICENSE) for information on the software availability and distribution.

## Requirements
* PHP >= 8.2

## Security
Please disclose any vulnerabilities found responsibly – report security issues to the maintainers privately.

## Installation
Using Composer:
```sh
composer require laswitchtech/php-logger
```

## How do I use it?
### Usage
#### Initiate phpLogger
To use `phpLogger`, simply include the phpLogger.php file and create a new instance of the `phpLogger` class. By default, it will create a log file named "default.log" in the same directory as the phpLogger.php file.

```php

//Import phpLogger class into the global namespace
//These must be at the top of your script, not inside a function
use LaswitchTech\phpLogger\phpLogger;

//Load Composer's autoloader
require 'vendor/autoload.php';

//Initiate phpLogger
$phpLogger = new phpLogger();
```

#### Logging Messages
To log a message, use the log method of the `phpLogger` class. By default, it will log the message at the INFO level to the "default.log" file.

```php
$phpLogger->log("This is a log message");
```

You can also specify a log level and log file name:

```php
$phpLogger->log("This is a debug message", phpLogger::LEVEL_DEBUG, "debug");
```

#### Customizing Log Files
You can add, switch between, and rotate log files using the add, set, and rotate methods of the `phpLogger` class.

```php
// Add a new log file named "error.log"
$phpLogger->add("error", "error.log");

// Switch to the "error" log file
$phpLogger->set("error");

// Rotate the current log file
$phpLogger->rotate();
```

#### List Log Files
You can also list all of the log files using the list method:

```php
$files = $phpLogger->list();
print_r($files);
```

#### Customizing Log Message Format
You can customize the format of the logged message by subclassing `phpLogger` and overriding the log method. For example, the following code logs the message with the date and time, log level, calling function or method, file name, and line number:

```php
class MyLogger extends phpLogger {
  public function log($message, $level = self::LEVEL_INFO, $logName = null){
    $trace = debug_backtrace();
    $caller = isset($trace[1]) ? $trace[1] : $trace[0];
    $file = isset($caller['file']) ? $caller['file'] : '';
    $line = isset($caller['line']) ? $caller['line'] : '';
    $class = isset($caller['class']) && count($trace) > 1 ? $caller['class'] : '';
    $function = isset($caller['function']) && count($trace) > 1 ? $caller['function'] : '';

    $classTrace = '';
    if($class != ''){
      $classTrace .= $class . '::';
    }
    if($function != ''){
      $classTrace .= $function;
    }
    if($classTrace != ''){
      $classTrace = " [$classTrace]";
    }

    $timestamp = date("Y-m-d H:i:s");
    $logLine = "[$timestamp] [$level]$classTrace ($file:$line) $message"

    if(is_file($logFile)){
      $today = new DateTime();
      $logDate = new DateTime();
      $logDate->setTimestamp(filemtime($logFile));
      if($today->format("Y-m-d") > $logDate->format("Y-m-d")){
        $fileName = $logFile . '.' . strtotime($logDate->format("Y-m-d"));
        rename($logFile, $fileName);
      }
    }

    file_put_contents($logFile, $logLine, FILE_APPEND);
    
    if(defined('STDIN')){
      echo $logLine;
    }
  }
}
```
