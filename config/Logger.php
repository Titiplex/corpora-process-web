<?php
class Logger {
    private string $logFile;

    public function __construct($filePath) {
        $this->logFile = $filePath;
    }

    public function log($level, $message): void
    {
        $date = date('Y-m-d H:i:s');
        $entry = "[$date] [$level] $message" . PHP_EOL;
        file_put_contents($this->logFile, $entry, FILE_APPEND);
    }

    public function task($task) : void {
        $this->log("TASK", "Task executed: " . $task);
    }

    public function logError($level, $message): void {
        $this->log("ERROR-".$level, $message);
    }

    public function info($message): void
    {
        $this->log("INFO", $message);
    }

    public function var_dump($message): void {
        $this->log("VAR_DUMP", $message);
    }

    public function debug($message): void
    {
        $this->log("DEBUG", $message);
    }

    public function error($message): void
    {
        $this->log("ERROR", $message);
    }
}
