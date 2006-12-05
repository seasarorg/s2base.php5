<?php
/**
 * available properties.
 *    protected $invocation;
 *    protected $request;
 *    protected $moduleName;
 *    protected $actionName;
 *    protected $action;
 *    protected $view;
 *    protected $rule;
 *    protected $controller;
 */
class @@ACTION_NAME@@ConfirmValidator extends RegexpValidator {

    public function getSuffix(){
        return "regexp";
    }

    protected function invalidAction($invalidParams) {
        $dto = @@CONTROLLER_CLASS_NAME@@::create@@ENTITY_CLASS_NAME@@FromRequest($this->request);
        $this->view->assign('dto', $dto);
        $this->view->assign('func', $this->request->getParam('func'));
        $page = $this->rule[self::PAGE_KEY];
        $this->request->setDispatched(true);
        S2Base_ZfValidateSupportPlugin::setExitDispatchLoop();
        $this->view->render($page);
        $this->rule = null;
    }
}
?>
