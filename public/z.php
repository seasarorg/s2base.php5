<?php
S2Base_ZfStopWatch::start();

require_once(dirname(dirname(__FILE__)).'/config/environment.inc.php');
require_once(dirname(dirname(__FILE__)).'/config/s2base_zf.inc.php');

try{
    Zend_Session::regenerateId();
    S2Base_ZfInitialize::init();
    Zend_Controller_Front::getInstance()->dispatch();
}catch(Exception $e){
    Zend_Registry::get('logger')->crit($e->__toString());
}

Zend_Registry::get('logger')->info('dispatch time : ' . S2Base_ZfStopWatch::stop() . ' seconds');
exit;

class S2Base_ZfStopWatch {
    private static $start = 0;
    private static $place = 0;
    private function __construct(){}
    public static function start() {
        self::$start = self::microtime_float();
    }
    public static function place($point = true) {
        $point ? self::$place = self::microtime_float() : self::$place = 0;
    }
    public static function time() {
        $start = self::$place === 0 ? self::$start : self::$place;
        return self::microtime_float() - $start;
    }
    public static function stop() {
        return self::microtime_float() - self::$start;
    }
    public static function microtime_float(){
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }
}
