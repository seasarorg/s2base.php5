<?php
class S2Base_ZfRegexValidateFactory implements S2Base_ZfValidateFactory {

    const ID = 'regex';
    private $instance = null;
    private $validateClassName = 'Zend_Validate_Regex';

    public function getId() {
        return self::ID;
    }

    public function getInstance($paramName, Zend_Config $config) {
        if ($config->regex === null or $config->regex->pattern === null) {
            throw new Exception("pattern not found in Regex validation [param : $paramName]");
        }
        if ($this->instance === null) {
            Zend::loadClass($this->validateClassName);
            $this->instance = new $this->validateClassName($config->regex->pattern);
        } else {
            $this->instance->setPattern($config->regex->pattern);
        }
        return $this->instance;
    }
}
?>
