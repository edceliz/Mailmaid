<?php
    /**
     * All requests are redirected here
     * 
     * This file creates an instance of the bootstrap class and start it.
     * 
     * @package Mailmaid
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    session_start();
    require_once(__DIR__ . '/../app/autoloader.php');
    $boot = new app\Bootstrap;
    $boot->start();
