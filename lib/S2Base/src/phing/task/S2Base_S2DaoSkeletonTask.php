<?php
class S2Base_S2DaoSkeletonTask extends S2DaoSkeletonTask {
    protected function setupTask(){
        $container = S2ContainerFactory::create(PDO_DICON);
        $cd = $container->getComponentDef('dataSource');
        $this->dsn = $cd->getPropertyDef('dsn')->getValue();
        if ($cd->hasPropertyDef('user')) {
            $this->user = $cd->getPropertyDef('user')->getValue();
        }
        if ($cd->hasPropertyDef('password')) {
            $this->pass = $cd->getPropertyDef('password')->getValue();
        }
        $this->skeldir = S2BASE_PHP5_ROOT . '/vendor/s2base/skeletons/gendao';
    }
}
?>