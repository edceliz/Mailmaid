<?php
    /**
     * Contains common website authentication related functions.
     * 
     * Example usage:
     * $auth = new Authentication();
     * if ($auth->checkLogin()) {
     *     echo 'Logged in!';
     * } else {
     *     echo 'Not logged in!';
     * }
     * 
     * @package Mailmaid
     * @subpackage app
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app;

    class Authentication {
        /**
         * Contains information about user and cookies database table
         * 
         * @var array[string|int]
         */
        private $config = array(
            'userTable' => 'users',
            'idColumnName' => 'id',
            'usernameColumnName' => 'username',
            'passwordColumnName' => 'password',
            'levelColumnName' => 'level',
            'usesCookie' => 0,
            'cookieTable' => 'cookies',
            'cookieUIDColumnName' => 'uid',
            'cookieCodeColumnName' => 'code',
            'cookieIPColumnName' => 'ip',
            'CookieExpiration' => 3600 * 24 * 30
        );
        
        /**
         * An instance of database object that can only perform read actions.
         * 
         * @var Database
         */
        private $readConnection;
        
        /**
         * An instance of database object that can only perform write actions.
         * 
         * @var Database
         */
        private $writeConnection;
        
        /**
         * Assigns the new config data to global $config.
         * 
         * @param array[string|int] $config
         */
        function __construct($config = array()) {
            if (!empty($config)) {
                foreach($config as $k => $v) {
                    $this->config[$k] = $v;
                }
            }
            $this->readConnection = Registry::get('ReadConnection');
            $this->writeConnection = Registry::get('WriteConnection');
        }
        
        /**
         * Gets the best IP address of the client.
         * 
         * @return string
         */
        function getIPAddress() {
            if (!empty($_SERVER['HTTP_CLIENT_IP']) && $this->validateIPAddress($_SERVER['HTTP_CLIENT_IP'])) {
                return $_SERVER['HTTP_CLIENT_IP'];
            }
            
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach($iplist as $ip) {
                    if ($this->validateIPAddress($ip)) {
                        return $ip;
                    }
                }
            }
            
            if (!empty($_SERVER['HTTP_X_FORWARDED']) && $this->validateIPAddress($_SERVER['HTTP_X_FORWARDED'])) {
                return $_SERVER['HTTP_X_FORWARDED'];
            }
                
            if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && $this->validateIPAddress($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
                return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
            }
                
            if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && $this->validateIPAddress($_SERVER['HTTP_FORWARDED_FOR'])) {
                return $_SERVER['HTTP_FORWARDED_FOR'];
            }
                
            if (!empty($_SERVER['HTTP_FORWARDED']) && $this->validateIPAddress($_SERVER['HTTP_FORWARDED'])) {
                return $_SERVER['HTTP_FORWARDED'];
            }
            
            if ($_SERVER['REMOTE_ADDR'] == '::1') {
                return '127.0.0.1';
            }
            
            return $_SERVER['REMOTE_ADDR'];
        }

        /**
         * Checks if the IP address is valid.
         * 
         * @param string $ip
         * @return boolean
         */
        private function validateIPAddress($ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP, 
                         FILTER_FLAG_IPV4 | 
                         FILTER_FLAG_IPV6 |
                         FILTER_FLAG_NO_PRIV_RANGE | 
                         FILTER_FLAG_NO_RES_RANGE) === false) {
                return false;
            }
            return true;
        }
        
        /**
         * Logs in user if credentials are valid.
         * 
         * Looks for username's corresponding password and 
         * compare its hash on submitted password.
         * 
         * If cookie login is enabled by the developer,
         * the function will call checkCookie function to
         * return if a cookie is set and valid.
         * 
         * @param string $username
         * @param string $password
         * @return boolean
         */
        function login($username, $password) {
            $read = $this->readConnection;
            $write = $this->writeConnection;
            $read->select(
                $this->config['userTable'],
                array(
                    $this->config['idColumnName'],
                    $this->config['passwordColumnName'],
                    $this->config['levelColumnName']
                )
            );
            $read->where($this->config['usernameColumnName'], '=', $username);
            $read->additional('LIMIT 1');
            $result = $read->execute();
            $read->reset();
            while($row = $result->fetch_assoc()) {
                if (password_verify($password, $row[$this->config['passwordColumnName']])) {
                    $_SESSION['uid'] = $row[$this->config['idColumnName']];
                    $_SESSION['level'] = $row[$this->config['levelColumnName']];
                }
            }
            if ($this->config['usesCookie'] == 1) {
                if ($this->checkCookie()) {
                    return true;
                } else {
                    return false;
                }
                $this->createCookie();
            }
            return true;
        }
        
        /**
         * Creates an IP specific and code based login cookie.
         */
        private function createCookie() {
            $write = $this->writeConnection;
            $ip = ip2long($this->getIPAddress());
            $rand = bin2hex(openssl_random_pseudo_bytes(128));
            $read = $this->readConnection;
            $read->select($this->config['cookieTable'], array($this->config['cookieCodeColumnName']));
            $read->where($this->config['cookieIPColumnName'], '=', $ip);
            $read->additional('LIMIT 1');
            $result = $read->execute();
            $read->reset();
            if (!$result->num_rows) {
                $write->insert(
                    $this->config['cookieTable'],
                    array(
                        $this->config['cookieUIDColumnName'] => $_SESSION['uid'],
                        $this->config['cookieCodeColumnName'] => $rand,
                        $this->config['cookieIPColumnName'] => $ip
                    )
                );
                $write->execute();
                $write->reset();
            } else {
                while($row = $result->fetch_assoc()) {
                    $rand = $row[$this->config['cookieCodeColumnName']];
                }
            }
            setcookie('cw_id', $rand, time() + $this->config['CookieExpiration'], '/');
        }
        
        /**
         * Insert user into user table.
         * 
         * Checks if user doesn't exist in database
         * then insert new account.
         * 
         * @param string $username
         * @param string $password
         * @param int $level
         * @return boolean
         */
        function register($username, $password, $level) {
            $password = password_hash($password, PASSWORD_DEFAULT);
            $write = $this->writeConnection;
            if($this->checkUserExist($username)) {
                return false;
            }
            $write->insert(
                $this->config['userTable'],
                array(
                    $this->config['usernameColumnName'] => $username,
                    $this->config['passwordColumnName'] => $password,
                    $this->config['levelColumnName'] => $level
                )
            );
            $write->execute();
            $write->reset();
            return true;
        }
        
        /**
         * Checks if username exists in user table.
         * 
         * @param string $username
         * @return boolean
         */
        function checkUserExist($username) {
            $read = $this->readConnection;
            $read->select($this->config['userTable'], array('id'));
            $read->where($this->config['usernameColumnName'], '=', $username);
            $result = $read->execute();
            $read->reset();
            if($result->num_rows) {
                return true;
            }
            return false;
        }
        
        /**
         * Unsets all session
         * 
         * If cookie is set, the cookie row in database is
         * deleted and the browser cookie is set to expire.
         */
        function logout() {
            session_unset();
            if(isset($_COOKIE['cw_id'])) {
                $ip = ip2long($this->getIPAddress());
                unset($_COOKIE['cw_id']);
                setcookie('cw_id', '', time() - 3600);
                $write = $this->writeConnection;
                $write->delete($this->config['cookieTable']);
                $write->where($this->config['cookieIPColumnName'], '=', $ip);
                $write->execute();
                $write->reset();
            }
        }
 
        /**
         * Checks if user is logged in.
         * 
         * @return boolean
         */
        function checkLogin() {
            if (!isset($_SESSION['uid'])) {
                return $this->checkCookie();
            } else {
                return true;
            }
        }
        
        /**
         * Checks if user's cookie is set and valid.
         * 
         * Logs in user if the cookie is valid.
         * 
         * @return boolean
         */
        function checkCookie() {
            if ($this->config['usesCookie'] == 0) {
                return false;
            }

            $read = $this->readConnection;
            $ip = ip2long($this->getIPAddress());
            $read->select($this->config['cookieTable'], array($this->config['cookieUIDColumnName'], $this->config['cookieCodeColumnName']));
            $read->where($this->config['cookieIPColumnName'], '=', $ip);
            $result = $read->execute();
            $read->reset();
            while($row = $result->fetch_assoc()) {
                if ($row[$this->config['cookieCodeColumnName']] == $_COOKIE['cw_id']) {
                    $_SESSION['uid'] = $row[$this->config['cookieUIDColumnName']];
                    return true;
                }
            }
            return false;
        }
        
        /**
         * Returns the role of the logged in user.
         * 
         * @return int|boolean
         */
        function checkLevel() {
            if (isset($_SESSION['level'])) {
                return $_SESSION['level'];
            }
            return false;
        }
        
        /**
         * Returns the current user's ID.
         * 
         * @return id
         */
        function getID() {
            return $_SESSION['uid'];
        }
        
        /**
         * Creates a secure 32 character random code for forms.
         * 
         * @param string $name
         * @return string
         */
        function generateFormToken($name) {
            if (empty($_SESSION[$name])) {
                $_SESSION[$name] = bin2hex(openssl_random_pseudo_bytes(16));
            }
            $token = $_SESSION[$name];
            return $token;
        }
        
        /**
         * Remove the form token with specific key name.
         * 
         * @param string $name
         */
        function removeFormToken($name) {
            if (isset($_SESSION[$name])) {
                unset($_SESSION[$name]);
            }
        }
        
        /**
         * Check if the submitted token is valid with the set token.
         * 
         * @param string $name
         * @param string $token
         * @return boolean
         */
        function validateFormToken($name, $token) {
            if (!empty($_SESSION[$name])) {
                if (hash_equals($_SESSION[$name], $token)) {
                    unset($_SESSION[$name]);
                    return true;
                }
            }
            unset($_SESSION[$name]);
            return false;
        }
        
        /**
         * Checks if a row exists on user table.
         * 
         * @return boolean
         */
        function checkFirstUser() {
            $db = $this->readConnection;
            $db->select('users', array('id'));
            $db->additional('LIMIT 1');
            $result = $db->execute();
            $db->reset();
            if (!$result->num_rows) {
                return false;
            }
            return true;
        }
    }
