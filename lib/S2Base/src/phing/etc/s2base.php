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

/**
 * Initial setting
 */
$s2baseDir   = dirname(dirname(__FILE__));
$args        = $_SERVER['argv'];
$projectType = 'default';
$projectDir  = null;

/**
 * Command Line Arguments setting
 */
if (isset($args[1])) {
    $projectDir = $args[1];
}

if (is_null($projectDir) or
    strtolower($projectDir) == '--help' or 
    strtolower($projectDir) == '-h') {
    print "[INFO ] Usage: % s2base <project directory> [smarty]" . PHP_EOL;
    exit;
}
if (isset($args[2])) {
    $projectType = $args[2];
}

/**
 * Project directory validation
 */
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

/**
 * Main
 */
print "[INFO ] s2base  directory : $s2baseDir"   . PHP_EOL;
print "[INFO ] project directory : $projectDir"  . PHP_EOL;
print "[INFO ] project type      : $projectType" . PHP_EOL;

switch($projectType) {
    case 'smarty':
        smartyProjectHandler($s2baseDir, $projectDir, $projectType);
        break;
    default:
        defaultProjectHandler($s2baseDir, $projectDir, $projectType);
        break;
}

exit;
/**
 * End of Main
 */
 
 
/**
 * Functions 
 */
/**
 * @param string $src
 * @param string $dest
 * @param array  $excludes
 */
function dircopy($src,$dest,$excludes) {
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

        $isExclude = false;
        foreach ($excludes as $key) {
            if (preg_match($key, $srcItem)){ 
                $isExclude = true;
            }
        }
        if ($isExclude) {
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
            dircopy($srcItem,$destItem,$excludes);
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

function modifyBuildXmlFile($projectDir, $projectType) {
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
}

/**
 *
 */
function smartyProjectHandler($s2baseDir, $projectDir, $projectType) {
    $excludes = array('/dummy$/');
    $excludes[] = '/plugins.maple$/';
    $excludes[] = '/plugins.agavi$/';
    $excludes[] = '/plugins.symfony$/';
    $srcDir = $s2baseDir . DIRECTORY_SEPARATOR . 'project';
    dircopy($srcDir,$projectDir,$excludes);
    modifyBuildXmlFile($projectDir, $projectType);
}

/**
 *
 */
function defaultProjectHandler($s2baseDir, $projectDir, $projectType) {
    $excludes = array('/dummy$/');
    $excludes[] = '/var.smarty$/';
    $excludes[] = '/var.session$/';
    $excludes[] = '/plugins.smarty$/';
    $excludes[] = '/public$/';
    $excludes[] = '/commons.view$/';
    $srcDir = $s2baseDir . DIRECTORY_SEPARATOR . 'project';
    dircopy($srcDir,$projectDir,$excludes);
}

/**
 * End of Functions
 */
?>
