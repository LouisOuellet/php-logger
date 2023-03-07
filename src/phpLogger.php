<?php

//Declaring namespace
namespace LaswitchTech\phpLogger;

class phpLogger {

  private $logFile;

  public function __construct($logFile = "log.log"){
    $this->logFile = $logFile;
  }

  public function log($message, $level = "INFO"){
    $timestamp = date("Y-m-d H:i:s");
    $logLine = "[$timestamp] [$level] $message" . PHP_EOL;
    file_put_contents($this->logFile, $logLine, FILE_APPEND);
  }
}
