<?php
    /**
     * Model for managing subscribers
     *
     * @package Mailmaid
     * @subpackage models
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\models;

    class SubscriberModel {
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
         * Fetches list of subscriber list
         *
         * @param  array[any] $params
         * @return array[any]
         */
        function index($params) {
            $db = $this->classes['db'];
            $auth = $this->classes['auth'];
            $this->result['edit'] = $auth->checkLevel() == 1 ? true : false;
            $db->select('lists', array('id', 'name'));
            $db->where('status', '<', '2');
            $db->by('ORDER', 'id', 'DESC');
            $result = $db->execute();
            $db->reset();
            $this->result['list'] = array();
            while($row = $result->fetch_assoc()) {
                array_push($this->result['list'], array($row['id'], $row['name']));
            }
            return $this->result;
        }
        
        /**
         * Prepares a form for creating subscriber list
         *
         * @return array[any]
         */
        function add($params) {
            $auth = $this->classes['auth'];
            if ($auth->checkLevel() != 1) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/subscriber");
                die();
            }
            
            if(isset($_POST['token'])) {
                $this->create();
            }
            
            $this->result['token'] = $auth->generateFormToken('subToken');
            return $this->result;
        }
        
        /**
         * Creates a subscriber list from submitted request.
         */
        function create() {
            $auth = $this->classes['auth'];
            $db = $this->classes['db'];
            if (!isset($_POST['name']) || !$auth->validateFormToken('subToken', $_POST['token'])) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/subscriber");
                die();
            }
            $input = array();
            $input['name'] = $_POST['name'];
            if (!empty($_POST['description'])) {
                $input['description'] = $_POST['description'];
            } else {
                $input['description'] = 'No description';
            }
            $db->insert('lists', $input);
            $db->execute();
            $db->reset();
            header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/subscriber");
        }
        
        /**
         * Returns subscriber list information for viewing
         *
         * @param  array[any] $params
         * @return array[any]
         */
        function view($params) {
            $db = $this->classes['db'];
            $auth = $this->classes['auth'];
            
            if(!is_numeric($params[2])) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/subscriber");
                die();
            } 
            
            $db->select('lists', array('name', 'description'));
            $db->where('id', '=', $params[2]);
            $db->andWhere('status', '<', '2');
            $db->additional('LIMIT 1');
            $result = $db->execute();
            $db->reset();
            if (!$result->num_rows) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/subscriber");
                die();
            }
            $this->result['listData'] = array();
            while($row = $result->fetch_assoc()) {
                $this->result['name'] = $row['name'];
                $this->result['description'] = $row['description'];
            }
            
            if ($auth->checkLevel() == 2 || $params[2] == 1) {
                $this->result['token'] = '';
                $this->result['disabled'] = 'disabled';
                $this->result['submit'] = false;
                $this->result['listId'] = '';
            } else {
                $this->result['token'] = $auth->generateFormToken('subUpdateToken');
                $this->result['disabled'] = '';
                $this->result['submit'] = true;
                $this->result['listId'] = $params[2];
            }
            
            $db->select('listsubs', array('COUNT(id) AS count'));
            $db->where('listId', '=', intval($params[2]));
            $db->andWhere('status', '=', 1);
            $result = $db->execute();
            $db->reset();
            $this->result['subCount'] = $result->fetch_assoc()['count'];
            return $this->result;
        }
        
        /**
         * Updates subscriber list information from submitted request.
         */
        function update() {
            if (isset($_POST['delete'])) {
                $this->delete();
            }
            $auth = $this->classes['auth'];
            $db = $this->classes['db'];
            if (!isset($_POST['name']) || !$auth->validateFormToken('subUpdateToken', $_POST['token'])) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/subscriber");
                die();
            }
            $update['name'] = $_POST['name'];
            if (!empty($_POST['description'])) {
                $update['description'] = $_POST['description'];
            } else {
                $update['description'] = 'No description';
            }
            $db->update('lists', $update);
            $db->where('id', '=', $_POST['listId']);
            $db->execute();
            $db->reset();
            header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/subscriber");
        }
        
        /**
         * Deletes subscriber list.
         */
        function delete() {
            if (!isset($_POST['token']) || !$this->classes['auth']->validateFormToken('subUpdateToken', $_POST['token']) || $_POST['listId'] == 1) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/subscriber");
                die();
            }
            $db = $this->classes['db'];
            $db->update('lists', array('status'=>2));
            $db->where('id', '=', $_POST['listId']);
            $db->execute();
            $db->reset();
            header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/subscriber");
        }
        
        /**
         * Adds a subscriber to a list. Returns a JSON about status.
         */
        function subscribe() {
            $db = $this->classes['db'];
            $email = $_POST['email'];
            $listId = $_POST['listId'];
            header('Content-Type: application/json');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo(json_encode(array('subscription'=>false)));
                exit();
            }
            $dbi = $db->getConnection();
            $stmt = $dbi->prepare('SELECT subscribers.id, status FROM listsubs INNER JOIN subscribers ON subscribers.id = listsubs.subscriberId WHERE (status = 0 OR status = 1) AND emailAddress = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->bind_result($id, $status);
            $stmt->store_result();
            if ($stmt->num_rows) {
                $stmt->fetch();
                $stmt->free_result();
                $stmt->close();
                if ($status) {
                    echo(json_encode(array('subscription'=>false)));
                } else {
                    $db->update('listsubs', array('status'=>1));
                    $db->where('subscriberId', '=', $id);
                    $db->execute();
                    $db->reset();
                    echo(json_encode(array('subscription'=>true)));
                }
                die();
            } else {
                $stmt->free_result();
                $stmt->close();
            }

            $db->insert('subscribers', array('emailAddress'=>$email));
            $db->execute();
            $db->reset();
            $insertId = $db->getConnection()->insert_id;
            $db->insert('listsubs', array('listId'=>1, 'subscriberId'=>$insertId));
            $db->execute();
            $db->reset();
            if ($listId == 1) {
                echo(json_encode(array('subscription'=>true)));
                exit();
            }
            $db->insert('listsubs', array('listId'=>$listId, 'subscriberId'=>$insertId));
            $db->execute();
            $db->reset();
            echo(json_encode(array('subscription'=>true)));
            exit();
        }
        
        /**
         * Removes subscriber from usable contacts.
         * @param array[any] $user - Email address and ID
         */
        function unsubscribe($user) {
            $new = array();
            $new = array_merge($new, $user);
            $db = $this->classes['db'];
            $db->select('subscribers', array('id'));
            $db->where('emailAddress', '=', $new[0]);
            $db->andWhere('id', '=', $new[1]);
            $db->additional('LIMIT 1');
            if(!$db->execute()->num_rows) {
                $db->reset();
                return;
            }
            $db->reset();
            $db->update('listsubs', array('status'=>0));
            $db->where('subscriberId', '=', $new[1]);
            $db->execute();
            $db->reset();
        }
        
        /**
         * Fetches subscriber growth of selected list from last day.
         * @param  int $id               - Subscriber list ID
         * @param  boolean [$return = false] - If true, sends PHP array instead of outputting JSON.
         * @return array[any] - Optional
         */
        function graphDay($id, $return = false) {
            $result = array(array(), array());
            $db = $this->classes['db']->getConnection();
            $stmt = $db->prepare("SELECT CONCAT(HOUR(latestUpdate), ':00-', HOUR(latestUpdate)+1, ':00') AS hours, COUNT(id) AS count FROM listsubs WHERE latestUpdate >= NOW() - INTERVAL 1 DAY AND status = 1 AND listId = ? GROUP BY hours");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($hours, $count);
            while ($stmt->fetch()) {
                $result[0][$hours] = $count;
            }
            $stmt->close();
            $stmt = $db->prepare("SELECT CONCAT(HOUR(latestUpdate), ':00-', HOUR(latestUpdate)+1, ':00') AS hours, COUNT(id) AS count FROM listsubs WHERE latestUpdate >= NOW() - INTERVAL 1 DAY AND status = 0 AND listId = ? GROUP BY hours");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($hours, $count);
            while ($stmt->fetch()) {
                $result[1][$hours] = $count;
            }
            $stmt->close();
            if (!$return) {
                header('Content-Type: application/json');
                echo json_encode($result);
                die();
            }
            return $result;
        }
        
        /**
         * Fetches subscriber growth of selected list from last 7 days.
         * @param  int $id               - Subscriber list ID
         * @param  boolean [$return = false] - If true, sends PHP array instead of outputting JSON.
         * @return array[any] - Optional
         */
        function graphWeek($id, $return = false) {
            $result = array(array(), array());
            $db = $this->classes['db']->getConnection();
            $stmt = $db->prepare("SELECT CONCAT(MONTH(latestUpdate), '/', DAY(latestUpdate), '/', YEAR(latestUpdate)) AS days, COUNT(id) AS count FROM listsubs WHERE latestUpdate >= NOW() - INTERVAL 7 DAY AND status = 1 AND listId = ? GROUP BY days");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($days, $count);
            while ($stmt->fetch()) {
                $result[0][$days] = $count;
            }
            $stmt->close();
            $stmt = $db->prepare("SELECT CONCAT(MONTH(latestUpdate), '/', DAY(latestUpdate), '/', YEAR(latestUpdate)) AS days, COUNT(id) AS count FROM listsubs WHERE latestUpdate >= NOW() - INTERVAL 7 DAY AND status = 0 AND listId = ? GROUP BY days");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($days, $count);
            while ($stmt->fetch()) {
                $result[1][$days] = $count;
            }
            $stmt->close();
            if (!$return) {
                header('Content-Type: application/json');
                echo json_encode($result);
                die();
            }
            return $result;
        }
        
        /**
         * Fetches subscriber growth of selected list from month.
         * @param  int $id               - Subscriber list ID
         * @param  boolean [$return = false] - If true, sends PHP array instead of outputting JSON.
         * @return array[any] - Optional
         */
        function graphMonth($id, $return = false) {
            $result = array(array(), array());
            $db = $this->classes['db']->getConnection();
            $stmt = $db->prepare("SELECT MONTH(latestUpdate) AS month, COUNT(id) AS count FROM listsubs WHERE YEAR(latestUpdate) = YEAR(NOW()) AND status = 1 AND listId = ? GROUP BY month");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($month, $count);
            while ($stmt->fetch()) {
                $result[0][$month] = $count;
            }
            $stmt->close();
            $stmt = $db->prepare("SELECT MONTH(latestUpdate) AS month, COUNT(id) AS count FROM listsubs WHERE YEAR(latestUpdate) = YEAR(NOW()) AND status = 0 AND listId = ? GROUP BY month");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($month, $count);
            while ($stmt->fetch()) {
                $result[1][$month] = $count;
            }
            $stmt->close();
            if (!$return) {
                header('Content-Type: application/json');
                echo json_encode($result);
                die();
            }
            return $result;
        }
        
        /**
         * Fetches subscriber growth of selected list from last 5 years.
         * @param  int $id               - Subscriber list ID
         * @param  boolean [$return = false] - If true, sends PHP array instead of outputting JSON.
         * @return array[any] - Optional
         */
        function graphAll($id) {
            $result = array(array(), array());
            $db = $this->classes['db']->getConnection();
            $stmt = $db->prepare("SELECT YEAR(latestUpdate) AS year, COUNT(id) AS count FROM listsubs WHERE YEAR(latestUpdate) >= YEAR(NOW() - INTERVAL 5 YEAR) AND status = 1 AND listId = ? GROUP BY year");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($year, $count);
            while ($stmt->fetch()) {
                $result[0][$year] = $count;
            }
            $stmt->close();
            $stmt = $db->prepare("SELECT YEAR(latestUpdate) AS year, COUNT(id) AS count FROM listsubs WHERE YEAR(latestUpdate) >= YEAR(NOW() - INTERVAL 5 YEAR) AND status = 0 AND listId = ? GROUP BY year");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($year, $count);
            while ($stmt->fetch()) {
                $result[1][$year] = $count;
            }
            $stmt->close();
            header('Content-Type: application/json');
            echo json_encode($result);
            die();
        }
    }
