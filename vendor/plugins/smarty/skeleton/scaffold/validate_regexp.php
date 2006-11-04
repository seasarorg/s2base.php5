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
class RegexpValidator extends S2Base_AbstractValidateFilter {
    const REGEXP_KEY = 'regexp';
    const MSG_KEY    = 'msg';
    const PAGE_KEY   = 'page';

    public function getSuffix(){
        return "regexp";
    }

    public function validate(){

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
                $this->controller->putError($key, $val[self::MSG_KEY]);
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
        return null;
    }

    protected function invalidAction($invalidParams) {
        $page = $this->rule[self::PAGE_KEY];
        $this->rule = null;
        return $page;
    }

    protected function isValid($regexp, $paramVal) {
        //return preg_match($regexp, $paramVal);
        return mb_ereg_match($regexp, $paramVal);
    }
}
?>
