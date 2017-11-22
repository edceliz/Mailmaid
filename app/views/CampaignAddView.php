<?php
    /**
     * View for adding a campaign
     * 
     * @package Mailmaid
     * @subpackage views
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\views;

    class CampaignAddView extends View {
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
            parent::__construct('Mailmaid | Campaigns', array('form.css'), array(), array('menu.js'));
            $this->params = $params;
        }

        /**
         * Contains the HTML content to be displayed on the user.
         */
        function content() {
            echo "
                <form action='http://{$_SERVER['SERVER_NAME']}/mailmaid/campaign/add' method='post'>
                    <input name='token' type='hidden' value='{$this->params['token']}'>
                    <label for='campaign'>Campaign Name</label>
                    <input name='campaign' placeholder='Campaign Name' id='campaign' type='text' required>
                    <label for='subject'>Subject</label>
                    <input name='subject' placeholder='Subject' id='subject' type='text' required>
                    <label for='list'>List</label>
                    <select name='list' id='list'>
            ";
            
            foreach($this->params['list'] as $list) {
                echo "<option value='{$list['id']}'>{$list['name']}</option>";
            }
            
            echo "
                    </select>
                    <input type='submit' value='Create Campaign'>
                </form>
            ";
        }
    }
