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
class S2Base_CommandLauncherFactory {

    public static function create($classFiles){
        $launcher = new S2Base_CommandLauncher();

        foreach($classFiles as $classFile){
            if(preg_match("/(\w+)\.class\.php$/",$classFile,$matches)){
                $cmdClassName = $matches[1];
                if(!class_exists($cmdClassName,false)){
                    require_once($classFile);
                }
                if(!class_exists($cmdClassName,false)){
                    continue;
                }
                $cmdObj = new $cmdClassName();
                if($cmdObj instanceof S2Base_GenerateCommand){
                    $launcher->addCommand($cmdObj);
                }
            }
        }

        return $launcher;
    }
}
?>
