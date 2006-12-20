<?php
require_once('Zend/View.php');
class S2Base_ZfDefaultView
    extends Zend_View
    implements S2Base_ZfView {

    private $template = null;

    public function __construct(){
        parent::__construct();
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $moduleName = S2Base_ZfDispatcherSupportPlugin::getModuleName($request);
        $this->setScriptPath(S2BASE_PHP5_ROOT
                           . '/app/modules/'
                           . $moduleName . '/'
                           . $this->request->getControllerName()
                           . '/view');
    }
    
    public function setTpl($tpl) {
        $this->template = $tpl;
    }

    public function getTpl() {
        return $this->template;
    }

    public function renderWithTpl() {
        if ($this->template == null) {
            $this->response->setBody($this->render(
                   $this->request->getActionName() . S2BASE_PHP5_ZF_TPL_SUFFIX));
        } else {
            $this->response->setBody($this->render($this->template));
        }
    }
}
?>
