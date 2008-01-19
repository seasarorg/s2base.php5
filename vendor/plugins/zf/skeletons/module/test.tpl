<?php
require_once S2BASE_PHP5_ROOT ."/app/modules/@@MODULE_NAME@@/controllers/@@CONTROLLER_CLASS_FILE@@";
class @@CLASS_NAME@@ extends S2Base_ZfControllerTestCase {

    public function testIndexAction() {
        try {
            $this->get('index');
            $this->assertResponse(200);
        } catch(Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function setUp() {
        print PHP_EOL . __CLASS__ . '->' . $this->getName() . '()' . PHP_EOL;
        S2Base_ZfInitialize::initFanctionalTest();
        $this->fc = Zend_Controller_Front::getInstance();
        $this->request = new Zend_Controller_Request_Http();
        $this->response = new Zend_Controller_Response_Http();
        $this->controller = new @@CONTROLLER_CLASS@@(
            $this->request, $this->response, $this->fc->getParams()
        );
        $this->moduleName = '@@MODULE_NAME@@';
        $this->controllerName = '@@CONTROLLER_NAME@@';
    }

    public function tearDown() {
    }
}