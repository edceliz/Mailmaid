<?php
    /**
     * View for a list of subscriber list
     * 
     * @package Mailmaid
     * @subpackage views
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\views;

    class SubscriberView extends View {
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
            parent::__construct('Mailmaid | Subscribers', array('subscribers.css', 'card.css'), array(), array('menu.js'));
            $this->params = $params;
        }

        /**
         * Contains the HTML content to be displayed on the user.
         */
        function content() {
            echo "<div class='container'>";
            if ($this->params['edit']) {
                echo "
                    <div class='card'>
                        <p class='card-logo'><span class='fa fa-plus'></span></p>
                        <div class='card-actions'>
                            <ul>
                                <li class='blue'><a href='http://{$_SERVER['SERVER_NAME']}/mailmaid/subscriber/add'>Create List</a></li>
                            </ul>
                        </div>
                    </div>
                ";
            }
            foreach($this->params['list'] as $a) {
                echo "
                    <div class='card'>
                        <p class='card-logo'><span class='fa fa-users'></span></p>
                        <p class='card-title'>{$a[1]}</p>
                        <p class='card-title'>ID: {$a[0]}</p>
                        <div class='card-actions'>
                            <ul>
                                <li class='blue'><a href='http://{$_SERVER['SERVER_NAME']}/mailmaid/subscriber/view/{$a[0]}'>Open List</a></li>
                            </ul>
                        </div>
                    </div>
                ";
            }
            echo "</div>";
        }
    }
