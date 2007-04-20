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
 * @deprecated
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.0.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.0
 * @package    org.seasar.s2base.web.impl
 * @author     klove
 */
class S2Base_CacheUtil {

    public static function init(S2Base_Request $request){

        if(!defined('S2BASE_PHP5_CACHE_ON') or !S2BASE_PHP5_CACHE_ON){
            return;
        }

        $cacheDir = S2BASE_PHP5_VAR_DIR . "/cache/" . 
                    $request->getModule() . "_" .
                    $request->getAction();
        if(!is_dir($cacheDir)){
            if(!mkdir($cacheDir)){
                throw new S2Base_RuntimeException('ERR105',array($cacheDir));
            }
        }

        ini_set('include_path', 
                $cacheDir . PATH_SEPARATOR . ini_get('include_path'));
        
        if(!defined('S2AOP_PHP5_FILE_CACHE')){
            define('S2AOP_PHP5_FILE_CACHE',true);
            define('S2AOP_PHP5_FILE_CACHE_DIR',$cacheDir);
            define('S2CONTAINER_PHP5_CACHE_DIR',$cacheDir);
        }
        //S2ContainerFileCacheFactory::$INITIALIZE_BEFORE_CACHE = true;      
    } 
}
?>
