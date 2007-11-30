<?php
S2ContainerApplicationContext::init();
S2ContainerApplicationContext::import(dirname(__FILE__) . '/dao');
S2ContainerClassLoader::import(dirname(__FILE__) . '/entity');
S2ContainerApplicationContext::import(dirname(__FILE__) . '/interceptor');
S2ContainerApplicationContext::import(dirname(__FILE__) . '/service');
S2ContainerApplicationContext::import(S2BASE_PHP5_ROOT . '/app/commons/dicon/dao.dicon');
S2ContainerApplicationContext::registerAspect('/Dao$/', 'dao.interceptor');

