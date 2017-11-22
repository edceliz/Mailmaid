<?php
    /**
     * View for account register
     * 
     * @package Mailmaid
     * @subpackage views
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\views;

    class AccountRegisterView extends View {
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
            parent::__construct('Mailmaid | Register', array('form.css'), array(), array('menu.js'));
            $this->params = $params;
        }

        /**
         * Contains the HTML content to be displayed on the user.
         */
        function content() {
            echo "
                <form action='http://{$_SERVER['SERVER_NAME']}/mailmaid/account/register' method='post'>
                    <input name='token' type='hidden' value='{$this->params['registerToken']}'>
                    <label for='username'>Username</label>
                    <input name='username' placeholder='Username' id='username' type='text' required>
                    <label for='password'>Password</label>
                    <input name='password' placeholder='Password' id='password' type='password' required>
                    <label for='role'>Role</label>
                    <select name='role' id='role'>
                        <option value='1'>Admin</option>
                        <option value='2'>Agent</option>
                    </select>
                    <input type='submit' value='Add Account'>
                </form>
            ";
        }
    }
