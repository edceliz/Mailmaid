<?php
    /**
     * A simple database wrapper that uses prepared MySQLi.
     * 
     * Example usage:
     * $db = new Database('read');
     * $db->select('user', array('id', 'password'));
     * $db->where('username', '=', 'testing');
     * $db->additional('LIMIT 1');
     * $result = $db->execute();
     * $db->reset();
     * 
     * @package Mailmaid
     * @subpackage app
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app;

    class Database {
        /**
         * The connection for database.
         * 
         * @var mysqli 
         */
        private $connection;
        
        /**
         * The handle for queries.
         * 
         * @var mysqli
         */
        private $stmt;
        
        /**
         * Contains the raw SQL query.
         * 
         * @var string
         */
        private $query;
        
        /**
         * Contains the concatenated identifier of params' type.
         * 
         * @var string
         */
        private $paramsType;
        
        /**
         * Contains the parameters for the prepared statement.
         * 
         * @var array[string]
         */
        private $params = array();
        
        /**
         * Checks if there is a WHERE statement on the raw query.
         * 
         * @var boolean
         */
        private $_where;

        /**
         * Checks if there is a an additional statement on the raw query.
         * 
         * @var boolean
         */
        private $_additional;
        
        /**
         * Checks if the query is an INSERT.
         * 
         * @var boolean
         */
        private $_insert;
        
        /**
         * Defines the type of the user database connection.
         * 
         * @var string
         */
        private $type;
        
        /**
         * Contains pre-configured/default value for database account.
         * 
         * @var array[string][string]
         */
        private $connectionList = array(
            'write'=>array(
                'host'=>'localhost',
                'username'=>'root',
                'password'=>'root',
                'database'=>'mailmaid'
            ),
            'read'=>array(
                'host'=>'localhost',
                'username'=>'root',
                'password'=>'root',
                'database'=>'mailmaid'
            )
        );
        
        /**
         * Sets the value of the global $type.
         * 
         * @param string $type
         */
        function __construct($type) {
            $this->type = $type;
            $this->configConnection();
        }
        
        /**
         * Closes the database connection.
         */
        function __destruct() {
            @$this->connection->close();
        }
        
        /**
         * Connects to the selected database account.
         * 
         * Closes the existing open connection.
         */
        function connect() {
            if (!is_null($this->connection)) {
                @$this->connection->close();
            }
            @$this->connection = new \mysqli($this->connectionList[$this->type]['host'], $this->connectionList[$this->type]['username'], $this->connectionList[$this->type]['password'], $this->connectionList[$this->type]['database']);
        }
        
        /**
         * Sets the connection list' value from the database configure file.
         */
        function configConnection() {
            $json = file_get_contents('../app/db.json');
            $json = json_decode($json, true);
            $this->connectionList['write']['username'] =  $json['user'];
            $this->connectionList['write']['password'] = $json['pass'];
            $this->connectionList['write']['database'] = $json['db'];
            $this->connectionList['read']['username'] = $json['user'];
            $this->connectionList['read']['password'] = $json['pass'];
            $this->connectionList['read']['database'] = $json['db'];
            $this->connect();
        }

        /**
         * Checks if connection is active.
         * 
         * @return boolean
         */
        function checkConnection() {
            if ($this->connection->connect_error) {
                Registry::get('SystemLogger')->log('Can\'t connect to database! ' . $this->connection->connect_error , 2);
                return false;
            }
            return true;
        }
        
        /**
         * Creates a DELETE statement with the selected table.
         * 
         * @param string $table
         */
        function delete($table) {
            $this->query = 'DELETE FROM ' . $table . ' ';
        }
        
        /**
         * Creates an UPDATE statement with the selected table
         * 
         * The data to update are parsed for prepared statement.
         * 
         * @param string $table
         * @param array[string|int|boolean] $data
         */
        function update($table, $data) {
            $this->query = 'UPDATE ' . $table . ' SET';
            $dataCount = count($data);
            $count = 0;
            foreach($data as $k => $v) {
                $this->query .= ' ' . $k . ' = ?';
                if (($count + 1) < $dataCount) {
                    $this->query .= ',';
                }
                $this->addParam($v);
                $count++;
            }
            $this->query .= ' ';
        }
        
        /**
         * Creates an INSERT statement from the selected table.
         * 
         * The data to insert are parsed for prepared statement.
         * 
         * @param string $table
         * @param array[string|int|boolean] $data
         */
        function insert($table, $data) {
            $this->query = 'INSERT INTO ' . $table . '(';
            $dataCount = count($data);
            $count = 0;
            foreach($data as $k => $v) {
                $this->query .= $k;
                if (($count + 1) < $dataCount) {
                    $this->query .= ',';
                }
                $count++;
                $this->addParam($v);
            }
            $this->query .= ') VALUES(';
            for($i = 0; $i < $count; $i++) {
                if ($i == 0) {
                    $this->query .= '?';
                } else {
                    $this->query .= ' ?';
                }
                
                if (($i + 1) < $count) {
                    $this->query .= ',';
                }
            }
            $this->query .= ') ';
            $this->_insert = true;
        }
        
        /**
         * Creates a SELECT statement with the selected table.
         * 
         * The data to select are parsed with the raw query.
         * 
         * @param string $table
         * @param array[string|]|array('*') $data
         */
        function select($table, $data = array('*')) {
            $selects = '';
            if ($data != '*') {
                for($i = 0; $i < count($data); $i++) {
                    $selects .= $data[$i];
                    if(($i + 1) < count($data)) {
                        $selects = $selects . ', ';
                    }
                }
            }
            $this->query = 'SELECT ' . $selects . ' FROM ' . $table . ' ';
        }
        
        /**
         * Adds a JOIN statement with the raw query
         * 
         * @param string $table
         * @param string $key
         * @param string $type
         */
        function join($table, $key, $type) {
            $addition = $type . ' JOIN ' . $table . ' ON ' . $key . ' ';
            $this->query .= $addition;
        }
        
        /**
         * Adds a WHERE statement on raw query with the selected criteria.
         * 
         * @param string $criteria
         * @param string $condition
         * @param string|boolean|int $value
         */
        function where($criteria, $condition, $value) {
            $this->query .= 'WHERE ' . $criteria . ' ' . $condition . ' ?';
            $this->addParam($value);
            $this->_where = true;
        }
        
        /**
         * Used to add an AND WHERE statement on the raw query.
         * 
         * @param string $criteria
         * @param string $condition
         * @param string $value
         */
        function andWhere($criteria, $condition, $value) {
            $this->query .= ' AND ' . $criteria . ' ' . $condition . ' ?';
            $this->addParam($value);
            $this->_andWhere = true;
        }
        
        /**
         * Used to add an OR WHERE statement on the raw query.
         * 
         * @param string $criteria
         * @param type $condition
         * @param type $value
         */
        function orWhere($criteria, $condition, $value) {
            $this->query .= ' OR ' . $criteria . ' ' . $condition . ' ?';
            $this->addParam($value);
            $this->_orWhere = true;
        }
        
        /**
         * Used to add SQL statements that uses BY keywords such as
         * ORDER BY N or GROUP BY N.
         * 
         * @param string $criteria
         * @param string $value
         * @param string $param|''
         */
        function by($criteria, $value, $param = '') {
            $this->query .= ' ' . $criteria . ' BY ' . $value . ' ' . $param;
        }
        
        /**
         * Adds any additional statements that are not supported
         * by the wrapper to the raw query.
         * 
         * @param string $condition
         */
        function additional($condition) {
            if (!$this->_where) {
                $this->query .= ' WHERE 1';
                $this->_where = true;
            }
            $this->query .= ' ' . $condition;
        }
        
        /**
         * Resets the value of properties no blank.
         */
        function reset() {
            $this->paramsType = '';
            $this->params = array();
            $this->query = '';
            $this->_where = '';
            $this->_andWhere = '';
            $this->_orWhere = '';
            $this->_join = '';
            $this->_additional = '';
        }
        
        /**
         * Builds the raw query statement into a prepared statement.
         */
        private function builder() {
            if (!$this->_where && !$this->_insert) {
                $this->query .= ' WHERE 1';
            }
            
            $this->stmt = $this->connection->prepare($this->query);
            call_user_func_array(
                array($this->stmt, 'bind_param'), 
                array_merge(
                    array($this->paramsType), 
                    array_map(function(&$param) {
                        return $param;
                    }, $this->params)));
        }
        
        /**
         * Adds the type of the passed variabled into the global $paramsType.
         * 
         * @param string $var
         */
        private function addParam($var) {
            $this->paramsType .= gettype($var)[0];
            array_push($this->params, $var);
        }
        
        /**
         * Executes the prepared statement.
         * 
         * @return mysqli
         */
        function execute() {
            @$this->builder();
            if($this->stmt) {
                $this->stmt->execute();
                $result = $this->stmt->get_result();
                $this->stmt->close();
                return $result;
            } else {
                Registry::get('SystemLogger')->log('Can\'t execute ' . $this->query . ' with data ' . json_encode($this->params), 4);
            }
        }
    
        /**
         * Returns the instance of the database connection
         * for statements that can't be done using the wrapper.
         * 
         * @return mysqli
         */
        function getConnection() {
            return $this->connection;
        }
    }
