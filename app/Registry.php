<?php
    /**
     * A static class that stores and provide instances of a class.
     * 
     * @package Mailmaid
     * @subpackage app
     * @copyright (c) 2017, Edcel Celiz
     * @author Edcel Celiz <edceliz01@gmail.com>
     */
    namespace app;
    
    class Registry {
        /**
         * Contains all the set instance of a class.
         * 
         * @var classes[]
         */
        private static $store = array();
        
        private function __construct() {}
        private function __clone() {}
        
        /**
         * Stores the the object with its corresponding key name
         * 
         * Checks if $name is null and provide the class name instead
         * 
         * @param string $name|null
         * @param class $object
         */
        static function set($name = null, $object) {
            $name = !is_null($name) ? $name : get_class($object);
            self::$store[$name] = $object;
        }
        
        /**
         * Returns the object with the corresponding key name.
         * 
         * @param string $name
         * @return object|boolean
         */
        static function get($name) {
            if (!isset(self::$store[$name])) {
                self::get('SystemLogger')->log('Can\'t fetch ' . $name . ' from registry!', 5);
                return false;
            } else {
                return self::$store[$name];
            }
        }
        
        /**
         * Returns if the object with key name exists on the registry.
         * 
         * @param string $name
         * @return boolean
         */
        static function contains($name) {
            if (!isset(self::$store[$name])) {
                return false;
            }
            return true;
        }
        
        /**
         * Removes the object with corresponding key name on the registry.
         * 
         * @param type $name
         */
        static function remove($name) {
            if (isset(self::$store[$name])) {
                unset(self::$store[$name]);
            }
        }
    }
