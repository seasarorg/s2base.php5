<?php
class @@DAO_TEST_CLASS_NAME@@ extends PHPUnit_Framework_TestCase {
    private $appName    = '@@APP_NAME@@';
    private $moduleName = '@@MODULE_NAME@@';
    private $container;
    private $dao;
    private $pdo;

    public function testA() {
        try {
            //$this->pdo->beginTransaction();
        } catch(Exception $e) {
            //$this->pdo->rollBack();
        }
        //$this->pdo->rollBack();
    }

    public function setUp() {
        print PHP_EOL . PHP_EOL . __CLASS__ . '->' . $this->getName() . '()' . PHP_EOL;
        $incFile = sfConfig::get('sf_app_dir') . DIRECTORY_SEPARATOR
                 . 'modules' . DIRECTORY_SEPARATOR . $this->moduleName . DIRECTORY_SEPARATOR
                 . 'actions' . DIRECTORY_SEPARATOR . 'actions.inc.php';
        require_once($incFile);
        $this->container = S2ContainerApplicationContext::create();
        $this->dao = $this->container->getComponent('@@DAO_INTERFACE_NAME@@');
        $this->pdo = $this->container->getComponent("dataSource")->getConnection();
    }

    public function tearDown() {
        $this->pdo = null;
        $this->dao = null;
        $this->container = null;
    }
}
