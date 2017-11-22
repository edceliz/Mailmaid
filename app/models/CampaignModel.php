<?php
    /**
     * Model for managing campaigns
     *
     * @package Mailmaid
     * @subpackage models
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\models;

    class CampaignModel {
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
         * Fetches list of campaigns
         *
         * @param  array[any] $params
         * @return array[any]
         */
        function index($params) {
            $db = $this->classes['db'];
            $db->select('campaign', array('name', 'id'));
            $db->where('status', '>', '0');
            $db->by('ORDER', 'dateModified', 'DESC');
            $result = $db->execute();
            $this->params['campaign'] = array();
            while($row = $result->fetch_assoc()) {
                array_push($this->params['campaign'], array('id'=>$row['id'], 'name'=>$row['name']));
            }
            return $this->params;
        }
        
        /**
         * Prepares a form for creating campaign
         *
         * @return array[any]
         */
        function add($params) {
            $this->result['token'] = $this->classes['auth']->generateFormToken('campaignCreateToken');
            $db = $this->classes['db'];
            $db->select('lists', array('id', 'name'));
            $db->where('status', '<', 2);
            $result = $db->execute();
            $db->reset();
            $this->result['list'] = array();
            while($row = $result->fetch_assoc()) {
                array_push($this->result['list'], array('id'=>$row['id'], 'name'=>$row['name']));
            }
            return $this->result;
        }
        
        /**
         * Creates a campaign from submitted request.
         */
        function createCampaign() {
            if (!$this->classes['auth']->validateFormToken('campaignCreateToken', $_POST['token']) || !isset($_POST['campaign']) || !isset($_POST['subject'])) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign");
                die();
            }
            $name = $_POST['campaign'];
            $subject = $_POST['subject'];
            $listId = $_POST['list'];
            $db = $this->classes['db'];
            $db->insert('content', array('content'=>"<p align='center'><b>Hello! Goodluck on your new campaign!&nbsp;<img src='http://mailmaid/mailmaid/js/tinymce/plugins/emoticons/img/smiley-laughing.gif' alt='laughing' /></b></p>
            <p align='left'><font style='background-color: #008080;' color='#FFFFFF'><b>&nbsp;Tips:&nbsp;</b></font></p>
            <ul style='list-style-type: circle;'>
            <li align='left'>The <b>average width of an e-mail is 600px</b>. If you are viewing this page with a 1366x768 resolution monitor, this textarea would be just pixels larger from the average width.</li>
            <li align='left'><b>Unsubscribe&nbsp;</b>link is automatically added for every mail sent.</li>
            <li align='left'>Every mail sent has a <b>secure website version viewable in your server</b> in case recipients' e-mail client can't handle or view HTML mails properly.</li>
            <li align='left'>You can only upload images using this editor. If you want to embed videos and other files, you can go to&nbsp;<i>Insert-&gt;Media</i> or&nbsp;<i>Insert-&gt;Link</i>.</li>
            <li align='left'>Make use of traceable links. Create a link below and insert the link here for you to track e-mail engagement.</li>
            </ul>
            <hr />
            <p align='center'><i>If you need help, just click the question mark button or go to View-&gt;Help</i></p>
            <hr />
            <h1 id='mcetoc_1bmhmmnu60' align='center'><font color='#FF0000'><b>Remove whole content to get started!</b></font></h1>"));
            $db->execute();
            $contentId = $db->getConnection()->insert_id;
            $db->reset();
            $db->insert('campaign', array('name'=>$name, 'subject'=>$subject, 'contentId'=>$contentId, 'listId'=>$listId));
            $db->execute();
            $location = $db->getConnection()->insert_id;
            $db->reset();
            header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign/edit/{$location}");
            die();
        }
        
        /**
         * Prepares an edit form for a selected campaign
         *
         * Campaign Status Code
         * 0 - Deleted
         * 1 - Ready
         * 2 - Ongoing
         * 3 - Paused
         * 4 - Finished
         *
         * @param  array[any] $params
         * @return array[any] - Campaign information
         */
        function edit($params) {
            $db = $this->classes['db'];
            $campaignId = intval(reset($params));
            $this->result['id'] = $campaignId;
            $db->select('links', array('id', 'direction', 'code'));
            $db->where('campaignid', '=', $campaignId);
            $result = $db->execute();
            $this->result['links'] = array();
            while($row = $result->fetch_assoc()) {
                array_push($this->result['links'], array('id'=>$row['id'], 'direction'=>$row['direction'], 'link'=>"http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign/link/{$row['code']}"));
            }
            $db->reset();
            $db->select('campaign', array('name', 'subject', 'content', 'listId', 'status'));
            $db->join('content', 'content.id = contentId', 'INNER');
            $db->where('campaign.id', '=', $campaignId);
            $db->additional('LIMIT 1');
            $result = $db->execute();
            if (!$result->num_rows) {
                $db->reset();
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign");
                die();
            }
            $this->result = array_merge($this->result, $result->fetch_assoc());
            $db->reset();
            $db->select('lists', array('id', 'name'));
            $db->where('status', '<', 2);
            $result = $db->execute();
            $db->reset();
            $this->result['list'] = array();
            while($row = $result->fetch_assoc()) {
                array_push($this->result['list'], array('id'=>$row['id'], 'name'=>$row['name']));
            }
            $this->result['token'] = $this->classes['auth']->generateFormToken('campaignEditToken');
            
            // Calculate campaign progress
            $db->select('mailqueue', array('COUNT(id) as count', 'status'));
            $db->where('campaignId', '=', $campaignId);
            $db->andWhere('status', '!=', 3);
            $db->by('GROUP', 'status');
            $result = $db->execute();
            $this->result['progress']['target'] = 0;
            $this->result['progress']['sent'] = 0;
            $this->result['progress']['sentPercent'] = 0;
            $this->result['progress']['failed'] = 0;
            $this->result['progress']['failedPercent'] = 0;
            while ($row = $result->fetch_assoc()) {
                switch ($row['status']) {
                    case 0:
                        $this->result['progress']['target'] += $row['count'];
                        break;
                    case 1:
                        $this->result['progress']['target'] += $row['count'];
                        $this->result['progress']['sent'] += $row['count'];
                        break;
                    case 2:
                        $this->result['progress']['target'] += $row['count'];
                        break;
                    case 4:
                        $this->result['progress']['target'] += $row['count'];
                        $this->result['progress']['failed'] += $row['count'];
                        break;
                }
                $this->result['progress']['sentPercent'] = round(($this->result['progress']['sent'] / $this->result['progress']['target']) * 100, 2);
                $this->result['progress']['failedPercent'] = round(($this->result['progress']['failed'] / $this->result['progress']['target']) * 100, 2);
            }
            return $this->result;
        }
        
        /**
         * Update submitted campaign information
         */
        function save() {
            if (!$this->classes['auth']->validateFormToken('campaignEditToken', $_POST['token'])) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign");
                die();
            }
            $db = $this->classes['db'];
            $content = empty($_POST['content']) ? '' : $_POST['content'];
            $campaignId = $_POST['campaignId'];
            $title = $_POST['campaign'];
            $subject = $_POST['subject'];
            $listId = $_POST['list'];
            $db->update('content', array('content'=>$content));
            $db->execute();
            $db->reset();
            $db->update('campaign', array('name'=>$title, 'subject'=>$subject, 'listId'=>$listId));
            $db->where('id', '=', $campaignId);
            $db->execute();
            $db->reset();
            header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign/edit/{$campaignId}");
            die();
        }
        
        /**
         * Selects appropriate campaign movement based on request
         *
         * Mail Queue Status Code
         * 0 - Unsent
         * 1 - Sent
         * 2 - Paused
         * 3 - Deleted
         * 4 - Error
         */
        function operate() {
            if (!$this->classes['auth']->validateFormToken('campaignEditToken', $_POST['token'])) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign");
                die();
            }
            $id = $_POST['campaignId'];
            $listId = $_POST['list'];
            switch ($_POST['operate']) {
                case 'Start':
                    $this->startCampaign($id, $listId);
                    break;
                case 'Pause';
                    $this->pauseCampaign($id);
                    break;
                case 'Resume';
                    $this->resumeCampaign($id);
                    break;
            }
        }

        /**
         * Starts the campaign and initializes the mail queue for recipients
         *
         * @param int $id     - Campaign ID
         * @param int $listId - Recipient List ID
         */
        function startCampaign($id, $listId) {
            set_time_limit(0);
            ignore_user_abort(true);
            $db = $this->classes['db'];
            $db->select('listsubs', array('subscriberId'));
            $db->where('listId', '=', $listId);
            $result = $db->execute();
            $recipientId = array();
            while ($row = $result->fetch_assoc()) {
                array_push($recipientId, $row['subscriberId']);
            }
            $db->reset();
            foreach ($recipientId as $r) {
                $db->insert('mailqueue', array('campaignId'=>$id, 'subscriberId'=>$r));
                $db->execute();
                $db->reset();
            }
            $db->update('campaign', array('status'=>2));
            $db->where('id', '=', $id);
            $db->execute();
            $db->reset();
            header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign/edit/{$id}");
            die();
        }
        
        /**
         * Pauses campaign
         *
         * @param int $id - Campaign ID
         */
        function pauseCampaign($id) {
            set_time_limit(0);
            ignore_user_abort(true);
            $db = $this->classes['db'];
            $db->update('mailqueue', array('status'=>2));
            $db->where('campaignId', '=', $id);
            $db->andWhere('status', '!=', 1);
            $db->execute();
            $db->reset();
            $db->update('campaign', array('status'=>3));
            $db->where('id', '=', $id);
            $db->execute();
            $db->reset();
            header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign/edit/{$id}");
            die();
        }
        
        /**
         * Resumes campaign
         *
         * @param int $id - Campaign ID
         */
        function resumeCampaign($id) {
            set_time_limit(0);
            ignore_user_abort(true);
            $db = $this->classes['db'];
            $db->update('mailqueue', array('status'=>0));
            $db->where('campaignId', '=', $id);
            $db->andWhere('status', '!=', 1);
            $db->execute();
            $db->reset();
            $db->update('campaign', array('status'=>2));
            $db->where('id', '=', $id);
            $db->execute();
            $db->reset();
            header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign/edit/{$id}");
            die();
        }
        
        /**
         * Deletes the selected campaign
         */
        function delete() {
            set_time_limit(0);
            ignore_user_abort(true);
            if (!$this->classes['auth']->validateFormToken('campaignEditToken', $_POST['token'])) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign");
                die();
            }
            $db = $this->classes['db'];
            $db->update('campaign', array('status'=>0));
            $db->where('id', '=', $_POST['campaignId']);
            $db->execute();
            $db->reset();
            $db->update('mailqueue', array('status'=>3));
            $db->where('campaignId', '=', $_POST['campaignId']);
            $db->execute();
            $db->reset();
            header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign");
            die();
        }
        
        /**
         * Image upload handler for TinyMCE
         */
        function uploadImage() {
            $accepted_origins = array("http://localhost", "http://{$_SERVER['SERVER_NAME']}");
            $imageFolder = "img/campaign/";
            reset($_FILES);
            $temp = current($_FILES);
            if (is_uploaded_file($temp['tmp_name'])) {
                if (isset($_SERVER['HTTP_ORIGIN'])) {
                    if (in_array($_SERVER['HTTP_ORIGIN'], $accepted_origins)) {
                        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
                    } else {
                        header("HTTP/1.0 403 Origin Denied");
                        return;
                    }
                }
                
                if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
                    header("HTTP/1.0 500 Invalid file name.");
                    return;
                }

                if (!in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), array("gif", "jpg", "png"))) {
                    header("HTTP/1.0 500 Invalid extension.");
                    return;
                }

                $filetowrite = $imageFolder . $temp['name'];
                move_uploaded_file($temp['tmp_name'], $filetowrite);
                echo json_encode(array('location' => $filetowrite));
            } else {
                header("HTTP/1.0 500 Server Error");
                die();
            }
        }
        
        /**
         * Creates and returns a trackable link in JSON
         */
        function traceLink() {
            $link = $_POST['link'];
            $id = $_POST['id'];
            $rand = substr(md5(microtime()),rand(0,26),5);
            $db = $this->classes['db'];
            $db->insert('links', array('direction'=>$link, 'code'=>$rand, 'campaignId'=>$id));
            $db->execute();
            $insertId = $db->getConnection()->insert_id;
            $db->reset();
            header('Content-Type: application/json');
            echo json_encode(array('link'=>"http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign/link/{$rand}", 'id'=>$insertId));
            die();
        }
        
        /**
         * Removes the trackable link from the database
         */
        function untraceLink() {
            $link = $_POST['id'];
            $db = $this->classes['db'];
            $db->delete('links');
            $db->where('id', '=', $link);
            $db->execute();
            $db->reset();
            header('Content-Type: application/json');
            echo json_encode(array('status'=>true));
            die();
        }
        
        /**
         * Redirects the user to link's destination and record a semi-unique interaction
         *
         * @param string $shortCode - Trackable link's code
         */
        function link($shortCode) {
            $db = $this->classes['db'];
            $db->select('links', array('id', 'direction', 'campaignId'));
            $db->where('code', '=', $shortCode);
            $db->additional('LIMIT 1');
            $result = $db->execute();
            if (!$result->num_rows) {
                $db->reset();
                header("location: http://{$_SERVER['SERVER_NAME']}");
                die();
            }
            $link = $result->fetch_assoc();
            $db->reset();
            // Creates a cookie if it doesn't exist
            if (!isset($_COOKIE['mailmaid_click'])) {
                $id = substr(uniqid('', true), -8);
                setcookie('mailmaid_click', json_encode(array('id'=>$id)), time() + (86400 * 7));
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign/link/{$shortCode}");
                die();
            }
            // Gets cookie information
            $code = json_decode($_COOKIE['mailmaid_click'])->id;
            $ipAddress = ip2long($this->classes['auth']->getIPAddress());
            $db->select('linkclicks', array('id', 'count'));
            $db->where('ipAddress', '=', $ipAddress);
            $db->andWhere('code', '=', $code);
            $db->andWhere('linkId', '=', $link['id']);
            $result = $db->execute();
            $linkClick = $result->fetch_assoc();
            $db->reset();
            if (!$result->num_rows) {
                $db->insert('linkclicks', array('linkId'=>$link['id'], 'ipAddress'=>$ipAddress, 'code'=>$code));
                $db->execute();
                $db->reset();
                header("location: {$link['direction']}");
                die();
            }
            $db->update('linkclicks', array('count'=>$linkClick['count'] + 1));
            $db->where('id', '=', $linkClick['id']);
            $db->execute();
            $db->reset();
            header("location: {$link['direction']}");
            die();
        }
        
        /**
         * Generates a random RGBA color
         *
         * @return string - RGBA formatted color
         */
        function colorRandomizer() {
            $rgbColor = array();
            foreach (array('r', 'g', 'b') as $color) {
                $rgbColor[$color] = mt_rand(0,255);
            }
            $rgbColor = 'rgba(' . $rgbColor['r'] . ',' . $rgbColor['g'] . ',' . $rgbColor['b'] . ',0.8';
            return $rgbColor;
        }
        
        /**
         * Returns campaign engagement data from last 24 hours
         *
         * @param int $id - Campaign ID
         */
        function campaignEngagement24Hours($id) {
            $db = $this->classes['db']->getConnection();
            $stmt = $db->prepare('SELECT id, code FROM links WHERE campaignId = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($linkId, $linkCode);
            $stmt->store_result();
            while ($stmt->fetch()) {
                $result[$linkId]['label'] = $linkCode;
                $result[$linkId]['color'] = $this->colorRandomizer();
                $result[$linkId]['data'] = array();
                $stmt1 = $db->prepare("SELECT CONCAT(HOUR(dateCreated), ':00-', HOUR(dateCreated) + 1, ':00') AS hours, COUNT(id) as count FROM linkclicks WHERE dateCreated >= NOW() - INTERVAL 1 DAY and linkId = ? GROUP BY hours");
                $stmt1->bind_param('i', $linkId);
                $stmt1->execute();
                $stmt1->bind_result($hours, $count);
                while ($stmt1->fetch()) {
                    array_push($result[$linkId]['data'], array($hours=>$count));
                }
                $stmt1->close();
            }
            $stmt->free_result();
            $stmt->close();
            header('Content-Type: application/json');
            echo !empty($result) ? json_encode($result) : 'false';
            die();
        }
        
        /**
         * Returns campaign engagement data from last 7 days
         *
         * @param int $id - Campaign ID
         */
        function campaignEngagement7Days($id) {
            $db = $this->classes['db']->getConnection();
            $stmt = $db->prepare('SELECT id, code FROM links WHERE campaignId = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($linkId, $linkCode);
            $stmt->store_result();
            while ($stmt->fetch()) {
                $result[$linkId]['label'] = $linkCode;
                $result[$linkId]['color'] = $this->colorRandomizer();
                $result[$linkId]['data'] = array();
                $stmt1 = $db->prepare("SELECT CONCAT(MONTH(dateCreated), '/', DAY(dateCreated), '/', YEAR(dateCreated)) AS days, COUNT(id) as count FROM linkclicks WHERE dateCreated >= NOW() - INTERVAL 7 DAY AND linkId = ? GROUP BY days");
                $stmt1->bind_param('i', $linkId);
                $stmt1->execute();
                $stmt1->bind_result($days, $count);
                while ($stmt1->fetch()) {
                    array_push($result[$linkId]['data'], array($days=>$count));
                }
                $stmt1->close();
            }
            $stmt->free_result();
            $stmt->close();
            header('Content-Type: application/json');
            echo !empty($result) ? json_encode($result) : 'false';
            die();
        }
        
        /**
         * Returns campaign engagement data from last year
         *
         * @param int $id - Campaign ID
         */
        function campaignEngagementYear($id) {
            $db = $this->classes['db']->getConnection();
            $stmt = $db->prepare('SELECT id, code FROM links WHERE campaignId = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($linkId, $linkCode);
            $stmt->store_result();
            while ($stmt->fetch()) {
                $result[$linkId]['label'] = $linkCode;
                $result[$linkId]['color'] = $this->colorRandomizer();
                $result[$linkId]['data'] = array();
                $stmt1 = $db->prepare("SELECT MONTH(dateCreated) AS months, COUNT(id) as count FROM linkclicks WHERE YEAR(dateCreated) = YEAR(NOW()) and linkId = ? GROUP BY months");
                $stmt1->bind_param('i', $linkId);
                $stmt1->execute();
                $stmt1->bind_result($months, $count);
                while ($stmt1->fetch()) {
                    array_push($result[$linkId]['data'], array($months=>$count));
                }
                $stmt1->close();
            }
            $stmt->free_result();
            $stmt->close();
            header('Content-Type: application/json');
            echo !empty($result) ? json_encode($result) : 'false';
            die();
        }
        
        /**
         * Returns the mails sent data from last 24 hours
         *
         * @param  int $id               - Campaign ID
         * @param  boolean [$return = false] - If true, returns a JSON array
         * @return array[any] - Returns either JSON or PHP array
         */
        function mailSent24Hours($id, $return = false) {
            $db = $this->classes['db']->getConnection();
            $stmt = $db->prepare("SELECT CONCAT(HOUR(dateModified), ':00-', HOUR(dateModified) + 1, ':00') AS hours, COUNT(id) as count, status FROM mailqueue WHERE dateModified >= NOW() - INTERVAL 1 DAY and campaignId = ? GROUP BY hours, status ORDER BY status ASC");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($days, $count, $status);
            $result['sent'] = array();
            $result['failed'] = array();
            while ($stmt->fetch()) {
                if ($status == 1) {
                    $result['sent'][$days] = $count;
                } else {
                    $result['failed'][$days] = $count;
                }
            }
            if (!$return) {
                header('Content-Type: application/json');
                echo json_encode($result);
                die();
            }
            return $result;
        }
        
        /**
         * Returns the mails sent data from last 7 days
         *
         * @param  int $id               - Campaign ID
         * @param  boolean [$return = false] - If true, returns a JSON array
         * @return array[any] - Returns either JSON or PHP array
         */
        function mailSent7Days($id, $return = false) {
            $db = $this->classes['db']->getConnection();
            $stmt = $db->prepare("SELECT CONCAT(MONTH(dateModified), '/', DAY(dateModified), '/', YEAR(dateModified)) AS days, COUNT(id) as count, status FROM mailqueue WHERE dateModified >= NOW() - INTERVAL 7 DAY and campaignId = ? GROUP BY days, status ORDER BY status ASC");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($hours, $count, $status);
            $result['sent'] = array();
            $result['failed'] = array();
            while ($stmt->fetch()) {
                if ($status == 1) {
                    $result['sent'][$hours] = $count;
                } else {
                    $result['failed'][$hours] = $count;
                }
            }
            if (!$return) {
                header('Content-Type: application/json');
                echo json_encode($result);
                die();
            }
            return $result;
        }
        
        /**
         * Returns the mails sent data from last year
         *
         * @param  int $id               - Campaign ID
         * @param  boolean [$return = false] - If true, returns a JSON array
         * @return array[any] - Returns either JSON or PHP array
         */
        function mailSentYear($id, $return = false) {
            $db = $this->classes['db']->getConnection();
            $stmt = $db->prepare("SELECT MONTH(dateModified) AS month, COUNT(id) as count, status FROM mailqueue WHERE YEAR(dateModified) = YEAR(NOW()) and campaignId = ? GROUP BY month, status ORDER BY status ASC");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($months, $count, $status);
            $result['sent'] = array();
            $result['failed'] = array();
            while ($stmt->fetch()) {
                if ($status == 1) {
                    $result['sent'][$months] = $count;
                } else {
                    $result['failed'][$months] = $count;
                }
            }
            if (!$return) {
                header('Content-Type: application/json');
                echo json_encode($result);
                die();
            }
            return $result;
        }
        
        /**
         * Prepares web mail version of the selected campaign
         *
         * @param  int $campaignId
         * @param  string $email
         * @param  int $emailId
         * @return array[any]
         */
        function webmail($campaignId, $email, $emailId) {
            $result = array();
            $db = $this->classes['db'];
            $db->select('campaign', array('content'));
            $db->join('content', 'campaign.contentId = content.id', 'INNER');
            $db->where('campaign.id', '=', $campaignId);
            $db->andWhere('status', '>', 1);
            $db->additional('LIMIT 1');
            $campaign = $db->execute();
            if (!$campaign->num_rows) {
                $db->reset();
                header("location: http://{$_SERVER['SERVER_NAME']}");
                die();
            }
            $db->reset();
            $campaign = $campaign->fetch_assoc();
            $result['body'] = $campaign['content'];
            $db->select('mailqueue', array('mailqueue.id'));
            $db->join('subscribers', 'mailqueue.subscriberId = subscribers.id', 'INNER');
            $db->where('campaignId', '=', $campaignId);
            $db->andWhere('status', '=', 1);
            $db->andWhere('subscribers.id', '=', $emailId);
            $db->andWhere('emailAddress', '=', $email);
            $db->additional('LIMIT 1');
            $isLegitMail = $db->execute()->num_rows;
            $db->reset();
            if (!$isLegitMail) {
                $db->reset();
                header("location: http://{$_SERVER['SERVER_NAME']}");
                die();
            }
            $result['email'] = $email;
            $result['emailId'] = $emailId;
            return $result;
        }
    }
