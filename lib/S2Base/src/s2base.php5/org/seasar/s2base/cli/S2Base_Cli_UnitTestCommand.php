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
class S2Base_Cli_UnitTestCommand {
    private static $testClasses;

    /**
     * @param string $s2baseDir S2Base PEAR directory
     * @param array  $args      command line arguments
     */
    public static function execute($s2baseDir, $args) {
        $projectType = s2base_cli_getProjectTypeFromBuildXmlFile(getcwd());
        $projectDir  = getcwd();
        $testTarget = '.+Test';
        
        if (isset($args[2])) {
            $testTarget = $args[2];
        }

        print "[INFO ] s2base  directory : $s2baseDir"   . PHP_EOL;
        print "[INFO ] project directory : $projectDir"  . PHP_EOL;
        print "[INFO ] project type      : $projectType" . PHP_EOL;
        print "[INFO ] test target       : $testTarget"  . PHP_EOL . PHP_EOL;

        ini_set('include_path','lib' . PATH_SEPARATOR . ini_get('include_path'));
        require_once('config/environment.inc.php');
        require_once('S2Base/S2Base.cmd.php');
        switch($projectType) {
            case 'zf':
                require_once('vendor/plugins/zf/config/environment.inc.php');
                break;
            default:
                break;
        }

        if (!defined("PHPUnit_MAIN_METHOD")) {
            define("PHPUnit_MAIN_METHOD", "");
        }
        require_once('PHPUnit/TextUI/TestRunner.php');

        $suite = new PHPUnit_Framework_TestSuite('Unit Test');

        self::$testClasses = array();
        self::findTestClasses($projectDir . DIRECTORY_SEPARATOR . 'test');
        foreach(self::$testClasses as $testClass => $testFile) {
            if (!preg_match('/' . $testTarget . '/', $testFile)) {
                continue;
            }
            require_once($testFile);
            $classRef = new ReflectionClass($testClass);
            if ($classRef->isAbstract() or $classRef->isInterface()) {
                continue;
            }
            $suite->addTest(new PHPUnit_Framework_TestSuite($classRef));
        }
        PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * @param string $root
     * @param array  $includePattern
     */
    public static function findTestClasses($root) {
        $items = scandir($root);
        if ($items === false) {
            print "[ERROR] scan directory [$root] failed." . PHP_EOL;
            exit;
        }

        foreach ($items as $item) {
            if (preg_match('/^\./', $item)){ 
                continue;
            }
            $rootItem  = $root  . DIRECTORY_SEPARATOR . $item;
            if (is_dir($rootItem)) {
                self::findTestClasses($rootItem);
            }
            else if (is_file($rootItem)) {
                $matches = array();
                if (preg_match('/(.+?Test)\..*php/', $item, $matches)) {
                    self::$testClasses[$matches[1]] = $rootItem;
                }
            }
            else {
                print "[ERROR] invalid item [$rootItem] " . PHP_EOL;
                exit;
            }
        }
    }
}
