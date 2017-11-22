<?php
    /**
     * View for settings edit
     * 
     * @package Mailmaid
     * @subpackage views
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\views;

    class SettingsView extends View {
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
            parent::__construct('Mailmaid | Settings', array('form.css'), array(), array('menu.js', 'account.js'));
            $this->params = $params;
        }

        /**
         * Contains the HTML content to be displayed on the user.
         */
        function content() {
            echo "
                <form action='http://{$_SERVER['SERVER_NAME']}/mailmaid/settings/update' method='post' id='account.js'>
                    <label for='businessName'>Business Name</label>
                    <p class='disclaimer'>This is the sender name shown to the recipient.</p>
                    <input id='businessName' name='business' type='text' placeholder='Business Name' value='{$this->params['settings']['businessName']}' required maxlength='32'>
                    <label for='freq'>Mails Per Hour</label>
                    <p class='disclaimer'>Change at your own risk. Contact your hosting provider to know how much mail you can send per hour. Failure to follow provider's allocation can lead to account banning.</p>
                    <input name='frequency' min='12' type='number' id='freq' placeholder='Mails Per Hour' value='{$this->params['settings']['mailRate']}'>
                    <label for='priority'>Campaign Execution</label>
                    <p class='disclaimer'>This is how the system will send mail when running multiple campaigns.</p>
                    <select name='distribution' id='priority'>
                        <option value='single' {$this->params['settings']['singleMode']}}>Single (First campaign first)</option>
                        <option value='parallel' {$this->params['settings']['parallelMode']}>Parallel (Equally divided sending)</option>
                    </select>
                    <input type='submit' value='Update Settings'>
                </form>
            ";
        }
    }
