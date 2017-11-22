<?php
    /**
     * View for subscriber list add form.
     * 
     * @package Mailmaid
     * @subpackage views
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\views;

    class SubscriberAddView extends View {
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
            parent::__construct('Mailmaid | Add List', array('subscribers.css', 'form.css'), array(), array('menu.js'));
            $this->params = $params;
        }

        /**
         * Contains the HTML content to be displayed on the user.
         */
        function content() {
            echo "
                <form action='http://{$_SERVER['SERVER_NAME']}/mailmaid/subscriber/add' method='post'>
                    <input name='token' type='hidden' value='{$this->params['token']}'>
                    <label for='username'>Name</label>
                    <input name='name' placeholder='Name' id='name' type='text' required>
                    <label for='description'>Description</label>
                    <input name='description' placeholder='Description (Optional)' id='description' type='text'>
                    <input type='submit' value='Create List'>
                </form>
            ";
        }
    }

?>


