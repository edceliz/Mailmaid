<?php
    /**
     * Controller for logout branch.
     * 
     * @package Mailmaid
     * @subpackage controllers
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\controllers;

    class LogoutController extends Controller {
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
            $this->checkLogin();
        }

        /**
         * Calls the authentication's logout function and redirects user to
         * home controller.
         */
        function index() {
            $this->classes['auth']->logout();
            header('location: /mailmaid');
            exit();
        }
        
        /**
         * Checks if user is logged in.
         */
        private function checkLogin() {
            if (!$this->classes['auth']->checkLogin()) {
                header('location: /mailmaid');
                exit();
            }
        }
    }
