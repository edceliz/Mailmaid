<?php
    /**
     * Controller for settings branch.
     * 
     * @package Mailmaid
     * @subpackage controllers
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\controllers;

    class SettingsController extends Controller {
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
            $this->model = $this->getModel('Settings', $this->classes);
        }

        /**
         * Renders the settings view with parameters from index action of model.
         * 
         * @param array[string|int] $extras
         */
        function index($extras = array()) {
            $params = $this->model->index($extras);
            $this->getView('Settings', $params)->render();
        }
        
        /**
         * Calls the update function of the model.
         */
        function update() {
            $this->model->update();
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
