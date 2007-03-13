<?php
class S2Base_ZfDispatcherSupportPlugin extends Zend_Controller_Plugin_Abstract
{
    const PARAM_MAX_LEN = 50;
    const VIEW_REGISTRY_KEY = 's2base_view';
    public static $VIEW_CLASS = 'S2Base_ZfSmartyView';

    public static function getModuleName(Zend_Controller_Request_Abstract $request) {
        $moduleName = $request->getModuleName();
        if ($moduleName === null) {
            return S2BASE_PHP5_ZF_DEFAULT_MODULE;
        }
        return $moduleName;
    }

    public function routeStartup(Zend_Controller_Request_Abstract $request) {
        $moduleDir = S2BASE_PHP5_ROOT . '/app/modules/';
        $modules = scandir($moduleDir);
        foreach ($modules as $module) {
            if (preg_match('/^\./', $module) or
                !is_dir($moduleDir . $module)) {
                continue;
            }
            Zend_Controller_Front::getInstance()->
                               addControllerDirectory($moduleDir . $module, $module);
        }
        
        if (!in_array(S2BASE_PHP5_ZF_DEFAULT_MODULE, $modules)) {
            Zend_Controller_Front::getInstance()->
                               addControllerDirectory($moduleDir, S2BASE_PHP5_ZF_DEFAULT_MODULE);
        }
    }

    public function routeShutdown(Zend_Controller_Request_Abstract $request) {
        if (!$this->validate(self::getModuleName($request))){
            throw new Exception("invalid module.");
        }
        if (!$this->validate($request->getControllerName())){
            throw new Exception("invalid controller.");
        }
        if (!$this->validate($request->getActionName())){
            throw new Exception("invalid action.");
        }
    }

    private function validate($param) {
        return preg_match('/^\w{1,' . self::PARAM_MAX_LEN .'}$/', $param);
    }

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
        if (self::$VIEW_CLASS != null) {
            Zend::register(self::VIEW_REGISTRY_KEY, new self::$VIEW_CLASS);
        }
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request) {
    }

    public function dispatchLoopShutdown() {
        if (Zend::isRegistered(self::VIEW_REGISTRY_KEY)) {
            $view = Zend::registry(self::VIEW_REGISTRY_KEY);
            if ($view instanceof S2Base_ZfView) {
                $view->renderWithTpl();
            }
        }
    }
}
?>
