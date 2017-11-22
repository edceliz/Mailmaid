<?php
    /**
     * View for campaign list.
     * 
     * @package Mailmaid
     * @subpackage views
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\views;

    class CampaignWebViewView extends View {
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
            parent::__construct('Mailmaid | Web View', array('webview.css'), array(), array());
            $this->params = $params;
        }
        
        function nav() {
            echo "<div id='body' class='body'>
                <p id='page-title' class='page-title'>Mailmaid | Web View</p>";
        }

        /**
         * Contains the HTML content to be displayed on the user.
         */
        function content() {
            echo "<div class='container'>{$this->params['body']}
                <p style='background-color: #95a5a6; padding: 10px; text-align: center; margin-top: 10px; border-top-left-radius: 2px; border-top-right-radius: 2px;'><a href='http://{$_SERVER['SERVER_NAME']}/mailmaid/subscriber/unsubscribe/{$this->params['email']}/{$this->params['emailId']}' style='font-family: Arial; color: #333;'>Click here to unsubscribe from our updates</a></p>
            </div>";
        }
    }
