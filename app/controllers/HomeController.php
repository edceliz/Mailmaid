<?php
    /**
     * Controller for home branch.
     * 
     * @package Mailmaid
     * @subpackage controllers
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\controllers;
    
    class HomeController extends Controller {
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
            $this->model = $this->getModel('Home', $this->classes);
        }
        
        /**
         * Renders the home view with parameters from index action of model.
         * 
         * @param array[string|int] $extras
         */
        function index($extras = array()) {
            $this->getView('Home')->render();
        }
        
        function graph() {
            if (!isset($_POST['type'])) {
                header("location: http://{$_SERVER['SERVER_NAME']}");
                die();
            }
            $campaignModel = $this->getModel('Campaign', $this->classes);
            $subscriberModel = $this->getModel('Subscriber', $this->classes);
            
            switch ($_POST['type']) {
                case 1:
                    $this->model->audience24Hours($subscriberModel);
                    break;
                case 2:
                    $this->model->audience7Days($subscriberModel);
                    break;
                case 3:
                    $this->model->audienceYear($subscriberModel);
                    break;
                case 4:
                    $this->model->campaignEngagement24Hours();
                    break;
                case 5:
                    $this->model->campaignEngagement7Days();
                    break;
                case 6:
                    $this->model->campaignEngagementYear();
                    break;
                case 7:
                    $this->model->mailSent24Hours($campaignModel);
                    break;
                case 8:
                    $this->model->mailSent7Days($campaignModel);
                    break;
                case 9:
                    $this->model->mailSentYear($campaignModel);
                    break;
            }
        }
        
        /**
         * Checks if user is logged in.
         */
        private function checkLogin() {
            if (!$this->classes['auth']->checkLogin()) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/login");
                exit();
            }
        }
    }
