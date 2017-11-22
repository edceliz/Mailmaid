<?php
    /**
     * Used to log events on a file
     * 
     * Example usage:
     * $logger = new Logger('TEST', 'test');
     * $logger->log('Sample log', 3);
     * 
     * @package Mailmaid
     * @subpackage app
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app;

    class Logger {
        /**
         * Used to identify the message on the file.
         * 
         * @var string
         */
        private $tag;
        
        /**
         * Contains location where the log files are saved.
         * 
         * @var string
         */
        private $fileName = '../tmp/logs/';
        
        /**
         * Contains eight (8) levels of alert for message use.
         * 
         * @var string[]
         */
        private $alertLevel = array(
            'DEBUG',
            'INFO',
            'NOTICE',
            'WARNING',
            'ERROR',
            'CRITICAL',
            'ALERT',
            'EMERGENCY'
        );
        
        /**
         * Used to define current time.
         * 
         * @var DateTime
         */
        private $clock;
        
        /**
         * Contains e-mail address which clients will see upon encountering a specific error.
         * 
         * @var string
         */
        private $email = 'webmaster@localhost';
        
        /**
         * Assigns a tag name for the logger and which file to use.
         * 
         * @param string $tag|'SYSTEM'
         * @param string $fileName
         */
        function __construct($tag = 'SYSTEM', $fileName) {
            $this->tag = $tag;
            $this->fileName .= $fileName . '.log';
            $this->clock = new \DateTime();
        }
        
        /**
         * Appends message to the log file with time and its alert level.
         * 
         * Alert level is used to define the severity of the error.
         * Higher than two (2) alert level will stop the application run time.
         * 
         * @param string $message
         * @param int $alertLevel
         */
        function log($message, $alertLevel) {
            $message = '[' . $this->alertLevel[$alertLevel] . '][' . $this->tag . '] ' . $this->clock->format('F d, Y h:i:s A') . ': ' . $message . ' ' . PHP_EOL;
            file_put_contents($this->fileName, $message, FILE_APPEND);
            if ($alertLevel > 2) {
                exit('Encountered a fatal error! Please contact the web administrator at ' . $this->email . ' (' . $this->clock->format('F d, Y h:i:s A') . ')');
            }
        }
    }
