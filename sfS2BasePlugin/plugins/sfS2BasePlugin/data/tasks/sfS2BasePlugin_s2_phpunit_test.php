<?php
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php');

pake_desc('PHPUnit test with S2Base');
pake_task('s2_phpunit_test', 'app_exists');
pake_alias('s2test', 's2_phpunit_test');

function run_s2_phpunit_test($task, $args) {
    $pluginName = sfS2BasePluginConfig::PLUGIN_NAME;
    $app = $args[0];
    require_once(sfConfig::get('sf_test_dir') . '/bootstrap/functional.php');
    isset($args[1]) ? $targetPattern = $args[1] : $targetPattern = '.*Test';

    sfS2BasePlugin_util_echo_comment('');
    sfS2BasePlugin_util_echo_comment('sfS2BasePlugin s2_phpunit_test task');
    sfS2BasePlugin_util_echo_comment('');
    sfS2BasePlugin_util_echo_comment("Application    : $app");
    sfS2BasePlugin_util_echo_comment('SF_ROOT_DIR    : ' . SF_ROOT_DIR);
    sfS2BasePlugin_util_echo_comment('SF_ENVIRONMENT : ' . SF_ENVIRONMENT);
    sfS2BasePlugin_util_echo_comment('SF_DEBUG       : ' . (SF_DEBUG ? 'true' : 'false'));
    sfS2BasePlugin_util_echo_comment("Target Pattern : $targetPattern");
    sfS2BasePlugin_util_echo_comment('');

    $testDir = sfConfig::get('sf_test_dir') . '/unit';

    if (!defined("PHPUnit_MAIN_METHOD")) {
        define("PHPUnit_MAIN_METHOD", "");
    }
    require_once('PHPUnit/TextUI/TestRunner.php');

    $suite = new PHPUnit_Framework_TestSuite('Unit Test');

    $testClasses = array();
    sfS2BasePlugin_s2_phpunit_test_find_test($testDir, $testClasses);
    foreach($testClasses as $testFile => $testClass) {
        if (!preg_match('/' . $targetPattern . '/', $testFile)) {
            continue;
        }
        require_once($testFile);
        $classRef = new ReflectionClass($testClass);
        if ($classRef->isAbstract() or 
            $classRef->isInterface() or 
            !$classRef->isSubclassOf(new ReflectionClass('PHPUnit_Framework_TestCase'))) {
            pake_echo_action('skip', $testFile);
            continue;
        }
        $suite->addTest(new PHPUnit_Framework_TestSuite($classRef));
    }
    if ($suite->testCount() > 0 ) {
       PHPUnit_TextUI_TestRunner::run($suite);
    } else {
        pake_echo_action('info', 'none PHPUnit test found.');
    }
}

function sfS2BasePlugin_s2_phpunit_test_find_test($root, &$spool) {
    $items = scandir($root);
    if ($items === false) {
        throw new Exception("scan directory [$root] failed.");
    }

    foreach ($items as $item) {
        if (preg_match('/^\./', $item)){ 
            continue;
        }
        $rootItem  = $root  . DIRECTORY_SEPARATOR . $item;
        if (is_dir($rootItem)) {
            sfS2BasePlugin_s2_phpunit_test_find_test($rootItem, $spool);
        }
        else if (is_file($rootItem)) {
            $matches = array();
            if (preg_match('/(.+?Test)\..*php$/', $item, $matches)) {
                $spool[$rootItem] = $matches[1];
            }
        }
        else {
            throw new Exception("invalid item [$rootItem] ");
            exit;
        }
    }
}
