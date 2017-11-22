<?php
    /**
     * Controller for login branch.
     * 
     * @package Mailmaid
     * @subpackage controllers
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\controllers;

    class LoginController extends Controller {
        /**
         * Contains an array of classes.
         * 
         * @var array[classes]
         */
        private $classes;
        
        /**
         * Contains an instance of the account model.
         * 
         * @var 'N'model 'N' is the name of controller
         */
        private $model;
        
        /**
         * Puts content on global $classes and checks login.
         * 
         * @param array[classes] $params
         */
        function __construct($params) {
            $this->classes = $params;
            $this->model = $this->getModel('Login', $this->classes);
        }

        /**
         * Renders the login view with parameters from index action of model.
         * 
         * If login token is sent, call the login function instead.
         * 
         * @param array[string|int] $extras
         */
        function index($extras = array()) {
            if (isset($_POST['token'])) {
                $this->login();
            }

            $params = $this->model->index($extras);
            $this->getView('Login', $params)->render();
        }

        /**
         * Calls the login function of the model.
         */
        private function login() {
            $this->model->login();
        }
    }
