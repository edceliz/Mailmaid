<?php
    /**
     * View for home view charts.
     * 
     * @package Mailmaid
     * @subpackage views
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\views;

    class HomeView extends View {
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
            parent::__construct('Mailmaid | Dashboard', array('index.css', 'form.css', 'card.css', 'chart.css'), array('chart.js', 'ajax.js'), array('menu.js', 'homeGraph.js'));
            $this->params = $params;
        }
        
        /**
         * Contains the HTML content to be displayed on the user.
         */
        function content() {
            echo "
                <div class='canvas'>
                    <canvas id='audienceGrowth'></canvas>
                    <button id='ug-1' type='button'>Last 24 Hours</button>
                    <button id='ug-2' type='button'>Last 7 Days</button>
                    <button id='ug-3' type='button'>This Year</button>
                </div>
            ";
            
            echo "
                <div class='canvas'>
                    <canvas id='campaignEngagement'></canvas>
                    <button id='ce-1' type='button'>Last 24 Hours</button>
                    <button id='ce-2' type='button'>Last 7 Days</button>
                    <button id='ce-3' type='button'>This Year</button>
                </div>
            ";
            
            echo "
                <div class='canvas'>
                    <canvas id='mailSent'></canvas>
                    <button id='ms-1' type='button'>Last 24 Hours</button>
                    <button id='ms-2' type='button'>Last 7 Days</button>
                    <button id='ms-3' type='button'>This Year</button>
                </div>
            ";
        }
    }

?>
