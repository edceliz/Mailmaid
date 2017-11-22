<?php
    /**
     * View for first account registration form.
     * 
     * @package Mailmaid
     * @subpackage views
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\views;

    class FirstAccountView extends View {
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
            $this->params['agent'] = "<option value='2'>Agent</option>";
        }

        /**
         * Override nav function to do nothing if registering for first account
         * 
         * Display the sidebar if it is not the first account registration.
         * 
         * @return null
         */
        function nav() {
            if ($this->params['setupMode']) {
                $this->params['agent'] = '';
                return;
            }
        }

        /**
         * Contains the HTML content to be displayed on the user.
         */
        function content() {
            echo "
                <form action='http://{$_SERVER['SERVER_NAME']}/mailmaid/setup/account' method='post'>
                    <p>Register</p>
                    <input name='token' type='hidden' value='{$this->params['registerToken']}'>
                    <label for='username'>Username</label>
                    <input id='username' name='username' type='text' placeholder='Username' required maxlength='32'>
                    <label for='password'>Password</label>
                    <input id='password' name='password' type='password' placeholder='Password' required>
                    <label for='role'>Role</label>
                    <select id='role' name='role'>
                        <option value='1'>Administrator</option>
                        {$this->params['agent']}
                    </select>
                    <input type='submit' value='Register'>
                </form>
            ";
        }
    }
