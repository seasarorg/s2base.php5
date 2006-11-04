<?php
class @@CLASS_NAME@@
    implements S2Base_Action {
    private $@@SERVICE_PROPERTY@@;

    public function execute(S2Base_Request $request,
                            S2Base_View $view){
        $view->assign('func','create');
        $view->assign('dto',new @@ENTITY_CLASS_NAME@@());
        return '@@ACTION_NAME@@Input.tpl';
    }

    public function set@@SERVICE_INTERFACE@@(@@SERVICE_INTERFACE@@ $service){
        $this->@@SERVICE_PROPERTY@@ = $service;
    }
}
?>
