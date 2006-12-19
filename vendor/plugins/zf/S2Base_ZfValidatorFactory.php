<?php
class S2Base_ZfValidatorFactory {
    const VALIDATE_DIR = '/validate/';
    const CLASS_KEY = 'class';

    private function __construct(){}

    public static function create(Zend_Controller_Request_Abstract $request) {
        $validators = array();
        $validateDir = S2BASE_PHP5_ROOT . '/app/modules/'
                     . $request->getControllerName()
                     . self::VALIDATE_DIR;
        if (!is_dir($validateDir)) {
            return $validators;
        }
        $filePattern = $request->getActionName() . '.*.ini';
        $iniFiles = glob($validateDir . $filePattern);
        foreach ($iniFiles as $iniFile) {
            if (!is_readable($iniFile)) {
                continue;
            }
            $rule = parse_ini_file($iniFile, true);
            if (!isset($rule[self::CLASS_KEY])) {
                throw new Exception("class key not found. [$iniFile]");
            }
            $validatorClassName = $rule[self::CLASS_KEY];
            $validator = new $validatorClassName();
            if ($validator instanceof S2Base_ZfAbstractValidator) {
                $validators[] = $validator;
                $validator->setIniFile($iniFile);
                $validator->setRule($rule);
            } else {
                throw new Exception("invalid class found. [$validatorClassName : $iniFile]");
            }
        }
        return $validators;
    }
}
?>
