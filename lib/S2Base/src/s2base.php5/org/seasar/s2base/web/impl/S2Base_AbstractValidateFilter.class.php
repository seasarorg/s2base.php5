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
// $Id: S2Base_AbstractValidateFilter.class.php 278 2007-04-20 11:31:31Z klove $
/**
 * withSmarty WEBフレームワークのフィルタークラス
 * 
 * このabstractクラスを継承してvalidateフィルターを実装します。
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.0.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.0
 * @package    org.seasar.s2base.web.impl
 * @author     klove
 */
abstract class S2Base_AbstractValidateFilter 
    extends S2Base_AbstractBeforeFilter {
    const VALIDATE_DIR = '/validate/';
    /**
     * @var array validateルールを格納した配列
     */
    protected $rule;
    
    /**
     * validateを実装します。
     */
    abstract public function validate();

    /**
     * validateルールを設定するINIファイルのサフィックスを返します。
     */
    abstract public function getSuffix();

    /**
     * app/modules/module名/validate/action名.suffix.iniファイルから
     * validateルールを読み込みます。
     * 
     * @see S2Base_AbstractFilterInterceptor::invoke()
     * @see S2Base_FilterInterceptor::before()
     */
    public function before(){
        if($this->rule == null){
            $ruleFile = S2BASE_PHP5_ROOT
                      . '/app/modules/' 
                      . $this->moduleName
                      . self::VALIDATE_DIR
                      . $this->actionName
                      . '.'
                      . $this->getSuffix()
                      . '.ini';
            if(is_readable($ruleFile)){
                $this->rule = parse_ini_file($ruleFile,true);
            }
        }
        return $this->validate();   
    }
}
?>
