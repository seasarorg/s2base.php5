<?php
class AutoTestCommand implements S2Base_GenerateCommand {

    protected $testClasses = array();

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
            $this->testClasses = array();
            $this->findTestClasses($testDir);
            $monitorTmp = array();
            foreach($this->testClasses as $testClass => $testFile) {
                $monitorTmp[$testFile] = filemtime($testFile);
            }
            $addFiles = array_diff_key($monitorTmp, $monitorFiles);
            $delFiles = array_diff_key($monitorFiles, $monitorTmp);
            foreach ($addFiles as $file => $stamp) {
                print "[INFO ] test add : $file" . PHP_EOL;
                $monitorFiles[$file] = $isFirst ? $stamp : $stamp -1;
            }
            foreach ($delFiles as $file => $stamp) {
                print "[INFO ] test del : $file" . PHP_EOL;
                unset($monitorFiles[$file]);
            }
            foreach ($monitorFiles as $testFile => $stamp) {
                $currentStamp = filemtime($testFile);
                if ($currentStamp > $stamp) {
                    $monitorFiles[$testFile] = $currentStamp;
                    $testTarget = str_replace(S2BASE_PHP5_ROOT . DIRECTORY_SEPARATOR, '', $testFile);
                    $testTarget = str_replace(DIRECTORY_SEPARATOR, '.', $testTarget);
                    if (preg_match('/\.php$/', $this->args[0])) {
                        $cmd = "php {$this->args[0]} test $testTarget";
                    } else {
                        $cmd = "phing test -Dtt=$testTarget";
                    }
                    print PHP_EOL . "[INFO ] modified : $testFile" . PHP_EOL;
                    print "[INFO ] execute  : $cmd" . PHP_EOL;
                    system($cmd);
                    print PHP_EOL . PHP_EOL . '[INFO ] monitoring test files. . . . .' . PHP_EOL;
                }
            }
            sleep(2);
            $isFirst = false;
        }
    }

    protected function findTestClasses($root) {
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
                $this->findTestClasses($rootItem);
            }
            else if (is_file($rootItem)) {
                $matches = array();
                if (preg_match('/(.+?Test)\..*php/', $item, $matches)) {
                    $this->testClasses[$matches[1]] = $rootItem;
                }
            }
            else {
                print "[ERROR] invalid item [$rootItem] " . PHP_EOL;
                exit;
            }
        }
    }
}
