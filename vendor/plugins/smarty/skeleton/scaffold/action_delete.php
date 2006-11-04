<?php
class @@CLASS_NAME@@
    implements S2Base_Action {
    private $@@SERVICE_PROPERTY@@;

    public function execute(S2Base_Request $request,
                            S2Base_View $view){
        $pk = $request->getParam('@@UNIQUE_KEY_NAME@@');
        $dto = $this->@@SERVICE_PROPERTY@@->getById($pk);
        if (count($dto) == 0) {
            throw new Exception("primarty key not found.[$pk]");
        }
        $view->assign('dto',$dto);
        $view->assign('func', 'delete');
        session_start();
        session_regenerate_id(true);
        $_SESSION['@@DTO_SESSION_KEY@@'] = $dto;

        return '@@ACTION_NAME@@Confirm.tpl';
    }

    public function set@@SERVICE_INTERFACE@@(@@SERVICE_INTERFACE@@ $service){
        $this->@@SERVICE_PROPERTY@@ = $service;
    }
}
?>
