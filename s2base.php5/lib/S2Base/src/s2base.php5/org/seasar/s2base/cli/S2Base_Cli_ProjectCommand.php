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
class S2Base_Cli_ProjectCommand {
    /**
     * @param string $s2baseDir S2Base PEAR directory
     * @param array  $args      command line arguments
     */
    public static function exectute($s2baseDir, $args) {
        $projectType = 'default';
        $projectDir  = null;

        if (isset($args[2])) {
            $projectDir = $args[2];
        }

        if (is_null($projectDir)){
            exectuteHelpCommand($s2baseDir, $args);
            return;
        }

        if (isset($args[3])) {
            $projectType = $args[3];
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

        print "[INFO ] s2base  directory : $s2baseDir"   . PHP_EOL;
        print "[INFO ] project directory : $projectDir"  . PHP_EOL;
        print "[INFO ] project type      : $projectType" . PHP_EOL;

        switch($projectType) {
            case 'zf':
                self::zfProjectHandler($s2baseDir, $projectDir, $projectType);
                break;
            case 'cmd':
            case 'command':
                self::cmdProjectHandler($s2baseDir, $projectDir, $projectType);
                break;
            default:
                self::defaultProjectHandler($s2baseDir, $projectDir, $projectType);
                break;
        }
    }

    /**
     * @param string $s2baseDir S2Base PEAR directory
     * @param string $projectDir
     * @param string $projectType default|smarty|zf
     */
    public static function zfProjectHandler($s2baseDir, $projectDir, $projectType) {
        $includePattern = array('/project.build\.xml$/');
        $includePattern[] = '/project.app$/';
        $includePattern[] = '/project.app.commons/';
        $includePattern[] = '/project.app.modules/';
        $includePattern[] = '/project.config/';
        $includePattern[] = '/project.lib/';
        $includePattern[] = '/project.public$/';
        $includePattern[] = '/project.public.images/';
        $includePattern[] = '/project.public.css/';
        $includePattern[] = '/project.public.htaccess\.sample$/';
        $includePattern[] = '/project.public.z\.php$/';
        $includePattern[] = '/project.test/';
        $includePattern[] = '/project.var/';
        $includePattern[] = '/project.vendor$/';
        $includePattern[] = '/project.vendor.plugins/';
//        $includePattern[] = '/project.vendor.plugins$/';
//        $includePattern[] = '/project.vendor.plugins.zf/';

        $excludePattern = array('/dummy$/');
//        $excludePattern[] = '/project.vendor.plugins.zf.src/';
//        $excludePattern[] = '/project.vendor.plugins.zf.test/';
//        $excludePattern[] = '/project.vendor.plugins.zf.build\.xml$/';

        $srcDir = $s2baseDir . DIRECTORY_SEPARATOR . 'project';
        self::dircopy($srcDir, $projectDir, $includePattern, $excludePattern);
        self::modifyBuildXmlFile($projectDir, $projectType);
        self::modifyZfEnvFile($projectDir, $projectType);
        self::renameZfHtaccess($projectDir);
    }

    /**
     * @param string $s2baseDir S2Base PEAR directory
     * @param string $projectDir
     * @param string $projectType default|smarty|zf
     */
    public static function cmdProjectHandler($s2baseDir, $projectDir, $projectType) {
        $includePattern = array('/project.build\.xml$/');
        $includePattern[] = '/project.app/';
        $includePattern[] = '/project.config/';
        $includePattern[] = '/project.lib/';
        $includePattern[] = '/project.test/';
        $includePattern[] = '/project.var$/';
        $includePattern[] = '/project.var.logs/';
        $includePattern[] = '/project.vendor$/';
        $includePattern[] = '/project.vendor.s2base/';

        $excludePattern = array('/dummy$/');
        $excludePattern[] = '/project.app.commons.view/';

        $srcDir = $s2baseDir . DIRECTORY_SEPARATOR . 'project';
        self::dircopy($srcDir, $projectDir, $includePattern, $excludePattern);
    }

    /**
     * @param string $s2baseDir S2Base PEAR directory
     * @param string $projectDir
     * @param string $projectType default|smarty|zf
     */
    public static function defaultProjectHandler($s2baseDir, $projectDir, $projectType) {
        $includePattern = array();
/*
        $includePattern = array('/project.build\.xml$/');
        $includePattern[] = '/project.app/';
        $includePattern[] = '/project.config/';
        $includePattern[] = '/project.lib/';
        $includePattern[] = '/project.test/';
        $includePattern[] = '/project.var$/';
        $includePattern[] = '/project.var.logs/';
        $includePattern[] = '/project.vendor/';
*/
        $excludePattern = array('/dummy$/');
//        $excludePattern[] = '/project.app.commons.view/';

        $srcDir = $s2baseDir . DIRECTORY_SEPARATOR . 'project';
        self::dircopy($srcDir, $projectDir, $includePattern, $excludePattern);
    }

    /**
     * @param string $src
     * @param string $dest
     * @param array  $includePattern
     * @param array  $excludePattern
     */
    public static function dircopy($src,$dest,$includePattern = array(),$excludePattern = array()) {
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
    public static function modifyBuildXmlFile($projectDir, $projectType) {
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

    /**
     * @param string $projectDir
     * @param string $projectType default|smarty|zf
     */
    public static function modifyZfEnvFile($projectDir, $projectType) {
        $envFile = $projectDir 
                 . DIRECTORY_SEPARATOR 
                 . 'config'
                 . DIRECTORY_SEPARATOR 
                 . 'environment.inc.php';
        if (!file_exists($envFile)) {
            print "[ERROR] file not found [$envFile] " . PHP_EOL;
            exit;
        }
        $iniContents = file_get_contents($envFile);
        if ($iniContents === false) {
            print "[ERROR] could not read file [$envFile] " . PHP_EOL;
            exit;
        }
        $pattern = "/'\.class\.php'/";
        $replacement = "'.php'";
        $iniContents = preg_replace($pattern, $replacement, $iniContents, 1);
        $result = file_put_contents($envFile, $iniContents, LOCK_EX);
        if ($result === false) {
            print "[ERROR] could not write file [$envFile] " . PHP_EOL;
            exit;
        }
        print "[INFO ] modify : $envFile" . PHP_EOL;
    }

    /**
     * @param string $projectDir
     */
    public static function renameZfHtaccess($projectDir) {
        $htFile = $projectDir 
                . DIRECTORY_SEPARATOR 
                . 'public'
                . DIRECTORY_SEPARATOR 
                . 'htaccess.sample';
        $newFile = $projectDir 
                 . DIRECTORY_SEPARATOR 
                 . 'public'
                 . DIRECTORY_SEPARATOR 
                 . '.htaccess';
        if (!file_exists($htFile)) {
            print "[ERROR] file not found [$htFile] " . PHP_EOL;
            exit;
        }

        if (file_exists($newFile)) {
            print "[INFO ] file exist. ignore. [$newFile] " . PHP_EOL;
            return;
        }

        $result = rename($htFile, $newFile);
        if ($result === false) {
            print "[ERROR] could not rename file [$htFile to $newFile] " . PHP_EOL;
            exit;
        }
        print "[INFO ] modify : $newFile" . PHP_EOL;
    }
}
