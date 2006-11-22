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
class S2Base_StdinManager {
    const EXIT_LABEL = "(exit)";
    
    public static function getValueFromArray($cmds, $title){
        $cmds = array_merge(array(self::EXIT_LABEL),$cmds);
        $number = null;
        while(true){
            print "\n[ $title ]\n";
            foreach($cmds as $key=>$module){
                print "$key : $module\n";
            }

            print "choice ? : ";
            $val = trim(fgets(STDIN));
            if(strcasecmp($val,'q') == 0){
                $number = 0;
                break;
            } else if (is_numeric($val) and
                       array_key_exists($val,$cmds)) {
                $number = $val;
                break;
            }
        }
        return $cmds[$number];
    }

    public static function getValuesFromArray($cmds, $title){
        $cmds = array_merge(array(self::EXIT_LABEL),$cmds);
        $items = null;
        while(true){
            print "\n[ $title ]\n";
            $items = array();
            foreach($cmds as $key => $val){
                print "$key : $val\n";
            }
            print "choices ? (1,2,--,) : ";
            $inputVal = trim(fgets(STDIN));

            if(strcasecmp($inputVal,'q') == 0 or
               strcasecmp($inputVal,'0') == 0 ){
                $items[] = $cmds[0];
                break;
            }

            $nums = explode(',', $inputVal);
            foreach ($nums as $num) {
                $num = trim($num);
                if (is_numeric($num) and
                    $num != '0' and
                    array_key_exists($num, $cmds)) {
                    $items[] = $cmds[$num];
                }
            }
            if (count($items) > 0) {
                break;
            }
        }
        return array_unique($items);
    }

    public static function getValue($msg){
        print "\n$msg";
        $val = trim(fgets(STDIN));
        return $val;
    }

    public static function isYes($msg){
        $ret = false;

        while(true){
            print "\n$msg (y/n) : ";
            $val = trim(fgets(STDIN));

            if(strcasecmp($val,'y') == 0){
                $ret = true;
                break;
            }else if (strcasecmp($val,'n') == 0){
                $ret = false;
                break;
            }
        }

        return $ret;
    }
}
?>
