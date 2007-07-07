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
class S2Base_Cli_DefaultCommandHandler extends S2Base_Cli_AbstractCommandHandler {

    /**
     * @see S2Base_Cli_AbstractCommandHandler::projectHandler()
     */
    public function projectHandler(){
        self::validateProjectDir($this->projectDir);

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

        $srcDir = $this->s2baseDir . DIRECTORY_SEPARATOR . 'project';
        self::dircopy($srcDir, $this->projectDir, $includePattern, $excludePattern);
    }

    /**
     * @see S2Base_Cli_AbstractCommandHandler::commandHandler()
     */
    public function commandHandler(){
        ini_set('include_path','lib' . PATH_SEPARATOR . ini_get('include_path'));
        require_once('config/environment.inc.php');
        require_once('S2Base/S2Base.cmd.php');
        require_once('S2Base/S2Base.phing.php');
        $pattern = $this->projectDir
                 . DIRECTORY_SEPARATOR . 'vendor'
                 . DIRECTORY_SEPARATOR . 's2base'
                 . DIRECTORY_SEPARATOR . 'commands'
                 . DIRECTORY_SEPARATOR . '*.php';
        $launcher = S2Base_CommandLauncherFactory::create(glob($pattern));
        $launcher->main();
    }

    /**
     * @see S2Base_Cli_AbstractCommandHandler::testHandler()
     */
    public function testHandler(){
        $testTargetPattern = '.+Test';
        if (isset($this->cmdLineArgs[2])) {
            $testTargetPattern = $this->cmdLineArgs[2];
        }
        print "[INFO ] test target       : $testTargetPattern"  . PHP_EOL . PHP_EOL;

        ini_set('include_path','lib' . PATH_SEPARATOR . ini_get('include_path'));
        require_once('config/environment.inc.php');
        require_once('S2Base/S2Base.cmd.php');
        $this->runUnitTest($this->projectDir . DIRECTORY_SEPARATOR . 'test', $testTargetPattern);
    }
}