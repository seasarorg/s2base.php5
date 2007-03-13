<?php
class S2Base_ZfRegexValidateFactory implements S2Base_ZfValidateFactory {
    const ID = 'regex';
    private $instance = null;
    private $validateClassName = 'Zend_Validate_Regex';

    public function getId() {
        return self::ID;
    }

    public function getInstance($paramName, Zend_Config $config) {
        $valKey = self::ID;
        if ($config->$valKey === null or $config->$valKey->pattern === null) {
            throw new Exception("pattern not found in Regex validation [param : $paramName]");
        }
        if ($this->instance === null) {
            Zend::loadClass($this->validateClassName);
            $this->instance = new $this->validateClassName($config->$valKey->pattern);
        } else {
            $this->instance->setPattern($config->$valKey->pattern);
        }
        return $this->instance;
    }
}
?>
