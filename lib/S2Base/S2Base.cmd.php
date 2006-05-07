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
 * @author klove
 */
if(!defined('STDIN')){
    define('STDIN',fopen("php://stdin","r"));
}

define('S2BASE_PHP5_DS', DIRECTORY_SEPARATOR);
define('S2BASE_PHP5_APP_DIR',  S2BASE_PHP5_ROOT . S2BASE_PHP5_DS . "app"  . S2BASE_PHP5_DS);
define('S2BASE_PHP5_TEST_DIR', S2BASE_PHP5_ROOT . S2BASE_PHP5_DS . "test" . S2BASE_PHP5_DS);

define('S2BASE_PHP5_MODULES_DIR',      S2BASE_PHP5_APP_DIR  . "modules"  . S2BASE_PHP5_DS);
define('S2BASE_PHP5_SKELETON_DIR',     S2BASE_PHP5_APP_DIR  . "skeleton" . S2BASE_PHP5_DS);
define('S2BASE_PHP5_COMMANDS_DIR',     S2BASE_PHP5_APP_DIR  . "commands" . S2BASE_PHP5_DS);
define('S2BASE_PHP5_TEST_MODULES_DIR', S2BASE_PHP5_TEST_DIR . "modules"  . S2BASE_PHP5_DS);

define('S2BASE_PHP5_ACTION_DIR',      S2BASE_PHP5_DS . "action" .      S2BASE_PHP5_DS);
define('S2BASE_PHP5_DAO_DIR',         S2BASE_PHP5_DS . "dao" .         S2BASE_PHP5_DS);
define('S2BASE_PHP5_DICON_DIR',       S2BASE_PHP5_DS . "dicon" .       S2BASE_PHP5_DS);
define('S2BASE_PHP5_ENTITY_DIR',      S2BASE_PHP5_DS . "entity" .      S2BASE_PHP5_DS);
define('S2BASE_PHP5_INTERCEPTOR_DIR', S2BASE_PHP5_DS . "interceptor" . S2BASE_PHP5_DS);
define('S2BASE_PHP5_SERVICE_DIR',     S2BASE_PHP5_DS . "service" .     S2BASE_PHP5_DS);
define('S2BASE_PHP5_VIEW_DIR',        S2BASE_PHP5_DS . "view" .        S2BASE_PHP5_DS);

require_once('build/s2base.php5/s2base.cmd.classes.php')

?>
