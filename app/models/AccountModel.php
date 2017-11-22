<?php
    /**
     * Model for accounts
     *
     * @package Mailmaid
     * @subpackage models
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\models;

    class AccountModel {
        /**
         * Contains an array of dependencies.
         *
         * @var array[classes]
         */
        private $classes = array();

        /**
         * Contains an array of key->value pair for view's usage.
         *
         * @var array[any]
         */
        private $result = array();

        /**
         * Inject dependencies
         *
         * @param array[classes] $classes - Contains dependencies
         */
        function __construct($classes) {
            $this->classes = $classes;
        }

        /**
         * Fetches list of accounts
         *
         * @param  array[any] $params
         * @return array[any]
         */
        function index($params) {
            if ($this->classes['auth']->checkLevel() == 2) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/account/edit/{$this->classes['auth']->getID()}");
                exit();
            }
            $db = $this->classes['db'];
            $db->select('users', array('id', 'username', 'level'));
            $db->where('level', '<', 3);
            $db->by('ORDER', 'id', 'ASC');
            $result = $db->execute();
            $db->reset();
            $this->result['accounts'] = array();
            while($row = $result->fetch_assoc()) {
                $role = $row['level'] == '1' ? 'Admin' : 'Agent';
                array_push($this->result['accounts'], array($row['id'], $row['username'], $role));
            }
            return $this->result;
        }

        /**
         * Prepares a form for creating account
         *
         * @return array[any]
         */
        function register() {
            if ($this->checkSetup()) {
                $this->result['setupMode'] = true;
            } else {
                $this->result['setupMode'] = false;
            }
            $this->result['registerToken'] = $this->classes['auth']->generateFormToken('registerToken');
            return $this->result;
        }

        /**
         * Checks if the application is already setup
         *
         * @return boolean
         */
        function checkSetup() {
            $db = $this->classes['db'];
            $db->select('users', array('id'));
            $result = $db->execute();
            foreach($result as $v) {
                return false;
            }
            return true;
        }

        /**
         * Creates an account from submitted request.
         */
        function create() {
            if (!isset($_POST['username']) || !isset($_POST['password'])) {
                header('location: /mailmaid');
                return;
            }

            if (!$this->classes['auth']->validateFormToken('registerToken', $_POST['token'])) {
                header('location: /mailmaid');
                return;
            }
            
            $this->classes['auth']->register($_POST['username'], $_POST['password'], $_POST['role']);

            header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/account");
        }
        
        /**
         * Prepares an edit form for a selected account
         *
         * @param  array[any] $params
         * @return array[any] - User account information
         */
        function edit($params) {
            $db = $this->classes['db'];
            $auth = $this->classes['auth'];
            if (($auth->checkLevel() == 2) && ($auth->getId() != $params[2]) || !is_numeric($params[2])) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/account");
                die();
            }
            $db->select('users', array('username', 'level'));
            $db->where('id', '=', $params[2]);
            $db->additional('LIMIT 1');
            $result = $db->execute();
            $db->reset();
            if (!$result->num_rows) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/account");
                die();
            }
            $this->result['isAdmin'] = 'selected';
            $this->result['isAgent'] = '';
            $this->result['rolePower'] = '';
            while($row = $result->fetch_assoc()) {
                $this->result['username'] = $row['username'];
                if ($row['level'] == 2) {
                    $this->result['isAdmin'] = '';
                    $this->result['isAgent'] = 'selected';
                }
            }
            if ($this->classes['auth']->checkLevel() == 2) {
                $this->result['rolePower'] = 'disabled';
            }
            $this->result['editToken'] = $this->classes['auth']->generateFormToken('editToken');
            $this->result['editId'] = $params[2];
            return $this->result;
        }
        
        /**
         * Update submitted accounts information
         */
        function update() {
            if (!$this->classes['auth']->validateFormToken('editToken', $_POST['token'])) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/account");
                die();
            }
            if (($this->classes['auth']->checkLevel() == 2) && ($this->classes['auth']->getId() != $_POST['editId'])) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/account");
                die();
            }
            $update = array();
            if (!empty($_POST['username']) && !$this->classes['auth']->checkUserExist($_POST['username'])) {
                $update['username'] = $_POST['username'];
            }
            if (!empty($_POST['password'])) {
                $update['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            if (!empty($_POST['role'])) {
                $update['level'] = $_POST['role'];
            }
            $db = $this->classes['db'];
            $db->update('users', $update);
            $db->where('id', '=', $_POST['editId']);
            $db->execute();
            $db->reset();
            header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/account");
        }
    }
