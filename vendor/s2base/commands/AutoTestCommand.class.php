<?php
class AutoTestCommand implements S2Base_GenerateCommand {

    public function __construct() {
        $this->args = $_SERVER['argv'];
    }

    /**
     * @see S2Base_GenerateCommand::getName()
     */    
    public function getName(){
        return 'auto-test';
    }

    /**
     * @see S2Base_GenerateCommand::isAvailable()
     */
    public function isAvailable(){
        return true;
    }

    /**
     * @see S2Base_GenerateCommand::execute()
     */
    public function execute(){
        print PHP_EOL . '[INFO ] start monitoring test files. . . . .' . PHP_EOL;
        $testClasses = array();
        $monitorFiles = array();
        $isFirst = true;
        $testDir = S2BASE_PHP5_ROOT . DIRECTORY_SEPARATOR . 'test';
        $args = $_SERVER['argv'];
        while (true) {
            clearstatcache();
            $testClasses = array();
            $testClasses = $this->findTestClasses($testDir, $testClasses);
            $addFiles = array_diff(array_values($testClasses), array_keys($monitorFiles));
            $delFiles = array_diff(array_keys($monitorFiles), array_values($testClasses));

            foreach ($addFiles as $testFile) {
                print "[INFO ] test add : $testFile" . PHP_EOL;
                $stamp = filemtime($testFile);
                $testStamp = $isFirst ? $stamp : $stamp -1;
                $testClass = array_search($testFile, $testClasses, true);

                $srcClass = preg_replace('/Test$/', '', $testClass);
                $srcFile = $testFile;
                $srcFile = str_replace($testClass . S2BASE_PHP5_CLASS_SUFFIX,
                                       $srcClass  . S2BASE_PHP5_CLASS_SUFFIX, $srcFile);
                $srcFile = str_replace(S2BASE_PHP5_ROOT . DIRECTORY_SEPARATOR . 'test',
                                       S2BASE_PHP5_ROOT . DIRECTORY_SEPARATOR . 'app', $srcFile);
                if (file_exists($srcFile)) {
                    $srcStamp = filemtime($srcFile);
                    $srcStamp = $isFirst ? $srcStamp : $srcStamp -1;
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

            foreach ($delFiles as $testFile) {
                print "[INFO ] test del : $testFile" . PHP_EOL;
                unset($monitorFiles[$testFile]);
            }

            foreach ($monitorFiles as $testFile => $testInfo) {
                $testStamp = filemtime($testFile);
                if ($testStamp > $testInfo['test_stamp']) {
                    $monitorFiles[$testFile]['test_stamp'] = $testStamp;
                    print PHP_EOL . "[INFO ] modified : $testFile" . PHP_EOL;
                    $this->executeTest($testFile);
                } else if ($testInfo['src_file'] !== null) {
                    $srcStamp = filemtime($testInfo['src_file']);
                    if ($srcStamp > $testInfo['src_stamp']) {
                        $monitorFiles[$testFile]['src_stamp'] = $srcStamp;
                        print PHP_EOL . "[INFO ] modified : $srcFile" . PHP_EOL;
                        $this->executeTest($testFile);
                    }
                }
            }
            sleep(2);
            $isFirst = false;
        }
    }

    protected function executeTest($testFile) {
        $testTarget = str_replace(S2BASE_PHP5_ROOT . DIRECTORY_SEPARATOR, '', $testFile);
        $testTarget = str_replace(DIRECTORY_SEPARATOR, '.', $testTarget);
        if (preg_match('/\.php$/', $this->args[0])) {
            $cmd = "php {$this->args[0]} test $testTarget";
        } else {
            $cmd = "phing test -Dtt=$testTarget";
        }
        print "[INFO ] execute  : $cmd" . PHP_EOL;
        system($cmd);
        print PHP_EOL . PHP_EOL . '[INFO ] monitoring test files. . . . .' . PHP_EOL;
    }

    protected function findTestClasses($root, &$spool) {
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
                $spool = array_merge($spool, $this->findTestClasses($rootItem, $spool));
            }
            else if (is_file($rootItem)) {
                $matches = array();
                if (preg_match('/(.+?Test)\..*php$/', $item, $matches)) {
                    $spool[$matches[1]] = $rootItem;
                }
            }
            else {
                print "[ERROR] invalid item [$rootItem] " . PHP_EOL;
                exit;
            }
        }
        return $spool;
    }
}

