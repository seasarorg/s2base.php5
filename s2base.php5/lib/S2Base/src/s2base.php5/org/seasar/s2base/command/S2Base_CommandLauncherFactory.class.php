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
 * S2Base_CommandLauncherのファクトリークラスです。
 * 
 * @copyright  2005-2006 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.0.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.0
 * @package    org.seasar.s2base.command
 * @author     klove
 */
class S2Base_CommandLauncherFactory {

    /**
     * コマンドランチャーを作成し、コマンドクラスをランチャーに登録します。
     * 
     * @param array $classFiles コマンドクラスの配列
     * @return S2Base_CommandLauncher
     */
    public static function create($classFiles) {
        $launcher = new S2Base_CommandLauncher();

        foreach ($classFiles as $classFile) {
            $fileInfo = pathinfo($classFile);
            if (strtolower($fileInfo['extension']) == 'php' and 
                preg_match("/^(\w+)/",$fileInfo['basename'],$matches)) {
                $cmdClassName = $matches[1];
                if (!class_exists($cmdClassName,false)) {
                    require_once($classFile);
                }
                if (!class_exists($cmdClassName,false)) {
                    continue;
                }
                $ref = new ReflectionClass($cmdClassName);
                if (!$ref->isAbstract() and 
                    !$ref->isInterface() and
                    $ref->isSubclassOf('S2Base_GenerateCommand')) {
                    $launcher->addCommand(new $cmdClassName());
                }
            }
        }
        return $launcher;
    }
}
?>
