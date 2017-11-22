<?php
    /**
     * Model for managing logins
     *
     * @package Mailmaid
     * @subpackage models
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\models;

    class LoginModel {
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
         * Checks if application is already setup and if it is, prepare login form
         *
         * @param  array[any] $extras
         * @return array[any]
         */
        function index($extras) {
            if (!$this->classes['db']->checkConnection()) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/setup");
                exit();
            }
            
            if (!$this->classes['auth']->checkFirstUser()) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/setup/account");
                exit();
            }
            
            if ($this->classes['auth']->checkLogin()) {
                header('location: /mailmaid');
                return;
            }

            $this->result['loginToken'] = $this->classes['auth']->generateFormToken('loginToken');
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
         * Logs user in if valid.
         */
        function login() {
            $auth = $this->classes['auth'];
            if (!$auth->validateFormToken('loginToken', $_POST['token'])) {
                header('location: /mailmaid');
                return;
            }

            if ($auth->login($_POST['username'], $_POST['password'])) {
                header('location: /mailmaid');
            }
        }
    }
