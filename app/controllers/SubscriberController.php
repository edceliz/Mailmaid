<?php
    /**
     * Controller for subscriber branch.
     * 
     * @package Mailmaid
     * @subpackage controllers
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\controllers;

    class SubscriberController extends Controller {
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
            $this->model = $this->getModel('Subscriber', $this->classes);
        }

        /**
         * Renders the subscriber view with parameters from index action of model.
         * 
         * @param array[string|int] $extras
         */
        function index($extras = array()) {
            $params = $this->model->index($extras);
            $this->getView('Subscriber', $params)->render();
        }
        
        /**
         * Renders the subscriber add view with parameters from add action of model.
         * 
         * @param array[string|int] $extras
         */
        function add($extras = array()) {
            $this->checkLogin();
            $params = $this->model->add($extras);
            $this->getView('SubscriberAdd', $params)->render();
        }
        
        /**
         * Renders the subscriber list view with parameters from view action of model.
         * 
         * @param array[string|int] $extras
         */
        function view($extras = array()) {
            $this->checkLogin();
            $params = $this->model->view($extras);
            $this->getView('SubscriberList', $params)->render();
        }
        
        /**
         * Calls the update function of the model.
         * 
         * @param array[string|int] $extras
         */
        function update($extras = array()) {
            $this->checkLogin();
            $this->model->update();
        }
        
        function subscribe() {
            if (!empty($_POST)) {
                $this->model->subscribe();
            }
        }
        
        function unsubscribe($extras = array()) {
            if (count($extras) == 2) {
                $this->model->unsubscribe($extras);
            }
            header("location: http://{$_SERVER['SERVER_NAME']}");
            die();
        }
        
        function graph() {
            $listId = $_POST['id'];
            switch ($_POST['type']) {
                case 1:
                    $this->model->graphDay($listId, false);
                    break;
                case 2:
                    $this->model->graphWeek($listId, false);
                    break;
                case 3:
                    $this->model->graphMonth($listId, false);
                    break;
                case 4:
                    $this->model->graphAll($listId);
                    break;
            }
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
