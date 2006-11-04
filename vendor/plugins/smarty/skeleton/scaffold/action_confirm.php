<?php
class @@CLASS_NAME@@
    implements S2Base_Action {
    private $@@SERVICE_PROPERTY@@;

    public static function createDtoFromRequest($request){
        $dto = new @@ENTITY_CLASS_NAME@@();
        @@CREATE_DTO_METHOD@@
        return $dto;
    }

    public function execute(S2Base_Request $request,
                            S2Base_View $view){
        $view->assign('func', $request->getParam('func'));
        $dto = $this->createDtoFromRequest($request);
        $view->assign('dto', $dto);
        session_start();
        session_regenerate_id(true);
        $_SESSION['@@DTO_SESSION_KEY@@'] = $dto;
    }

    public function set@@SERVICE_INTERFACE@@(@@SERVICE_INTERFACE@@ $service){
        $this->@@SERVICE_PROPERTY@@ = $service;
    }
}
?>
