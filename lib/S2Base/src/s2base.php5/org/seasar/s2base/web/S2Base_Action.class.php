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
// $Id: S2Base_Action.class.php 278 2007-04-20 11:31:31Z klove $
/**
 * withSmarty WEBフレームワークのアクションインターフェイス
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.0.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.0
 * @package    org.seasar.s2base.web
 * @author     klove
 */
interface S2Base_Action {

    /**
     * アクション処理を実行します。
     * 
     * @param S2Base_Request $request リクエストのラッパーオブジェクト
     * @param S2Base_View $view Smartyオブジェクト
     * @return string|null ビューテンプレートファイル名を返します。 
     *                     nullを返した場合はアクション名からビューテンプレートファイル名が導出されます。
     */
    public function execute(S2Base_Request $request, S2Base_View $view);
}
?>
