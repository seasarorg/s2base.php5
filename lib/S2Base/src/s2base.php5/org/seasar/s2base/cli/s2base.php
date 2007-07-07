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

require_once(dirname(__FILE__) . '/S2Base_Cli_ProjectCommand.php');
require_once(dirname(__FILE__) . '/S2Base_Cli_CmdListCommand.php');
require_once(dirname(__FILE__) . '/S2Base_Cli_UnitTestCommand.php');

/**
 * Main
 */
$s2baseDir = dirname(dirname(__FILE__));
$args      = $_SERVER['argv'];

if (isset($args[1])) {
    switch (strtolower($args[1])) {
        case 'pro':
        case 'project':
            S2Base_Cli_ProjectCommand::exectute($s2baseDir, $args);
            break;
        case 'cmd':
        case 'command':
            S2Base_Cli_CmdListCommand::execute($s2baseDir, $args);
            break;
        case 'test':
            S2Base_Cli_UnitTestCommand::execute($s2baseDir, $args);
            break;
        default:
            s2base_cli_exectuteHelpCommand($s2baseDir, $args);
            break;
    }
} else {
    S2Base_Cli_CmdListCommand::execute($s2baseDir, $args);
}

exit;
/**
 * End of Main
 */

/**
 * Functions 
 */

/**
 * @param string $s2baseDir S2Base PEAR directory
 * @param array  $args      command line arguments
 */
function s2base_cli_exectuteHelpCommand($s2baseDir, $args) {
    print '
[INFO ] usage: % s2base [project|command|test|help] [option]
[INFO ]     project: create s2base project directory.
[INFO ]         usage: % s2base project [project dir] [none|cmd|zf]
[INFO ]
[INFO ]     command: start command launcher.
[INFO ]         usage: % s2base command
[INFO ]
[INFO ]     test: execute unit test.
[INFO ]         usage: % s2base test [target pattern]
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
