<?php
    /**
     * Controller for account branch.
     * 
     * @package Mailmaid
     * @subpackage controllers
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\controllers;

    class AccountController extends Controller {
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
            $this->model = $this->getModel('Account', $this->classes);
        }
        
        /**
         * Renders the account view with parameters from index action of model.
         * 
         * @param array[string|int] $extras
         */
        function index($extras = array()) {
            $params = $this->model->index($extras);
            $this->getView('Account', $params)->render();
        }
        
        /**
         * Renders account register view with parameters from register action of model.
         * 
         * @param array[string|int] $extras
         */
        function register($extras = array()) {
            if (isset($_POST['token'])) {
                $this->model->create();
            }
            $params = $this->model->register($extras);
            $this->getView('AccountRegister', $params)->render();
        }
        
        /**
         * Renders account edit view with parameters from edit action of model.
         * 
         * If an edit token is submitted, the update action of model is executed instead.
         * 
         * @param array[string|int] $extras
         */
        function edit($extras = array()) {
            if (isset($_POST['token'])) {
                $this->model->update();
            }
            $params = $this->model->edit($extras);
            $this->getView('AccountEdit', $params)->render();
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
