<?php
    /**
     * Model for managing setup
     *
     * @package Mailmaid
     * @subpackage models
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\models;

    class SetupModel {
        /**
         * Contains an array of dependencies.
         *
         * @var array[classes]
         */
        private $classes = array();
        
        /**
         * Contains an array of key->value pair for view's usage.
         *
         * @var array[any]
         */
        private $result = array();

        /**
         * Inject dependencies
         *
         * @param array[classes] $classes - Contains dependencies
         */
        function __construct($classes) {
            $this->classes = $classes;
        }
        
        /**
         * Checks if already setup and if not, generate setup form.
         * @param  array[any] $extras
         * @return array[any]
         */
        function index($extras) {
            if ($this->classes['db']->checkConnection()) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid");
                die();
            }
            $this->result['token'] = $this->classes['auth']->generateFormToken('setupToken');
            $this->checkParamsForError($extras);
            return $this->result;
        }
        
        /**
         * Check if params have error and register it to result array.
         *
         * @param  array[any] $params
         */
        private function checkParamsForError($params) {
            foreach($params as $k => $v) {
                if ($v == 'error') {
                    $this->result[$v] = $params[$k + 1];
                    break;
                }
            }
        }
        
        /**
         * Validates and records connection to database. If valid, proceeds to setup of database.
         */
        function validate() {
            if (!isset($_POST['token'])) {
                header('location: /mailmaid');
                $this->classes['auth']->removeFormToken('setupToken');
                exit();
            }
            
            if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['db'])) {
                header('location: /mailmaid/setup/error/1');
                $this->classes['auth']->removeFormToken('setupToken');
                exit();
            }
            
            if ($this->classes['auth']->validateFormToken('setupToken', $_POST['token'])) {
                $this->classes['auth']->removeFormToken('setupToken');
                $this->writeConfig();
            }
            
            if ($this->classes['db']->checkConnection()) {
                $this->setupDatabase();
            } else {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/setup/index/error/2");
                die();
            }
        }
        
        /**
         * Writes configuration to database and settings
         */
        private function writeConfig() {
            $account = array('user'=>$_POST['username'], 'pass'=>$_POST['password'], 'db'=>$_POST['db']);
            $account = json_encode($account);
            file_put_contents('../app/db.json', $account);
            $this->classes['db']->configConnection();
            $settings = array('mailRate'=>40, 'executionMode'=>'parallel', 'businessName'=>'Mailmaid', 'serverName'=>$_SERVER['SERVER_NAME']);
            file_put_contents('../app/settings.json', json_encode($settings));
        }
        
        /**
         * Prepares database for use.
         */
        private function setupDatabase() {
            $db = $this->classes['db']->getConnection();
            $query = "
                CREATE TABLE users (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `username` VARCHAR(32) NOT NULL,
                    `password` VARCHAR(128) NOT NULL,
                    `level` TINYINT NOT NULL,
                    PRIMARY KEY(id)
                );
                CREATE TABLE subscribers (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `emailAddress` VARCHAR(256) NOT NULL,
                    PRIMARY KEY(id)
                );
                CREATE TABLE lists (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(128) NOT NULL,
                    `description` VARCHAR(512) NOT NULL,
                    `dateCreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `status` TINYINT DEFAULT 1 NOT NULL,
                    PRIMARY KEY(id)
                );
                INSERT INTO lists (
                    name, 
                    description,
                    status
                ) VALUES(
                    'All Subscribers',
                    'A list containing all subscribers you have from different forms.',
                    0
                );
                CREATE TABLE listsubs (
                    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `listId` INT NOT NULL,
                    `subscriberId` INT NOT NULL,
                    `latestUpdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    `status` TINYINT DEFAULT 1 NOT NULL,
                    PRIMARY KEY(id)
                );
                CREATE TABLE links (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `direction` VARCHAR(512) NOT NULL,
                    `code` VARCHAR(128) NOT NULL,
                    `campaignId` INT NOT NULL,
                    PRIMARY KEY(id)
                );
                CREATE TABLE linkclicks (
                    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `linkId` INT NOT NULL,
                    `dateCreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `ipAddress` INT UNSIGNED NOT NULL,
                    `subscriberId` INT,
                    `code` VARCHAR(8) NOT NULL,
                    `count` INT NOT NULL DEFAULT 1,
                    PRIMARY KEY(id)
                );
                CREATE TABLE campaign (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(128) NOT NULL,
                    `subject` VARCHAR(128) NOT NULL,
                    `contentId` INT NOT NULL,
                    `listId` INT NOT NULL,
                    `dateCreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `dateModified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    `status` TINYINT NOT NULL DEFAULT 1,
                    PRIMARY KEY(id)
                );
                CREATE TABLE content (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `content` TEXT NOT NULL,
                    PRIMARY KEY(id)
                );
                CREATE TABLE mailqueue (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `campaignId` INT NOT NULL,
                    `subscriberId` INT NOT NULL,
                    `status` TINYINT NOT NULL DEFAULT 0,
                    `dateModified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY(id)
                );
                CREATE TABLE settings (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `mailRate` INT NOT NULL,
                    `executionMode` VARCHAR(128) NOT NULL,
                    `businessName` VARCHAR(128) NOT NULL,
                    `businessURL` VARCHAR(128) NOT NULL,
                    PRIMARY KEY(id)
                );
                ";
            $db->multi_query($query);
            header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/setup/account");
        }
    }
