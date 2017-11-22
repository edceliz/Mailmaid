<?php
    /**
     * View for account edit
     * 
     * @package Mailmaid
     * @subpackage views
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\views;

    class AccountEditView extends View {
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
            parent::__construct('Mailmaid | Edit', array('form.css'), array(), array('menu.js'));
            $this->params = $params;
        }
        
        /**
         * Contains the HTML content to be displayed on the user.
         */
        function content() {
            echo "
                <form action='' method='post'>
                    <input name='token' type='hidden' value='{$this->params['editToken']}'>
                    <input name='editId' type='hidden' value='{$this->params['editId']}'>
                    <label for='username'>Username</label>
                    <input name='username' placeholder='Username' id='username' type='text' value='{$this->params['username']}'>
                    <label for='password'>Password</label>
                    <input name='password' placeholder='Replace password (Optional)' id='password' type='password'>
                    <label for='role'>Role</label>
                    <select name='role' id='role' {$this->params['rolePower']}>
                        <option value='1' {$this->params['isAdmin']}>Admin</option>
                        <option value='2' {$this->params['isAgent']}>Agent</option>
                    </select>
                    <input type='submit' value='Edit Account'>
                </form>
            ";
        }
    }
