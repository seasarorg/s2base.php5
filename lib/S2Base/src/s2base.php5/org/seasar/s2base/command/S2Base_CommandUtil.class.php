<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright 2005-2006 the Seasar Foundation and the Others.            |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// |                                                                      |
// |     http://www.apache.org/licenses/LICENSE-2.0                       |
// |                                                                      |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,                        |
// | either express or implied. See the License for the specific language |
// | governing permissions and limitations under the License.             |
// +----------------------------------------------------------------------+
// | Authors: klove                                                       |
// +----------------------------------------------------------------------+
//
// $Id:$
/**
 * @package org.seasar.s2base.command
 * @author klove
 */
class S2Base_CommandUtil {

    public static function createDirectory($path){
        if(!file_exists($path)){
            if(!mkdir($path)){
               throw new Exception("Cannot make dir [ $path ]");
            }
            return true;
        }else{
            return false;
        }
    }

    public static function readFile($src){
        if(!is_readable($src)){
            throw new Exception("Cannot read file [ $src ]");
        }
        return file_get_contents($src);
    }

    public static function writeFile($filename,$content){
        if (file_exists($filename)) {
            throw new S2Base_FileExistsException("Already exists. [ $filename ]");
        }

        if(!file_put_contents($filename,$content,LOCK_EX)){
            throw new Exception("Cannot write to file [ $filename ]");
        }
    }    

    public static function getModuleName(){
        $modules = self::getAllModules();
        if(count($modules) == 0){
            throw new Exception("Module not found at all.");
        }
        return S2Base_StdinManager::getValueFromArray($modules,"Module list");
    }
        
    public static function getAllModules(){
        $entries = scandir(S2BASE_PHP5_APP_DIR . "modules");
        $modules = array();
        foreach($entries as $entry) {
            $path = S2BASE_PHP5_MODULES_DIR . $entry;
            if(!preg_match("/^\./",$entry) and is_dir($path)){
                array_push($modules,$entry);
            }
        }
        return $modules;
    }        

    public static function validate($name,$msg){
        if(!preg_match("/^\w+$/",$name)){
           throw new Exception($msg);
        }   
    }
}
?>
