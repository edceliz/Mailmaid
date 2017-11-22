<?php
    /**
     * Used to perform repetitive common actions on controllers.
     * 
     * @package Mailmaid
     * @subpackage controllers
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app\Controllers;

    class Controller {
        /**
         * Used to get an instance of the selected model.
         * 
         * @param string $model
         * @param array[classes] $params|array()
         * @return \app\Controllers\model
         */
        function getModel($model, $params = array()) {
            $model = "app\\models\\" . $model . "Model";
            return new $model($params);
        }
        
        /**
         * Used to get an instance of the selected view.
         * 
         * @param string $view
         * @param array[string|int|boolean] $params
         * @return \app\Controllers\view
         */
        function getView($view, $params = array()) {
            $view = "app\\views\\" . $view . "View";
            return new $view($params);
        }
        
        /**
         * Serves as the error action of the controller.
         * 
         * @param array[string] $params
         */
        function error($params = array()) {
            header('location: /');
        }
    }
?>
