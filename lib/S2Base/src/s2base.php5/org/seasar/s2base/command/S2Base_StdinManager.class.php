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
 * 標準入力のサポートクラスです。
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.0.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.0
 * @package    org.seasar.s2base.command
 * @author     klove
 */
class S2Base_StdinManager {
    const EXIT_LABEL = "(exit)";
    
    /**
     * 選択項目の一覧から1項目を取得します。
     * 
     * @param array $cmds 選択項目の配列
     * @param string $title 選択リストのタイトル文字列
     * @return string 選択された項目文字列
     */
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

    /**
     * 選択項目の一覧から複数項目を取得します。
     * 
     * @param array $cmds 選択項目の配列
     * @param string $title 選択リストのタイトル文字列
     * @return array 選択された項目
     */
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

    /**
     * 標準入力から一行文字列を取得します。
     * 
     * @param string $msg メッセージ
     * @return string 標準入力から取得した文字列
     */
    public static function getValue($msg){
        print "\n$msg";
        $val = trim(fgets(STDIN));
        return $val;
    }

    /**
     * y/n選択結果を取得します。
     * 
     * @param string $msg メッセージ
     * @return boolean
     */
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
