<?php
    /**
     * View for account list.
     * 
     * @package Mailmaid
     * @subpackage views
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\views;

    class AccountView extends View {
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
            parent::__construct('Mailmaid | Accounts', array('account.css', 'card.css'), array(), array('menu.js'));
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
                            <li class='blue'><a href='/mailmaid/account/register'>Create Account</a></li>
                        </ul>
                    </div>
                </div>
            ";
            
            foreach($this->params['accounts'] as $v) {
                echo "
                    <div class='card'>
                        <p class='card-logo'><span class='fa fa-user'></span></p>
                        <p class='card-title'>{$v[1]} - {$v[2]}</p>
                        <div class='card-actions'>
                            <ul>
                                <li class='blue'><a href='http://{$_SERVER['SERVER_NAME']}/mailmaid/account/edit/{$v[0]}'>Edit Account</a></li>
                            </ul>
                        </div>
                    </div>
                ";
            }
            
            echo '</div>';
        }
    }
