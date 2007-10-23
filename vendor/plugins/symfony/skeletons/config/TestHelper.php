<?php
S2ContainerApplicationContext::import(S2BASE_PHP5_ROOT . '/lib/dao');
S2ContainerApplicationContext::import(S2BASE_PHP5_ROOT . '/lib/entity');
S2ContainerApplicationContext::import(S2BASE_PHP5_ROOT . '/config/dao.dicon');
S2ContainerApplicationContext::registerAspect('/Dao$/', 'dao.interceptor');