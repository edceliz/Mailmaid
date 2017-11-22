<?php
    /**
     * An inheritable class used to create a view.
     * 
     * @package Mailmaid
     * @subpackage views
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\views;

    class View {
        /**
         * Contains the title of the HTML page.
         * 
         * @var string
         */
        private $title;
        
        /**
         * Contains an array of CSS files.
         * 
         * @var array[string]
         */
        private $css;
        
        /**
         * Contains an array of JavaScript files to be included on head tag.
         * 
         * @var array[string]
         */
        private $topScript;
        
        /**
         * Contains an array of JavaScript files to be included before the body tag closes.
         * 
         * @var array[string]
         */
        private $bottomScript;
        
        /**
         * Assigns values for the includable files and title of the HTML page.
         * 
         * @param string $title
         * @param array[string] $css
         * @param array[string] $topScript
         * @param array[string] $bottomScript
         */
        function __construct($title, $css = array(), $topScript = array(), $bottomScript = array()) {
            $this->title = $title;
            $this->cssBuilder($css);
            $this->scriptBuilder($topScript, 'top');
            $this->scriptBuilder($bottomScript, 'bottom');
        }
        
        /**
         * Creates link tag for each CSS file included on the view.
         * 
         * @param array[string] $css
         */
        private function cssBuilder($css = array()) {
            foreach($css as $v) {
                $this->css .= "
                    <link rel='stylesheet' type='text/css' href='http://{$_SERVER['SERVER_NAME']}/mailmaid/css/{$v}'>
                ";
            }
        }
        
        /**
         * Creates script tag for each JavaScript files depending on the selected position.
         * 
         * @param array[string] $script
         * @param array[string] $position
         */
        private function scriptBuilder($script = array(), $position = array()) {
            if (!empty($script)) {
                $build = '';
                foreach($script as $v) {
                    $build .= "
                        <script src='http://{$_SERVER['SERVER_NAME']}/mailmaid/js/{$v}'></script>
                    ";   
                }
                if ($position == 'top') {
                    $this->topScript = $build;
                } else {
                    $this->bottomScript = $build;
                }
            } 
        }
        
        /**
         * Prints the complete head tag for the HTML page.
         */
        private function getHeader() {
            echo "
                <!DOCTYPE html>
                <html lang='en'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <meta http-equiv='X-UA-Compatible' content='ie=edge'>
                    <title>{$this->title}</title>
                    <link rel='stylesheet' href='http://{$_SERVER['SERVER_NAME']}/mailmaid/css/header.css'>
                    <link rel='stylesheet' href='http://{$_SERVER['SERVER_NAME']}/mailmaid/css/font-awesome.min.css'>
                    {$this->css}
                    {$this->topScript}
                </head>
                <body>
            ";
        }
        
        /**
         * Prints the navigation sidebar for the HTML page.
         */
        function nav() {
            $settings = $_SESSION['level'] == 1 ? 'Settings' : ''; 
            echo "
                <nav id='nav'>
                    <a href='javascript:void(0)' onclick='closeNav()'>&times;</a>
                    <a href='/mailmaid/home'>Dashboard</a>
                    <a href='/mailmaid/campaign'>Campaigns</a>
                    <a href='/mailmaid/subscriber'>Subscribers</a>
                    <a href='/mailmaid/account'>Accounts</a>
                    <a href='/mailmaid/settings'>$settings</a>
                    <a href='/mailmaid/logout'>Logout</a>
                </nav>
                <span id='navButton' class='navButton' onclick='openNav()'>☰</span>
                <div id='body' class='body'>
                <p id='page-title' class='page-title'>{$this->title}</p>
            ";
        }
        
        /**
         * An overridable function used to output main content of the HTML page.
         */
        function content() {}
        
        /**
         * Prints the bottom part of the HTML page.
         */
        private function getFooter() {
            echo "
                {$this->bottomScript}
                </div>
                </body>
                </html>
            ";
        }
        
        /**
         * Calls function in order to print the whole HTML page.
         */
        function render() {
            $this->getHeader();
            $this->nav();
            $this->content();
            $this->getFooter();
        }
    }
