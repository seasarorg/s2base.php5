<?php
/**
 * available properties.
 *    protected $request;
 *    protected $moduleName;
 *    protected $actionName;
 *    protected $rule;
 *    protected $controllerName;
 *    protected $view;
 */
class RegexpValidator extends S2Base_ZfValidateFilter {
    const REGEXP_KEY = 'regexp';
    const MSG_KEY    = 'msg';
    const PAGE_KEY   = 'page';

    public function getSuffix(){
        return "regexp";
    }

    public function validate(){
        $this->includeRule();
        if ($this->rule === null) {
            return null;
        }

        $this->preValidate();

        $invalidParams = array();
        foreach ($this->rule as $key => $val) {
            if ($key == self::PAGE_KEY) {
                continue;
            }

            $paramVal = $this->request->getParam($key);
            if (!$this->isValid($val[self::REGEXP_KEY], $paramVal)) {
                $invalidParams[$key] = $paramVal;
                $this->view->putError($key, $val[self::MSG_KEY]);
            }
        }

        if (count($invalidParams) == 0) {
            return $this->validAction();
        } else {
            return $this->invalidAction($invalidParams);
        }
    }

    protected function preValidate() {}

    protected function validAction() {
        $this->rule = null;
        return true;
    }

    protected function invalidAction($invalidParams) {
        $this->request->setActionName($this->rule[self::PAGE_KEY]);
        $this->request->setDispatched(false);
        $this->rule = null;
        return false;
    }

    protected function isValid($regexp, $paramVal) {
        return mb_ereg_match($regexp, $paramVal);
    }
}
?>
