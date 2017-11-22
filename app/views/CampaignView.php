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

    class CampaignView extends View {
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
            parent::__construct('Mailmaid | Campaigns', array('card.css'), array(), array('menu.js'));
            $this->params = $params;
        }

        /**
         * Contains the HTML content to be displayed on the user.
         */
        function content() {
            echo "<div class='container'>
                <div class='card'>
                    <p class='card-logo'><span class='fa fa-plus'></span></p>
                    <div class='card-actions'>
                        <ul>
                            <li class='blue'><a href='/mailmaid/campaign/add'>Create Campaign</a></li>
                        </ul>
                    </div>
                </div>
            ";
            
            foreach($this->params['campaign'] as $campaign) {
                echo "
                    <div class='card'>
                        <p class='card-logo'><span class='fa fa-bullhorn'></span></p>
                        <p class='card-title'>{$campaign['name']}</p>
                        <div class='card-actions'>
                            <ul>
                                <li class='blue'><a href='http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign/edit/{$campaign['id']}'>Open Campaign</a></li>
                            </ul>
                        </div>
                    </div>
                ";
            }
            
            echo '</div>';
        }
    }
