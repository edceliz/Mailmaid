<?php
    /**
     * Prepares router and needed classes for application
     * 
     * Uses registry class to set and get classes.
     * Router is initiated and can be started.
     * 
     * Example usage:
     * $b = new Bootstrap();
     * $b->start();
     * 
     * @package Mailmaid
     * @subpackage app
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app;

    class Bootstrap {
        /**
         * Holder for router
         * 
         * @var Router
         */
        private $router;
        
        /**
         * Initiates all the classes that will be used later on the application.
         */
        function __construct() {
            error_reporting(E_ALL);
            Registry::set('SystemLogger', new Logger('SYSTEM', 'system'));
            Registry::set('WriteConnection', new Database('write'));
            Registry::set('ReadConnection', new Database('read'));
            Registry::set('Authentication', new Authentication());
            $this->router = new Router();
        }
        
        /**
         * Calls the start function for router.
         */
        function start() {
            $this->router->start(array(
                'db' => Registry::get('WriteConnection'),
                'auth' => Registry::get('Authentication')
            ));
        }
    }

