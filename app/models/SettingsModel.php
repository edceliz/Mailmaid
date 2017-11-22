<?php
    /**
     * Model for managing settings
     *
     * @package Mailmaid
     * @subpackage models
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\models;

    class SettingsModel {
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
         * Returns array of current configuration
         * @param  array[any] [$params = array()]
         * @return array[] - Array of settings
         */
        function index($params = array()) {
            if ($this->classes['auth']->checkLevel() == 2) {
                header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid");
                exit();
            }
            $this->result['settings'] = json_decode(file_get_contents('../app/settings.json'), true);
            $this->result['settings']['singleMode'] = $this->result['settings']['executionMode'] == 'single' ? 'selected' : '';
            $this->result['settings']['parallelMode'] = $this->result['settings']['executionMode'] == 'parallel' ? 'selected' : '';
            return $this->result;
        }
        
        /**
         * Updates the settings from the submitted request.
         */
        function update() {
            $settings = array('mailRate'=>$_POST['frequency'], 'executionMode'=>$_POST['distribution'], 'businessName'=>$_POST['business'], 'serverName'=>$_SERVER['SERVER_NAME']);
            file_put_contents('../app/settings.json', json_encode($settings));
            header("location: http://{$_SERVER['SERVER_NAME']}/mailmaid/settings");
            exit();
        }
    }
