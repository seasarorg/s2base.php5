<?php
require_once S2BASE_PHP5_ROOT ."/app/modules/@@MODULE_NAME@@/controllers/@@CONTROLLER_CLASS_FILE@@";
class @@CLASS_NAME@@ extends S2Base_ZfControllerTestCase {
    public function __construct($name) {
        parent::__construct($name);
    }

    public function testIndexAction(){
      $this->get('index');
      $this->assertResponse(200);
    }

    public function setUp(){
        S2Base_ZfInitialize::initTest();
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