<?php
class GencoreCommand implements S2Base_GenerateCommand {

    public function getName(){
        return "gencore";
    }

    public function isAvailable(){
        return true;
    }

    public function execute(){
        $coreClasses = $this->getCoreClasses();
        $libPath = S2BASE_PHP5_ROOT . DIRECTORY_SEPARATOR . 'lib';
        $this->validate($libPath . DIRECTORY_SEPARATOR . 'Zend');
        $rite = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($libPath . DIRECTORY_SEPARATOR . 'Zend'));
        foreach ($rite as $ite) {
            if (!preg_match('/\.php$/', $ite->getFileName())) {
                continue;
            }
            $this->modify($ite->getRealPath(), $coreClasses);
        }

        $src = '';
        foreach($coreClasses as $coreClass) {
            $path = $libPath . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $coreClass) . '.php';
            $src .= $this->getSrc($path);
        }
        $src = '<?php' . PHP_EOL . $src;
        file_put_contents($libPath . DIRECTORY_SEPARATOR . 'zf_core.php', $src);
    }

    private function validate($zendPath) {
        if (!is_dir($zendPath)) {
            throw new Exception('Zend directory not found.');
        }
    }

    private function getCoreClasses() {
        $coreClasses = file(S2BASE_PHP5_ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'zf_core.txt');
        $classes = array();
        foreach($coreClasses as $coreClass) {
            if (preg_match('/^#/', trim($coreClass)) or
                preg_match('/^\/\//', trim($coreClass))) {
                continue;
            }
            $classes[] = trim($coreClass);
        }
        return $classes;
    }

    private function getClassName($path, $libPath) {
        $className = str_replace($libPath, '', $path);
        $className = str_replace('.php', '', $className);
        $className = str_replace(DIRECTORY_SEPARATOR, '_', $className);
        $className = str_replace('/', '_', $className);
        return $className;
    }

    private function modify($classPath, array $coreClasses) {
        $contents = file($classPath);
        $isModify = false;
        foreach($contents as &$line) {
            $matches = array();
            if (preg_match('/^require.+\'(.+)\'\)*;$/', trim($line), $matches)) {
                $className = $this->getClassName($matches[1], '');
                if (in_array($className, $coreClasses)) {
                    $line = PHP_EOL;
                    $isModify = true;
                }
            }
        }
        file_put_contents($classPath, implode('', $contents));
        if ($isModify) {
            print '[INFO ] modify : ' . $classPath . PHP_EOL;
        }
    }

    private function getSrc($classPath) {
        $contents = file($classPath);
        $src = '';
        foreach($contents as $line) {
            if (preg_match('/^\/\/[^\*]/', trim($line))) {
                continue;
            }
            $line = preg_replace('/<\?php/s', '', $line);
            $line = preg_replace('/\?>/s', '', $line);
            if (trim($line) === '') {
                continue;
            }
            $src .= $line;
        }
        $src = preg_replace('/\/\*.+?\*\//s', '', $src);
        return $src;
    }
}
