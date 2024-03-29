<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright 2005-2007 the Seasar Foundation and the Others.            |
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
// $Id$
/**
 * 実行時例外クラス
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.0.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.0
 * @package    org.seasar.s2base.exception
 * @author     klove
 */
class S2Base_RuntimeException extends Exception {
    public function __construct($id,$args = array()){
        switch($id){
            case 'ERR102':
                $msg = "invalid name [{$args[0]} : {$args[1]}]";
                break;
            case 'ERR103':
                $msg = "module not found [{$args[0]}]";
                break;
            case 'ERR105':
                $msg = "cache dir create fail [{$args[0]}]";
                break;
            case 'ERR106':
                $msg = "invalid redirect target [{$args[0]}]";
                break;
            case 'ERR107':
                $msg = "cycle redirect occured [{$args[0]}] [ " . 
                       implode(' -> ',$args[1]) . 
                       " ] ";
                break;
            case 'ERR108':
                $msg = "invalid action result [{$args[0]}]";
                break;
            case 'ERR109':
                $msg = "template file[{$args[2]}] not found. [ module : {$args[0]}, action : {$args[1]} ]";
                break;
            default:
                $msg = implode($args);
        }
        parent::__construct($msg);
    }   
}
?>
