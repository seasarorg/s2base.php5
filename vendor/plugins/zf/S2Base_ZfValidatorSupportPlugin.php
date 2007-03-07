<?php
class S2Base_ZfValidatorSupportPlugin extends Zend_Controller_Plugin_Abstract
{
    const VALIDATE_DIR = 'validate';
    const DEFAULT_KEY = 'default';
    const HAS_KEY = 'has';
    const REGEX_KEY = 'regex';
    const ERRORS_KEY = 's2base_validate_errors';
    
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
        $moduleName = S2Base_ZfDispatcherSupportPlugin::getModuleName($request);
        $controllerName = $request->getControllerName();
        $actionName = $request->getActionName();

        $validateIni = S2BASE_PHP5_ROOT
                     . "/app/modules/$moduleName/$controllerName/"
                     . self::VALIDATE_DIR
                     . "/$actionName.ini";
        if (!is_file($validateIni)) {
            return;
        }
        $validateConfig = new Zend_Config_Ini($validateIni, null);
        $forceBreak = false;
        $errors = array();
        while($validateConfig->valid()) {
            $paramName = strtolower($validateConfig->key());
            if ($paramName == self::DEFAULT_KEY) {
                $validateConfig->next();
                continue;
            }
            $paramConfig = $validateConfig->current();

            $vals = preg_split('/,/', $paramConfig->validate, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($vals as $valKey) {
                $valKey = strtolower(trim($valKey));
                $validator = $this->getValidatorInstance($valKey, $paramConfig, $paramName);
                $paramValue = $request->getParam($paramName);
                if (!$validator->isValid($paramValue)){
                    if($paramConfig->exception != null) {
                        throw new Exception($paramConfig->exception);
                    }
                    if($paramConfig->module != null) {
                        $request->setModuleName($paramConfig->module);
                    }
                    if($paramConfig->controller != null) {
                        $request->setControllerName($paramConfig->controller);
                    }
                    if($paramConfig->action != null) {
                        $request->setActionName($paramConfig->action);
                    }
                    $msg = $paramConfig->$valKey->msg === null ? implode('. ', $validator->getMessages()) : $paramConfig->$valKey->msg;
                    $errors[$paramName] = array('value' => $paramValue, 'msg' => $msg);
                    if (strtolower($paramConfig->break) == 'true') {
                        $forceBreak = true;
                        break;
                    }
                }
            }
            if ($forceBreak) {
                break;
            }
            $validateConfig->next();
        }

        if (count($errors) > 0) {
            $request->setParam(self::ERRORS_KEY, $errors);
        }
    }

    private function getValidatorInstance($valKey, $paramConfig, $paramName) {
        $valClassName = 'Zend_Validate_' . ucfirst($valKey);
        Zend::loadClass($valClassName);
        
        switch($valKey) {
            case self::REGEX_KEY:
                if ($paramConfig->$valKey === null or $paramConfig->$valKey->pattern === null) {
                    throw new Exception("pattern not found in Regex validation [param : $paramName]");
                }
                return new $valClassName($paramConfig->$valKey->pattern);
                break;
            default:
                throw new Exception("unsupported validation [$valKey]");
        }
    }
}
?>
