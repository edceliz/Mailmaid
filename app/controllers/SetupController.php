<?php
    /**
     * Controller for setup branch.
     * 
     * @package Mailmaid
     * @subpackage controllers
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\controllers;

    class SetupController extends Controller {
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
            $this->model = $this->getModel('Setup', $this->classes);
        }
        
        /**
         * Renders the setup view with parameters from index action of model.
         * 
         * @param array[string|int] $extras
         */
        function index($extras = array()) {
            $params = $this->model->index($extras);
            $this->getView('Setup', $params)->render();
        }
        
        /**
         * Calls the validate function of the model.
         */
        function db() {
            $this->model->validate();
        }
        
        /**
         * Renders the first account view with parameters from the account 
         * model's register action.
         * 
         * Uses different model to increase code reusability.
         * 
         * @param array[string|int] $extras
         */
        function account($extras = array()) {
            $model = $this->getModel('Account', $this->classes);

            if (!$model->checkSetup()) {
                header('location: /mailmaid');
                exit();
            }

            if (isset($_POST['token'])) {
                $model->create();
            }
            $params = $model->register($extras);
            $this->getView('FirstAccount', $params)->render();
        }
    }
