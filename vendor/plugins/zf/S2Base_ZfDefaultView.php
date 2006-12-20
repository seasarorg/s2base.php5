<?php
require_once('Zend/View.php');
class S2Base_ZfDefaultView
    extends Zend_View
    implements S2Base_ZfView {

    private $template = null;
    private $request = null;
    private $response = null;
    private $scriptPath = null;
    
    public function __construct(){
        parent::__construct();
        $this->request = Zend_Controller_Front::getInstance()->getRequest();
        $this->response = Zend_Controller_Front::getInstance()->getResponse();
    }
    
    public function setTpl($tpl) {
        $this->template = $tpl;
    }

    public function getTpl() {
        return $this->template;
    }

    public function setScriptPath($tpl) {
        $this->template = $tpl;
    }

    public function renderWithTpl() {
        $this->addScriptPath(S2BASE_PHP5_ROOT
                           . '/app/modules/'
                           . S2Base_ZfDispatcherSupportPlugin::getModuleName($this->request) . '/'
                           . $this->request->getControllerName()
                           . '/view');
        if ($this->template == null) {
            $this->response->setBody($this->render(
                   $this->request->getActionName() . S2BASE_PHP5_ZF_TPL_SUFFIX));
        } else {
            $this->response->setBody($this->render($this->template));
        }
    }
}
?>
