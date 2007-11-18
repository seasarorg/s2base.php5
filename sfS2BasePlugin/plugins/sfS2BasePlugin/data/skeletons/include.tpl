<?php
/**
 * S2ContainerApplicationContext set up.
 */
S2ContainerApplicationContext::init();
S2ContainerApplicationContext::import(sfConfig::get('sf_app_module_dir') . '/@@MODULE_NAME@@/dao');
S2ContainerApplicationContext::import(sfConfig::get('sf_app_module_dir') . '/@@MODULE_NAME@@/service');
S2ContainerApplicationContext::import(sfConfig::get('sf_root_dir') . '/config/dao.dicon');
S2ContainerApplicationContext::registerAspect('/Dao$/', 'dao.interceptor');
if(!defined('PDO_DICON')) { define('PDO_DICON', sfConfig::get('sf_root_dir') . '/config/pdo_' . sfConfig::get('sf_environment') . '.dicon'); }
