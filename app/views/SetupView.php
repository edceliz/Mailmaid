<?php
    /**
     * View for database setup form.
     * 
     * @package Mailmaid
     * @subpackage views
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\views;

    class SetupView extends View {
        /**
         * Contains data from the model of the view.
         * 
         * @var array[string|int] 
         */
        private $params;

        /**
         * Constructs parent class and sets value for the global $params.
         * 
         * @param array[string|int] $params
         */
        function __construct($params = array()) {
            parent::__construct('Mailmaid | Setup', array('login.css', 'form.css'));
            $this->params = $params;
        }
        
        /**
         * Override nav function to do nothing.
         */
        function nav() {}
        
        /**
         * Contains the HTML content to be displayed on the user.
         */
        function content() {
            echo "
                <form action='http://{$_SERVER['SERVER_NAME']}/mailmaid/setup/db' method='post'>
                    <p>Setup MySQLI</p>
            ";
            
            if (isset($this->params['error'])) {
                echo '<p>';
                switch($this->params['error']) {
                    case 2:
                        echo 'Incorrect credentials!';
                }
                echo '</p>';
            }
            
            echo "
                    <input name='token' type='hidden' value='{$this->params['token']}'>
                    <label for='username'>Username</label>
                    <input id='username' name='username' type='text' placeholder='Username' required>
                    <label for='password'>Password</label>
                    <input id='password' name='password' type='password' placeholder='Password' required>
                    <label for='db'>Database Name</label>
                    <input id='db' name='db' type='text' placeholder='Database Name' required>
                    <input type='submit' value='Verify'>
                </form>
            ";
        }
    }
