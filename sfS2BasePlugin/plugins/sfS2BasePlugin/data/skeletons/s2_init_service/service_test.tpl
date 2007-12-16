<?php
class @@SERVICE_TEST_CLASS_NAME@@ extends PHPUnit_Framework_TestCase {
    private $appName    = '@@APP_NAME@@';
    private $moduleName = '@@MODULE_NAME@@';
    private $container;
    private $service;

    public function testA() {
    }

    public function setUp(){
        print PHP_EOL . PHP_EOL . __CLASS__ . '->' . $this->getName() . '()' . PHP_EOL;
        $incFile = sfConfig::get('sf_app_dir') . DIRECTORY_SEPARATOR
                 . 'modules' . DIRECTORY_SEPARATOR . $this->moduleName . DIRECTORY_SEPARATOR
                 . 'actions' . DIRECTORY_SEPARATOR . 'actions.inc.php';
        require_once($incFile);
        $this->container = S2ContainerApplicationContext::create();
        $this->service = $this->container->getComponent('@@SERVICE_CLASS_NAME@@');
    }

    public function tearDown() {
        $this->container = null;
        $this->service = null;
    }
}
