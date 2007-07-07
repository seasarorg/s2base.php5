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
class S2Base_Cli_CmdListCommand {

    /**
     * @param string $s2baseDir S2Base PEAR directory
     * @param array  $args      command line arguments
     */
    public static function execute($s2baseDir, $args) {
        $projectType = s2base_cli_getProjectTypeFromBuildXmlFile(getcwd());
        $projectDir  = getcwd();

        print "[INFO ] s2base  directory : $s2baseDir"   . PHP_EOL;
        print "[INFO ] project directory : $projectDir"  . PHP_EOL;
        print "[INFO ] project type      : $projectType" . PHP_EOL;

        ini_set('include_path','lib' . PATH_SEPARATOR . ini_get('include_path'));
        require_once('config/environment.inc.php');
        require_once('S2Base/S2Base.cmd.php');
        require_once('S2Base/S2Base.phing.php');
        switch($projectType) {
            case 'zf':
                require_once('config/s2base_zf.inc.php');
                $pathRegex = $projectDir
                           . DIRECTORY_SEPARATOR . 'vendor'
                           . DIRECTORY_SEPARATOR . 'plugins'
                           . DIRECTORY_SEPARATOR . 'zf'
                           . DIRECTORY_SEPARATOR . 'commands'
                           . DIRECTORY_SEPARATOR . '*.php';
                $launcher = S2Base_CommandLauncherFactory::create(glob($pathRegex));
                break;
            default:
                $pathRegex = $projectDir
                           . DIRECTORY_SEPARATOR . 'vendor'
                           . DIRECTORY_SEPARATOR . 's2base'
                           . DIRECTORY_SEPARATOR . 'commands'
                           . DIRECTORY_SEPARATOR . '*.php';
                $launcher = S2Base_CommandLauncherFactory::create(glob($pathRegex));
                break;
        }

        $launcher->main();
    }
}

class Task {}
