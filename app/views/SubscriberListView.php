<?php
    /**
     * View for details of the selected list.
     * 
     * @package Mailmaid
     * @subpackage views
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\views;

    class SubscriberListView extends View {
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
            parent::__construct('Mailmaid | List', array('form.css', 'card.css', 'chart.css'), array('chart.js', 'ajax.js'), array('menu.js', 'soloUserGrowth.js', 'delete.js'));
            $this->params = $params;
        }

        /**
         * Contains the HTML content to be displayed on the user.
         */
        function content() {
            echo "
                <form id='form' action='http://{$_SERVER['SERVER_NAME']}/mailmaid/subscriber/update' method='post'>
                    <input name='token' type='hidden' value='{$this->params['token']}'>
                    <input name='listId' type='hidden' value='{$this->params['listId']}'>
                    <label for='name'>Name</label>
                    <input name='name' placeholder='Name' id='name' value='{$this->params['name']}' type='text' required {$this->params['disabled']}>
                    <label for='description'>Description</label>
                    <input name='description' placeholder='Description (Optional)' id='description' value='{$this->params['description']}' type='text' {$this->params['disabled']}>
                    <label for='id'>ID</label>
                    <input name='id' placeholder='ID' id='id' type='number' disabled>
            ";
            
            if ($this->params['submit']) {
                echo "
                    <input id='update' name='update' type='submit' value='Update List'>
                    <input class='delete' id='delete' name='delete' type='submit' value='Delete List'>
                ";
            } else {
                echo "
                    <p>&nbsp;</p>
                ";
            }
            
            echo "
                </form>
                <div class='container'>
                    <div class='card'>
                        <p class='card-title'>{$this->params['subCount']} Subscribers</p>
                    </div>
                </div>
            ";
            
            echo "
                <div class='canvas'>
                    <canvas id='userGrowth'></canvas>
                    <button id='getHours' type='button'>Last 24 Hours</button>
                    <button id='getWeek' type='button'>Last 7 Days</button>
                    <button id='getMonth' type='button'>This Year</button>
                    <button id='getAll' type='button'>Yearly</button>
                </div>
            ";
        }
    }

?>
