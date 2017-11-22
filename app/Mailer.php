<?php
    /**
     * A cron executable script to send mails
     *
     * @package Mailmaid
     * @subpackage app
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */

    require('class.phpmailer.php');

    class Mailer {
        /**
         * Contains the database configuration
         *
         * @var array[string]
         */
        private $dbConfig;

        /**
         * Instance of the database
         *
         * @var Database
         */
        private $db;

        /**
         * Contains the mailing configuration
         *
         * @var array[string]
         */
        private $mailConfig;

        /**
         * Contains the number of recipients to be executed during the round
         *
         * @var int
         */
        private $recipientCount;

        /**
         * Instance of PHPMailer
         *
         * @var PHPMailer
         */
        private $phpMailer;
        
        /**
         * Contains the name of the server
         *
         * @var string
         */
        private $serverName;

        /**
         * Assigns value to all configuration
         *
         * @param PHPMailer $mailer
         */
        function __construct($mailer) {
            $this->dbConfig = json_decode(file_get_contents('db.json'));
            @$this->db = new mysqli('localhost', $this->dbConfig->user, $this->dbConfig->pass, $this->dbConfig->db);
            $this->checkDatabaseConnection();
            $this->mailConfig = json_decode(file_get_contents('settings.json'));
            $this->serverName = $this->mailConfig->serverName;
            $this->recipientCount = floor(intval($this->mailConfig->mailRate) / 12);
            $this->phpMailer = $mailer;
        }
        
        /**
         * Checks connection to database, ends execution if failed
         */
        private function checkDatabaseConnection() {
            if ($this->db->connect_error) {
                file_put_contents('../tmp/logs/mailer.log', date('m-d-Y h:i:s A') . ' - Can\'t connect to database!' . PHP_EOL, FILE_APPEND);
                die();
            }
        }
        
        /**
         * Sends mail in a controlled manner
         *
         * Theoretical time consumption in order to complete campaign using different methods.
         * The following uses only 3 mails per hour with 4 active campaigns totalling into 22 mails
         *
         * Single Sending
         * Limit 3, Active 4 (22 Mails)
         * C1 07 04 01 00 00 00 00 00 00 00 00 00 00 (3 Rounds)
         * C2 05 05 05 05 05 02 00 00 00 00 00 00 00 (6 Rounds)
         * C3 05 05 05 05 05 05 05 05 02 00 00 00 00 (9 Rounds)
         * C4 05 05 05 05 05 05 05 05 05 05 05 02 00 (12 Rounds)
         * Throughput: 3 Rounds (15 Minutes) | Completion: 12 Rounds (60 Minutes)
         *
         * Parallel Sending
         * Limit 3, Active 4 (22 Mails)
         * C1 07 06 05 04 03 02 01 00 00 00 (7 Rounds)
         * C2 05 04 03 02 01 00 00 00 00 00 (5 Rounds)
         * C3 05 04 03 02 01 00 00 00 00 00 (5 Rounds)
         * C4 05 05 05 05 05 05 04 03 02 00 (9 Rounds)
         * Throughput: 9 Rounds (45 Minutes) | Completion: 9 Rounds (45 Minutes)
         */
        function run() {
            // Run if single execution mode
            if ($this->mailConfig->executionMode == 'single') {
                // Get the oldest active campaign's information
                $stmt = $this->db->prepare('SELECT campaign.id, subject, content FROM campaign INNER JOIN content on content.id = contentId WHERE status = 2 ORDER BY dateCreated ASC LIMIT 1');
                $stmt->execute();
                $stmt->store_result();
                if (!$stmt->num_rows) {
                    $stmt->free_result();
                    $stmt->close();
                    $this->db->close();
                    file_put_contents('../tmp/logs/mailer.log', date('m-d-Y h:i:s A') . ' - No campaign on going.' . PHP_EOL, FILE_APPEND);
                    die();
                }
                $stmt->bind_result($campaignId, $subject, $content);
                $stmt->fetch();
                $stmt->free_result();
                $stmt->close();
                $recipient = $this->getRecipient($campaignId, $this->recipientCount);
                if (!$recipient) {
                    $this->run();
                }
                $this->sendMail($campaignId, $subject, $content, $recipient);
            // Run if parallel execution mode
            } else if ($this->mailConfig->executionMode == 'parallel') {
                // Get the active campaigns' information, oldest first.
                $stmt = $this->db->prepare('SELECT campaign.id, subject, content FROM campaign INNER JOIN content on content.id = contentId WHERE status = 2 ORDER BY dateCreated ASC');
                $stmt->execute();
                $stmt->store_result();
                if (!$stmt->num_rows) {
                    $stmt->free_result();
                    $stmt->close();
                    $this->db->close();
                    file_put_contents('../tmp/logs/mailer.log', date('m-d-Y h:i:s A') . ' - No campaign on going.' . PHP_EOL, FILE_APPEND);
                    die();
                } else {
                    // Allocate balanced mail sending resource to each active campaign
                    $recipientCount = ($this->recipientCount / $stmt->num_rows) < 1 ? 1 : floor(($this->recipientCount / $stmt->num_rows));
                    $campaignIteration = $stmt->num_rows > $this->recipientCount ? $this->recipientCount : $stmt->num_rows;
                }
                $stmt->bind_result($campaignId, $subject, $content);
                // Cater to each campaign
                while ($stmt->fetch() && $campaignIteration != 0) {
                    $campaignIteration--;
                    $recipient = $this->getRecipient($campaignId, $recipientCount);
                    if ($recipient) {
                        $this->sendMail($campaignId, $subject, $content, $recipient);
                    }
                }
                $stmt->free_result();
                $stmt->close();
            }
            $this->db->close();
        }
        
        /**
         * Gets the mailing list with N amount of entries for specific campaign.
         * @param  int $id    - Campaign ID
         * @param  int $count - Number of entries to select
         * @return array[string]  - Contains the mail queue IDs and email addresses.
         */
        function getRecipient($id, $count) {
            $result = array();
            $stmt = $this->db->prepare('SELECT mailqueue.id, emailAddress FROM mailqueue INNER JOIN subscribers ON subscribers.id = subscriberId WHERE campaignId = ? AND status = 0 ORDER BY dateModified ASC LIMIT ?');
            $stmt->bind_param('ii', $id, $count);
            $stmt->execute();
            $stmt->store_result();
            if (!$stmt->num_rows) {
                $stmt->free_result();
                file_put_contents('../tmp/logs/mailer.log', date('m-d-Y h:i:s A') . " - Finished campaign #{$id}." . PHP_EOL, FILE_APPEND);
                $stmt1 = $this->db->prepare('UPDATE campaign SET status = 4 WHERE id = ? LIMIT 1');
                $stmt1->bind_param('i', $id);
                $stmt1->execute();
                $stmt1->close();
                return false;
            }
            $stmt->bind_result($queueId, $emailAddress);
            while ($stmt->fetch()) {
                $result[$queueId] = $emailAddress;
            }
            $stmt->free_result();
            $stmt->close();
            return $result;
        }

        /**
         * [[Description]]
         * @param int $campaignId
         * @param string $subject
         * @param text $content
         * @param array[string] $recipient
         */
        function sendMail($campaignId, $subject, $content, $recipient) {
            $mail = $this->phpMailer;
            $mail->isHTML(true);
            $mail->setFrom("noreply@{$this->serverName}", $this->mailConfig->businessName);
            $mail->Subject = $subject;
            $stmt = $this->db->prepare('UPDATE mailqueue SET status = ? WHERE id = ? LIMIT 1');
            $delete = array();
            // Add custom unsubscription and website version link to mail.
            foreach ($recipient as $key=>$value) {
                $unsubscribe = "<p style='background-color: #95a5a6; padding: 10px; text-align: center; margin-top: 10px; border-top-left-radius: 2px; border-top-right-radius: 2px;'><a href='http://{$this->serverName}/mailmaid/subscriber/unsubscribe/{$value}/{$key}' style='font-family: Arial; color: #333;'>Click here to unsubscribe from our updates</a></p><p style='background-color: #34495e; padding: 10px; text-align: center; margin-top: 0; border-bottom-left-radius: 2px; border-bottom-right-radius: 2px;'><a href='http://{$this->serverName}/mailmaid/campaign/webmail/{$campaignId}/{$value}/{$key}' style='font-family: Arial; color: #fff;'>Click here to view this mail's website version.</a></p>";
                $mail->Body = $content . $unsubscribe;
                $mail->AltBody = "This mail contains HTML and other resources which is not allowed by your client. You can view the website version of this mail by visiting http://{$this->serverName}/mailmaid/campaign/webmail/{$campaignId}/{$value}/{$key}";
                $mail->addAddress($value);
                if (!$mail->send()) {
                    $status = 4;
                    array_push($delete, $key);
                    $stmt->bind_param('ii', $status, $key);
                } else {
                    $status = 1;
                    $stmt->bind_param('ii', $status, $key);
                }
                $stmt->execute();
                $mail->clearAddresses();
            }
            $stmt->close();
            if ($delete) {
                $stmt = $this->db->prepare('UPDATE listsubs INNER JOIN mailqueue ON listsubs.subscriberId = mailqueue.subscriberId SET listsubs.status = 0 WHERE mailqueue.id = ?');
                foreach ($delete as $id) {
                    $stmt->bind_param('i', $id);
                    $stmt->execute();
                }
                $stmt->close();
            }
            $this->db->close();
        }
    }

    $mail = new PHPMailer();
    $mailer = new Mailer($mail);
    $mailer->run();
