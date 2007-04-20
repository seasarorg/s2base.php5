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
 * withSmarty WEBフレームワークのリクエストインターフェイス
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.0.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.0
 * @package    org.seasar.s2base.web
 * @author     klove
 */
interface S2Base_Request {
    /**
     * モジュール名とアクション名の最大文字数
     */
    const MAX_LEN = 50;

    /**
     * モジュール名を返します。
     * 
     * @return string モジュール名
     */
    public function getModule();

    /**
     * アクション名を返します。
     * 
     * @return string アクション名
     */
    public function getAction();
    
    /**
     * キーで登録されているパラメータ値を返します。
     * 
     * @param string $key キー
     * @return string パラメータ値
     */
    public function getParam($key);
    
    /**
     * キーでパラメータ値を登録します。
     * 
     * @param string $key キー
     * @param string $val パラメータ値
     */
    public function setParam($key,$val);
    
    /**
     * キーにパラメータが登録されているかを確認します。
     * 
     * @param string $key キー
     * @return boolean 
     */
    public function hasParam($key);
}
?>
