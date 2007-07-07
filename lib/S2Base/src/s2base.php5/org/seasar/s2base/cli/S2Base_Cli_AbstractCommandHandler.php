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
abstract class S2Base_Cli_AbstractCommandHandler {
    protected $testClasses = array();
    protected $s2baseDir   = null;
    protected $projectDir  = null;
    protected $cmdLineArgs = null;
    public function setS2BaseDir($path) {
        $this->s2baseDir = $path;
    }
    public function setProjectDir($path) {
        $this->projectDir = $path;
    }
    public function setCmdLineArgs(array $args) {
        $this->cmdLineArgs = $args;
    }
    abstract public function projectHandler();
    abstract public function commandHandler();
    abstract public function testHandler();

    /**
     * @see S2Base_Cli_AbstractCommandHandler::externalHandler()
     */
    public function externalHandler(){
        print "[INFO ] command [{$this->cmdLineArgs[1]}] not implemented." . PHP_EOL;
    }

    public static function validateProjectDir($projectDir) {
        if (!is_dir($projectDir)) {
            $parent = dirname($projectDir);
            if (is_dir($parent) and is_writable($parent)) {
                if (!mkdir($projectDir)) {
                    print "[ERROR] mkdir [$projectDir] failed." . PHP_EOL;
                    exit;
                }
            }
        }

        if (!is_dir($projectDir)) {
            print "[ERROR] project directory [$projectDir] not found." . PHP_EOL;
            exit;
        }
    }

    /**
     * @param string $src
     * @param string $dest
     * @param array  $includePattern
     * @param array  $excludePattern
     */
    public static function dircopy($src, $dest, $includePattern = array(), $excludePattern = array()) {
        $items = scandir($src);
        if ($items === false) {
            print "[ERROR] scan directory [$src] failed." . PHP_EOL;
            exit;
        }

        foreach ($items as $item) {
            if (preg_match('/^\./', $item)){ 
                continue;
            }

            $srcItem  = $src  . DIRECTORY_SEPARATOR . $item;
            $destItem = $dest . DIRECTORY_SEPARATOR . $item;

            if (!self::isIncludeFile($srcItem, $includePattern, $excludePattern)) {
                continue;
            }

            if (is_dir($srcItem)) {
                if (is_dir($destItem)) {
                    print "[INFO ] exsist : $destItem" . PHP_EOL;
                }
                else if (!mkdir($destItem)){
                    print "[ERROR] mkdir [$destItem] failed." . PHP_EOL;
                    exit;
                } 
                else {
                    print "[INFO ] create : $destItem" . PHP_EOL;
                }
                self::dircopy($srcItem,$destItem,$includePattern, $excludePattern);
            }
            else if (is_file($srcItem)) {
                if (is_file($destItem)) {
                    print "[INFO ] exsist : $destItem" . PHP_EOL;
                }
                else if (!copy($srcItem,$destItem)) {
                    print "[ERROR] file copy [$srcItem, $destItem] failed." . PHP_EOL;
                    exit;
                }
            }
            else {
                print "[ERROR] invalid item [$srcItem, $destItem] " . PHP_EOL;
                exit;
            }
        }
    }

    /**
     * @param string $item
     * @param array  $includePattern
     * @param array  $excludePattern
     */
    public static function isIncludeFile($item, $includePattern, $excludePattern) {
        $hit = false;
        foreach ($includePattern as $pattern) {
            if (preg_match($pattern, $item)){ 
                $hit = true;
            }
        }

        if (count($includePattern) > 0 and !$hit) {
            return false;
        }

        foreach ($excludePattern as $pattern) {
            if (preg_match($pattern, $item)){ 
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $projectDir
     * @param string $projectType default|smarty|zf
     */
    protected function modifyBuildXmlFile($projectDir, $projectType) {
        $buildFile = $projectDir . DIRECTORY_SEPARATOR . 'build.xml';
        if (!file_exists($buildFile)) {
            print "[ERROR] file not found [$buildFile] " . PHP_EOL;
            exit;
        }
        $xml = file_get_contents($buildFile);
        if ($xml === false) {
            print "[ERROR] could not read file [$buildFile] " . PHP_EOL;
            exit;
        }
        $pattern = '/\sdefault="command"\s/';
        $replacement = ' default="' . $projectType . '" ';
        $xml = preg_replace($pattern, $replacement, $xml, 1);
        $result = file_put_contents($buildFile, $xml, LOCK_EX);
        if ($result === false) {
            print "[ERROR] could not write file [$buildFile] " . PHP_EOL;
            exit;
        }
        print "[INFO ] modify : $buildFile" . PHP_EOL;
    }

    protected function runUnitTest($testDir, $targetPattern) {
        if (!defined("PHPUnit_MAIN_METHOD")) {
            define("PHPUnit_MAIN_METHOD", "");
        }
        require_once('PHPUnit/TextUI/TestRunner.php');

        $suite = new PHPUnit_Framework_TestSuite('Unit Test');

        $this->testClasses = array();
        $this->findTestClasses($testDir);
        foreach($this->testClasses as $testClass => $testFile) {
            if (!preg_match('/' . $targetPattern . '/', $testFile)) {
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

    protected function findTestClasses($root) {
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
                $this->findTestClasses($rootItem);
            }
            else if (is_file($rootItem)) {
                $matches = array();
                if (preg_match('/(.+?Test)\..*php/', $item, $matches)) {
                    $this->testClasses[$matches[1]] = $rootItem;
                }
            }
            else {
                print "[ERROR] invalid item [$rootItem] " . PHP_EOL;
                exit;
            }
        }
    }
}

class Task {}
