<?php
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php');

pake_desc('PHPUnit auto test with S2Base');
pake_task('s2_tool_auto_test','app_exists');
pake_alias('s2at', 's2_tool_auto_test');

function run_s2_tool_auto_test($task, $args) {
    $pluginName = sfS2BaseToolPluginConfig::PLUGIN_NAME;
    $classSuffix = '.class.php';
    $appName = $args[0];
    $testDir = sfConfig::get('sf_test_dir') . DIRECTORY_SEPARATOR . 'unit';
    sfS2BasePlugin_util_echo_comment('');
    sfS2BasePlugin_util_echo_comment('sfS2BasePlugin s2_auto_test task');
    sfS2BasePlugin_util_echo_comment('');
    sfS2BasePlugin_util_echo_comment("Application    : $appName");
    sfS2BasePlugin_util_echo_comment("Test Directory : $testDir");
    sfS2BasePlugin_util_echo_comment('');

    $testFiles = array();
    $monitorFiles = array();
    $isFirst = true;
    while (true) {
        clearstatcache();
        $testFiles = array();
        sfS2BasePlugin_s2_phpunit_test_find_test($testDir, $testFiles);
        $addFiles = array_diff(array_keys($testFiles), array_keys($monitorFiles));
        $delFiles = array_diff(array_keys($monitorFiles), array_keys($testFiles));

        foreach ($addFiles as $testFile) {
            pake_echo_action('test add', $testFile);
            $stamp = filemtime($testFile);
            $testStamp = $isFirst ? $stamp : $stamp -1;
            $testClass = $testFiles[$testFile];
            $srcClass = preg_replace('/Test$/', '', $testClass);
            $srcFile = $testFile;
            $srcFile = str_replace($testClass . $classSuffix,
                                   $srcClass  . $classSuffix, $srcFile);
            $srcFile = str_replace($testDir, sfConfig::get('sf_root_dir') . DIRECTORY_SEPARATOR . 'apps', $srcFile);
            if (file_exists($srcFile)) {
                $srcStamp = filemtime($srcFile);
            } else {
                $srcFile = null;
                $srcStamp = null;
            }
            $monitorFiles[$testFile] = array('test_stamp' => $testStamp,
                                             'test_class' => $testClass,
                                             'src_file'   => $srcFile,
                                             'src_stamp'  => $srcStamp,
                                             'src_class'  => $srcClass);
        }

        if (count($addFiles) > 0) {
            sleep(2);
        }

        foreach ($delFiles as $testFile) {
            pake_echo_action('test del', $testFile);
            unset($monitorFiles[$testFile]);
        }

        foreach ($monitorFiles as $testFile => $testInfo) {
            $isRunTest = false;
            $testStamp = filemtime($testFile);
            if ($testStamp > $testInfo['test_stamp']) {
                $monitorFiles[$testFile]['test_stamp'] = $testStamp;
                pake_echo_action('modify', $testFile);
                $isRunTest = true;
            } else if ($testInfo['src_file'] !== null and file_exists($testInfo['src_file'])) {
                $srcStamp = filemtime($testInfo['src_file']);
                if ($srcStamp > $testInfo['src_stamp']) {
                    $monitorFiles[$testFile]['src_stamp'] = $srcStamp;
                    pake_echo_action('modify', $srcFile);
                    $isRunTest = true;
                }
            }
            if ($isRunTest) {
                $testTarget = str_replace($testDir, '', $testFile);
                $testTarget = str_replace(DIRECTORY_SEPARATOR, '.', $testTarget);
                $cmd = "symfony s2test $appName $testTarget";
                pake_echo_action('run', $cmd);
                system($cmd);
                pake_echo_action('info', 'monitoring test files. . . . .');
            }
        }
        sleep(2);
        $isFirst = false;
    }
}
