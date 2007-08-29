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

require_once(dirname(__FILE__) . '/S2Base_Cli_AbstractCommandHandler.php');
require_once(dirname(__FILE__) . '/S2Base_Cli_DefaultCommandHandler.php');

/**
 * Main
 */
$s2baseDir   = dirname(dirname(__FILE__));
$args        = $_SERVER['argv'];
$projectDir  = getcwd();
$projectType = null;
$commandType = null;
$handlerMethod = null;

if (isset($args[1])) {
    switch (strtolower($args[1])) {
        case 'pro':
        case 'project':
            $commandType   = 'project';
            $handlerMethod = 'projectHandler';
            if (isset($args[2])) {
                $projectDir = $args[2];
            } else {
                s2base_cli_exectuteHelpCommand();
                exit;
            }
            if (isset($args[3])) {
                $projectType = $args[3];
            } else {
                $projectType = 'command';
            }
            break;
        case 'cmd':
        case 'command':
            $commandType   = 'command';
            $handlerMethod = 'commandHandler';
            if (isset($args[2])) {
                $projectType = $args[2];
            } else {
                $projectType = s2base_cli_getProjectTypeFromBuildXmlFile($projectDir);
            }
            break;
        case 'test':
            $commandType   = 'test';
            $handlerMethod = 'testHandler';
            if (isset($args[3])) {
                $projectType = $args[3];
            } else {
                $projectType = s2base_cli_getProjectTypeFromBuildXmlFile($projectDir);
            }
            break;
        case '-h':
        case '--h':
        case '-help':
        case '--help':
        case 'help':
            s2base_cli_exectuteHelpCommand();
            exit;
            break;
        default:
            $commandType   = 'external';
            $handlerMethod = 'externalHandler';
            $projectType = s2base_cli_getProjectTypeFromBuildXmlFile($projectDir);
            break;
    }
} else {
    $commandType   = 'command';
    $handlerMethod = 'commandHandler';
    $projectType = s2base_cli_getProjectTypeFromBuildXmlFile($projectDir);
}
$projectType = strtolower($projectType);

print "[INFO ] s2base  directory : $s2baseDir"   . PHP_EOL;
print "[INFO ] project directory : $projectDir"  . PHP_EOL;
print "[INFO ] project type      : $projectType" . PHP_EOL;
print "[INFO ] command type      : $commandType" . PHP_EOL;

if ($projectType == 'command') {
    $project = new S2Base_Cli_DefaultCommandHandler();
    $project->setS2BaseDir($s2baseDir);
    $project->setProjectDir($projectDir);
    $project->setCmdLineArgs($args);
    $project->$handlerMethod();
} else {
    $className = 'S2Base_Cli_' . ucfirst($projectType) . 'CommandHandler';
    $classFile = $projectDir
               . DIRECTORY_SEPARATOR . 'vendor'
               . DIRECTORY_SEPARATOR . 'plugins'
               . DIRECTORY_SEPARATOR . $projectType
               . DIRECTORY_SEPARATOR . $className . '.class.php';
    if (!is_file($classFile)) {
        $classFile = $s2baseDir
                   . DIRECTORY_SEPARATOR . 'project'
                   . DIRECTORY_SEPARATOR . 'vendor'
                   . DIRECTORY_SEPARATOR . 'plugins'
                   . DIRECTORY_SEPARATOR . $projectType
                   . DIRECTORY_SEPARATOR . $className . '.class.php';
        if (!is_file($classFile)) {
            print "[ERROR] project manage class [$className] not found." . PHP_EOL;
            print "[ERROR] project type [$projectType] not supported." . PHP_EOL;
            exit;
        }
    }
    require_once($classFile);
    $project = new $className;
    if (!$project instanceof S2Base_Cli_AbstractCommandHandler) {
        print "[ERROR] project manage class [$className] found but not implements S2Base_Cli_CommandHandler" . PHP_EOL;
        exit;
    }
    $project->setS2BaseDir($s2baseDir);
    $project->setProjectDir($projectDir);
    $project->setCmdLineArgs($args);
    $project->$handlerMethod($s2baseDir, $projectDir, $args);
}
exit;
/**
 * End of Main
 */

/**
 * Functions 
 */
function s2base_cli_exectuteHelpCommand() {
    print '
[INFO ] usage: % s2base [project|command|test|help] [option]
[INFO ]     project: create s2base project directory.
[INFO ]         usage: % s2base project [project dir] [none|cmd|zf]
[INFO ]
[INFO ]     command: start command launcher.
[INFO ]         usage: % s2base command [none|cmd|zf]
[INFO ]
[INFO ]     test: execute unit test.
[INFO ]         usage: % s2base test [target pattern] [none|cmd|zf]
[INFO ]
[INFO ]     help: show this help.
[INFO ]         usage: % s2base help
';
}

/**
 * @param string $projectDir
 * @param string $projectType default|smarty|zf
 */
function s2base_cli_getProjectTypeFromBuildXmlFile($path) {
    $buildFile = $path . DIRECTORY_SEPARATOR . 'build.xml';
    if (!file_exists($buildFile)) {
        print "[ERROR] $buildFile file not found. " . PHP_EOL;
        exit;
    }
    $xml = file_get_contents($buildFile);
    if ($xml === false) {
        print "[ERROR] could not read file [$buildFile] " . PHP_EOL;
        exit;
    }
    $pattern = '/\sdefault="(.+?)"\s/';
    $matches = array();
    if (preg_match($pattern, $xml, $matches)) {
        return $matches[1];
    } else {
        print "[ERROR] could not get project type from $buildFile" . PHP_EOL;
        exit;
    }
}

/**
 * End of Functions
 */
