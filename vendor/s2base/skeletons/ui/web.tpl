<?php
require_once(dirname(dirname(__FILE__)) . '/config/environment.inc.php');
require_once(S2BASE_PHP5_ROOT . '/app/modules/hoge/hoge.inc.php');
$container = S2ContainerApplicationContext::create();
//$dao = $container->getComponent('CdDao');
?>

<html>
<head>
<title>@@MODULE_NAME@@</title>
</head>
<body>

this is @@MODULE_NAME@@ page.<br>

<pre>
<?php //print_r($dao->findAllList()); ?>
</pre>

</body>
</html>
