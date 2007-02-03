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
class RegexpValidator extends S2Base_ZfAbstractValidator {
    const MSG_KEY    = 'msg';
    const PAGE_KEY   = 'page';
    const REGEXP_KEY = 'regexp';

    public function validate(Zend_Controller_Request_Abstract $request,
                             Zend_View_Interface $view){
        $this->initialize($request, $view);
        $this->preValidate();
        $invalidParams = array();
        foreach ($this->rule as $key => $val) {
            if ($key == self::PAGE_KEY or 
                $key == S2Base_ZfValidatorFactory::CLASS_KEY) {
                continue;
            }

            $this->validateRuleKey(self::REGEXP_KEY, $key, $val);
            $this->validateRuleKey(self::MSG_KEY, $key, $val);

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
        return true;
    }

    protected function invalidAction($invalidParams) {
        $this->validateRuleKey(self::PAGE_KEY, 'default', $this->rule);
        $page = trim($this->rule[self::PAGE_KEY]);
        $matches = array();
        if (preg_match('/^exception\s*:(.+)/', $page, $matches)) {
            throw new Exception(trim($matches[1]));
        } else if (preg_match('/^forward\s*:(.+)/', $page, $matches)) {
            $this->request->setActionName(trim($matches[1]));
            $this->request->setDispatched(false);
        } else {
            $this->request->setDispatched(true);
            S2Base_ZfDispatcherSupportPlugin::setExitDispatchLoop();
            $this->view->render($page);
        }
        return false;
    }

    protected function isValid($regexp, $paramVal) {
        return mb_ereg_match($regexp, $paramVal);
    }

    protected function validateRuleKey($key, $section, array $val) {
        if (!isset($val[$key])) {
            throw new Exception("$key key not found in $section section. [{$this->iniFile}]");
        }
    }
}
?>
