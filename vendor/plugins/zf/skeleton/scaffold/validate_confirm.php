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

    protected function invalidAction($invalidParams) {
        $dto = @@CONTROLLER_CLASS_NAME@@::create@@ENTITY_CLASS_NAME@@FromRequest($this->request);
        $this->view->assign('dto', $dto);
        $this->view->assign('func', $this->request->getParam('func'));
        parent::invalidAction($invalidParams);
    }
}
?>
