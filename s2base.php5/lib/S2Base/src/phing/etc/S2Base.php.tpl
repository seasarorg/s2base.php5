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
// $Id:$
/**
 * @author klove
 */
if(!defined('STDIN')){
    define('STDIN',fopen('php://stdin','r'));
}

ini_set('include_path', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes'
                      . PATH_SEPARATOR . ini_get('include_path'));
S2ContainerClassLoader::import(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'S2DaoSkeletonTask.php');

define('S2BASE_PHP5_DS', DIRECTORY_SEPARATOR);
define('S2BASE_PHP5_APP_DIR',    S2BASE_PHP5_ROOT . S2BASE_PHP5_DS . 'app');
define('S2BASE_PHP5_TEST_DIR',   S2BASE_PHP5_ROOT . S2BASE_PHP5_DS . 'test');
define('S2BASE_PHP5_VENDOR_DIR', S2BASE_PHP5_ROOT . S2BASE_PHP5_DS . 'vendor');

define('S2BASE_PHP5_MODULES_DIR',      S2BASE_PHP5_APP_DIR  . S2BASE_PHP5_DS . 'modules');
define('S2BASE_PHP5_TEST_MODULES_DIR', S2BASE_PHP5_TEST_DIR . S2BASE_PHP5_DS . 'modules');

define('S2BASE_PHP5_ACTION_DIR',      S2BASE_PHP5_DS . 'action');
define('S2BASE_PHP5_DAO_DIR',         S2BASE_PHP5_DS . 'dao');
define('S2BASE_PHP5_DICON_DIR',       S2BASE_PHP5_DS . 'dicon');
define('S2BASE_PHP5_ENTITY_DIR',      S2BASE_PHP5_DS . 'entity');
define('S2BASE_PHP5_INTERCEPTOR_DIR', S2BASE_PHP5_DS . 'interceptor');
define('S2BASE_PHP5_SERVICE_DIR',     S2BASE_PHP5_DS . 'service');
define('S2BASE_PHP5_VIEW_DIR',        S2BASE_PHP5_DS . 'view');


