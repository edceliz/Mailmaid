<?php
    /**
     * Model for home graphs
     *
     * @package Mailmaid
     * @subpackage models
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\models;

    class HomeModel {
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
         * Checks if user is logged in.
         *
         * @return array[]
         */
        function index() {
            if (!$this->classes['auth']->checkLogin()) {
                header('location: /mailmaid/login');
                exit();
            }
            return $this->result;
        }
        
        /**
         * Fetches audience growth from last 24 hours
         * @param SubscriberModel $subModel
         */
        function audience24Hours($subModel) {
            $db = $this->classes['db'];
            $db->select('lists', array('id', 'name'));
            $db->where('status', '<', '2');
            $result = $db->execute();
            $db->reset();
            while ($row = $result->fetch_assoc()) {
                $dataset[$row['id']]['name'] = $row['name'];
                $dataset[$row['id']]['data'] = $subModel->graphDay($row['id'], true);
            }
            header('Content-Type: application/json');
            echo json_encode($dataset);
            die();
        }
        
        /**
         * Fetches audience growth from last 7 days
         * @param SubscriberModel $subModel
         */
        function audience7Days($subModel) {
            $db = $this->classes['db'];
            $db->select('lists', array('id', 'name'));
            $db->where('status', '<', '2');
            $result = $db->execute();
            $db->reset();
            while ($row = $result->fetch_assoc()) {
                $dataset[$row['id']]['name'] = $row['name'];
                $dataset[$row['id']]['data'] = $subModel->graphWeek($row['id'], true);
            }
            header('Content-Type: application/json');
            echo json_encode($dataset);
            die();
        }
        
        /**
         * Fetches audience growth from last year
         * @param SubscriberModel $subModel
         */
        function audienceYear($subModel) {
            $db = $this->classes['db'];
            $db->select('lists', array('id', 'name'));
            $db->where('status', '<', '2');
            $result = $db->execute();
            $db->reset();
            while ($row = $result->fetch_assoc()) {
                $dataset[$row['id']]['name'] = $row['name'];
                $dataset[$row['id']]['data'] = $subModel->graphMonth($row['id'], true);
            }
            header('Content-Type: application/json');
            echo json_encode($dataset);
            die();
        }
        
        /**
         * Fetches all campaign engagement data from last 24 hours
         */
        function campaignEngagement24Hours() {
            $db = $this->classes['db']->getConnection();
            $stmt = $db->prepare('SELECT id, name FROM campaign WHERE status BETWEEN 2 AND 3');
            $stmt->execute();
            $stmt->bind_result($campaignId, $name);
            $stmt->store_result();
            while ($stmt->fetch()) {
                $stmt1 = $db->prepare("SELECT CONCAT(HOUR(dateCreated), ':00-', HOUR(dateCreated) + 1, ':00') AS hours, COUNT(linkclicks.id) as count FROM linkclicks INNER JOIN links ON linkclicks.linkId = links.id WHERE links.campaignId = ? AND dateCreated >= NOW() - INTERVAL 1 DAY GROUP BY hours");
                $stmt1->bind_param('i', $campaignId);
                $stmt1->execute();
                $stmt1->bind_result($period, $count);
                while ($stmt1->fetch()) {
                    $result[$name][$period] = $count;
                }
                $stmt1->close();
            }
            $stmt->free_result();
            $stmt->close();
            header('Content-Type: application/json');
            if (isset($result)) {
                echo json_encode($result);
            } else {
                echo 'false';
            }
            die();
        }
        
        /**
         * Fetches all campaign engagement data from last 7 days
         */
        function campaignEngagement7Days() {
            $db = $this->classes['db']->getConnection();
            $stmt = $db->prepare('SELECT id, name FROM campaign WHERE status BETWEEN 2 AND 3');
            $stmt->execute();
            $stmt->bind_result($campaignId, $name);
            $stmt->store_result();
            while ($stmt->fetch()) {
                $stmt1 = $db->prepare("SELECT CONCAT(MONTH(dateCreated), '/', DAY(dateCreated), '/', YEAR(dateCreated)) AS days, COUNT(linkclicks.id) as count FROM linkclicks INNER JOIN links ON linkclicks.linkId = links.id WHERE links.campaignId = ? AND dateCreated >= NOW() - INTERVAL 7 DAY GROUP BY days");
                $stmt1->bind_param('i', $campaignId);
                $stmt1->execute();
                $stmt1->bind_result($period, $count);
                while ($stmt1->fetch()) {
                    $result[$name][$period] = $count;
                }
                $stmt1->close();
            }
            $stmt->free_result();
            $stmt->close();
            header('Content-Type: application/json');
            if (isset($result)) {
                echo json_encode($result);
            } else {
                echo 'false';
            }
            die();
        }
        
        /**
         * Fetches all campaign engagement data from last year
         */
        function campaignEngagementYear() {
            $db = $this->classes['db']->getConnection();
            $stmt = $db->prepare('SELECT id, name FROM campaign WHERE status BETWEEN 2 AND 3');
            $stmt->execute();
            $stmt->bind_result($campaignId, $name);
            $stmt->store_result();
            while ($stmt->fetch()) {
                $stmt1 = $db->prepare("SELECT MONTH(dateCreated) AS months, COUNT(linkclicks.id) as count FROM linkclicks INNER JOIN links ON linkclicks.linkId = links.id WHERE links.campaignId = ? AND YEAR(dateCreated) = YEAR(NOW()) GROUP BY months");
                $stmt1->bind_param('i', $campaignId);
                $stmt1->execute();
                $stmt1->bind_result($period, $count);
                while ($stmt1->fetch()) {
                    $result[$name][$period] = $count;
                }
                $stmt1->close();
            }
            $stmt->free_result();
            $stmt->close();
            header('Content-Type: application/json');
            if (isset($result)) {
                echo json_encode($result);
            } else {
                echo 'false';
            }
            die();
        }
        
        /**
         * Fetches all mails sent per active campaign from last 24 hours.
         * @param CampaignModel $campModel
         */
        function mailSent24Hours($campModel) {
            $db = $this->classes['db'];
            $db->select('campaign', array('id', 'name'));
            $db->where('status', '=', '2');
            $db->orWhere('status', '=', '4');
            $db->by('ORDER', 'dateModified', 'DESC');
            $db->additional('LIMIT 5');
            $result = $db->execute();
            $db->reset();
            while ($row = $result->fetch_assoc()) {
                $dataset[$row['id']]['name'] = $row['name'];
                $dataset[$row['id']]['data'] = $campModel->mailSent24Hours($row['id'], true);
            }
            header('Content-Type: application/json');
            if (isset($dataset)) {
                echo json_encode($dataset);
            } else {
                echo 'false';
            }
            die();
        }
        
        /**
         * Fetches all mails sent per active campaign from last 7 days.
         * @param CampaignModel $campModel
         */
        function mailSent7Days($campModel) {
            $db = $this->classes['db'];
            $db->select('campaign', array('id', 'name'));
            $db->where('status', '=', '2');
            $db->orWhere('status', '=', '4');
            $db->by('ORDER', 'dateModified', 'DESC');
            $db->additional('LIMIT 5');
            $result = $db->execute();
            $db->reset();
            while ($row = $result->fetch_assoc()) {
                $dataset[$row['id']]['name'] = $row['name'];
                $dataset[$row['id']]['data'] = $campModel->mailSent7Days($row['id'], true);
            }
            header('Content-Type: application/json');
            if (isset($dataset)) {
                echo json_encode($dataset);
            } else {
                echo 'false';
            }
            die();
        }
        
        /**
         * Fetches all mails sent per active campaign from last year.
         * @param CampaignModel $campModel
         */
        function mailSentYear($campModel) {
            $db = $this->classes['db'];
            $db->select('campaign', array('id', 'name'));
            $db->where('status', '=', '2');
            $db->orWhere('status', '=', '4');
            $db->by('ORDER', 'dateModified', 'DESC');
            $db->additional('LIMIT 5');
            $result = $db->execute();
            $db->reset();
            while ($row = $result->fetch_assoc()) {
                $dataset[$row['id']]['name'] = $row['name'];
                $dataset[$row['id']]['data'] = $campModel->mailSentYear($row['id'], true);
            }
            header('Content-Type: application/json');
            if (isset($dataset)) {
                echo json_encode($dataset);
            } else {
                echo 'false';
            }
            die();
        }
    }
