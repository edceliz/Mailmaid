<?php
    /**
     * Controller for campaign branch.
     * 
     * @package Mailmaid
     * @subpackage controllers
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\controllers;

    class CampaignController extends Controller {
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
            $this->model = $this->getModel('Campaign', $this->classes);
        }
        
        /**
         * Renders the campaign view with parameters from index action of model.
         * 
         * @param array[string|int] $extras
         */
        function index($extras = array()) {
            $this->checkLogin();
            $params = $this->model->index($extras);
            $this->getView('Campaign', $params)->render();
        }
        
        function add($extras = array()) {
            $this->checkLogin();
            if (isset($_POST['token'])) {
                $this->model->createCampaign();
            }
            $params = $this->model->add($extras);
            $this->getView('CampaignAdd', $params)->render();
        }
        
        function edit($extras = array()) {
            $this->checkLogin();
            if (isset($_POST['token'])) {
                if (isset($_POST['save'])) {
                    $this->model->save();
                } else if (isset($_POST['operate'])) {
                    $this->model->operate();
                } else if (isset($_POST['delete'])) {
                    $this->model->delete();
                }
            }
            $params = $this->model->edit($extras);
            $this->getView('CampaignEdit', $params)->render();
        }
        
        function upload() {
            $this->checkLogin();
            $this->model->uploadImage();
        }
        
        function trace() {
            $this->checkLogin();
            if (isset($_POST['link'])) {
                $this->model->traceLink();
            }
            header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign");
            die();
        }
        
        function untrace() {
            $this->checkLogin();
            if (isset($_POST['id'])) {
                $this->model->untraceLink();
            }
            header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign");
            die();
        }
        
        function link($params = array()) {
            if (!isset($params[2])) {
                header("location: http://{$_SERVER['SERVER_NAME']}");
                die();
            }
            $this->model->link($params[2]);
        }
        
        function graph() {
            $this->checkLogin();
            $campaignId = $_POST['id'];
            switch ($_POST['type']) {
                case 1:
                    $this->model->campaignEngagement24Hours($campaignId);
                    break;
                case 2:
                    $this->model->campaignEngagement7Days($campaignId);
                    break;
                case 3:
                    $this->model->campaignEngagementYear($campaignId);
                    break;
                case 4:
                    $this->model->mailSent24Hours($campaignId, false);
                    break;
                case 5:
                    $this->model->mailSent7Days($campaignId, false);
                    break;
                case 6:
                    $this->model->mailSentYear($campaignId, false);
                    break;
            }
        }
        
        function webmail($params = array()) {
            if (count($params) != 3) {
                header("location: http://{$_SERVER['SERVER_NAME']}");
                die();
            }
            $params = $this->model->webmail(intval($params[2]), $params[3], $params[4]);
            $this->getView('CampaignWebView', $params)->render();
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
