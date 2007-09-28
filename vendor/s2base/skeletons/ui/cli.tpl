<?php
require_once(dirname(dirname(__FILE__)) . '/config/environment.inc.php');
require_once(S2BASE_PHP5_ROOT . '/app/modules/@@MODULE_NAME@@/@@MODULE_NAME@@.inc.php');
$container = S2ContainerApplicationContext::create();

/**
 * sample
 *
 * $dao = $container->getComponent('CdDao');
 * print_r($dao->findAllList());
 */

