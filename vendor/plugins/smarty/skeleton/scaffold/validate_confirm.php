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
        $dto = @@CONFIRM_ACTION_CLASS_NAME@@::createDtoFromRequest($this->request);
        $this->view->assign('dto', $dto);
        $this->view->assign('func', $this->request->getParam('func'));
        return parent::invalidAction($invalidParams);
    }
}
?>
