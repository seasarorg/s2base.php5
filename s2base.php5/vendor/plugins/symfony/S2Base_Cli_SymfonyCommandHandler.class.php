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
// | Authors: klove kiyo-shit                                             |
// +----------------------------------------------------------------------+
//
// $Id:$
/**
 * @author klove kiyo-shit
 */
class S2Base_Cli_SymfonyCommandHandler extends S2Base_Cli_AbstractCommandHandler {

    /**
     * @see S2Base_Cli_AbstractCommandHandler::projectHandler()
     */
    public function projectHandler(){
        self::validateProjectDir($this->projectDir);
        require_once $this->s2baseDir . 
            '/project/vendor/plugins/symfony/commands/S2Base_SymfonyCommandUtil.class.php';
        S2Base_SymfonyCommandUtil::execSfCmd('init-project', 's2base', $this->projectDir);
        
        $includePattern = array('/project.build\.xml$/');
        $includePattern[] = '/project.vendor$/';
        $includePattern[] = '/project.vendor.s2base/';
        $includePattern[] = '/project.vendor.plugins$/';
        $includePattern[] = '/project.vendor.plugins.symfony/';
        $excludePattern = array('/dummy$/');
        $srcDir = $this->s2baseDir . DIRECTORY_SEPARATOR . 'project';
        self::dircopy($srcDir, $this->projectDir, $includePattern, $excludePattern);
        $this->copyProjectConfigFiles();
        $this->modifyBuildXmlFile($this->projectDir, 'symfony');
        $this->modifyConfigFile($this->projectDir);
    }

    /**
     * @see S2Base_Cli_AbstractCommandHandler::commandHandler()
     */
    public function commandHandler(){
        ini_set('include_path','lib' . PATH_SEPARATOR . ini_get('include_path'));
        require_once($this->projectDir . '/config/config.php');
        require_once('S2Base/S2Base.php');
        $pattern = $this->projectDir
                 . DIRECTORY_SEPARATOR . 'vendor'
                 . DIRECTORY_SEPARATOR . 'plugins'
                 . DIRECTORY_SEPARATOR . 'symfony'
                 . DIRECTORY_SEPARATOR . 'commands'
                 . DIRECTORY_SEPARATOR . '*.php';
        $launcher = S2Base_CommandLauncherFactory::create(glob($pattern));
        $launcher->main();
    }

    private function modifyConfigFile($projectDir) {
        $confFile = $projectDir 
                  . DIRECTORY_SEPARATOR 
                  . 'config'
                  . DIRECTORY_SEPARATOR 
                  . 'config.php';
        if (!file_exists($confFile)) {
            print "[ERROR] file not found [$confFile] " . PHP_EOL;
            exit;
        }
        $iniContents = file_get_contents($confFile);
        if ($iniContents === false) {
            print "[ERROR] could not read file [$confFile] " . PHP_EOL;
            exit;
        }
        $result = file_put_contents($confFile, "require_once 's2base_sf.inc.php';\n", FILE_APPEND);
        if ($result === false) {
            print "[ERROR] could not write file [$confFile] " . PHP_EOL;
            exit;
        }
        print "[INFO ] modify : $confFile" . PHP_EOL;
    }

    private function copyProjectConfigFiles()
    {
        $diconDir = $this->s2baseDir
                  . DIRECTORY_SEPARATOR
                  . 'project'
                  . DIRECTORY_SEPARATOR
                  . 'app'
                  . DIRECTORY_SEPARATOR
                  . 'commons'
                  . DIRECTORY_SEPARATOR
                  . 'dicon'
                  . DIRECTORY_SEPARATOR;
        $pluginDir = $this->s2baseDir
                   . DIRECTORY_SEPARATOR
                   . 'project'
                   . DIRECTORY_SEPARATOR
                   . 'vendor'
                   . DIRECTORY_SEPARATOR
                   . 'plugins'
                   . DIRECTORY_SEPARATOR
                   . 'symfony'
                   . DIRECTORY_SEPARATOR
                   . 'skeletons'
                   . DIRECTORY_SEPARATOR
                   . 'config'
                   . DIRECTORY_SEPARATOR;
        $srcFiles = array(
            'pdo.dicon' => $diconDir,
            'dao.dicon' => $diconDir,
            's2base_sf.inc.php' => $pluginDir
        );
        foreach ($srcFiles as $item => $srcDir) {
            if (!copy(
                $srcDir . $item,
                $this->projectDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $item
            )){
                print "[ERROR] file copy [$item] failed." . PHP_EOL;
                exit;
            } else {
                print "[INFO ] create : " . $this->projectDir 
                    . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $item . PHP_EOL;
            }
        }
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
        require_once($this->projectDir . '/config/config.php');
        require_once('S2Base/S2Base.php');
        $this->runUnitTest(
            $this->projectDir . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'unit',
            $testTargetPattern
        );
    }

}
