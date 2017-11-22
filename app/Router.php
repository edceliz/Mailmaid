<?php
    /**
     * Parses request URL into runnable action from controller
     * 
     * @package Mailmaid
     * @subpackage app
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app;

    class Router {
        /**
         * Contains the location of the controller compatible with namespacing.
         * HomeController as default value.
         * 
         * @var string
         */
        private $controller = 'app\controllers\\HomeController';
        
        /**
         * Contains the action to be ran using the controller
         * index function is called by default.
         * 
         * @var string
         */
        private $action = 'index';
        
        /**
         * An array that will contain excess parameters from URL.
         * 
         * @var string
         */
        private $params = array();
        
        /**
         * Checks the URL if a controller or more is set.
         * Gets the parsed URL and assign the values into controller, action and params.
         */
        function __construct() {
            $url = $_GET['url'] ?: 'home';
            if ($url != 'home') {
                $url = $this->parseURL($url);
                $this->setController($url[0]);
                unset($url[0]);
                $this->setAction(empty($url) ? 'index' : $url[1]);
                unset($url[1]);
                $this->setParams(empty($url) ? [] : $url);
            }
        }
        
        /**
         * Splits the URL with '/' as marker.
         * 
         * @param string $url
         * @return string[]
         */
        private function parseURL($url) {
            return explode('/', filter_var(rtrim($url, '/'), FILTER_SANITIZE_URL));
        }
        
        /**
         * Assigns new value to the global $controller
         * 
         * Checks if selected controller exists and assigns it.
         * If controller doesn't exist, redirect to home controller.
         * 
         * @param string $controller
         */
        private function setController($controller) {
            $controller = ucfirst($controller).'Controller';
            if (file_exists(__DIR__ . '/controllers/' . $controller . '.php')) {
                $this->controller = 'app\controllers\\' . $controller;
            } else {
                Registry::get('SystemLogger')->log($controller . ' doesn\'t exist' , 2);
                header("location: {$_SERVER['SERVER_NAME']}/mailmaid");
            }
        }
        
        /**
         * Assigns new value to the global $action
         * 
         * Checks if action method exists on the selected controller.
         * If method doesn't exist, 'error' is set instead.
         * 
         * @param string $action
         */
        private function setAction($action) {
            if (method_exists($this->controller, $action)) {
                $this->action = $action;
            } else {
                $this->action = 'error';
            }
        }
        
        /**
         * Pushes the remaining values of $params into the global $params.
         * 
         * @param string[] $params
         */
        private function setParams($params) {
            array_push($this->params, $params);
        }
        
        /**
         * Calls the action from controller with the array parameters.
         * 
         * @param string[]|array() $params
         */
        function start($params = array()) {
            call_user_func_array([new $this->controller($params), $this->action], $this->params);
        }
    }
