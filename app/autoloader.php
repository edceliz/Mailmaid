<?php
    /**
     * An includable file used to set the application autoloader.
     * 
     * @package Mailmaid
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */

    /**
     * Includes the desired file into the project.
     * 
     * @param class $class
     * @return boolean
     */
    function appLoader($class) {
        $namespace = str_replace('\\', '/', __NAMESPACE__);
        $class = str_replace('\\', '/', $class);
        $class = __DIR__ . '/../' . $namespace . '/' . $class . '.php';
        if (file_exists($class)) {
            include($class);
            return true;
        }
        return false;
    }

    spl_autoload_register('appLoader');
