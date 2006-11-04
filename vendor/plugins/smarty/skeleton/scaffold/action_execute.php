<?php
class @@CLASS_NAME@@
    implements S2Base_Action {
    private $@@SERVICE_PROPERTY@@;

    public function execute(S2Base_Request $request,
                            S2Base_View $view){
        $func = $request->getParam('func');
        session_start();
        session_regenerate_id(true);
        if (!isset($_SESSION['@@DTO_SESSION_KEY@@'])) {
            throw new Exception("session dto not found.[@@DTO_SESSION_KEY@@]");
        }
        $dto = $_SESSION['@@DTO_SESSION_KEY@@'];

        switch ($func) {
            case 'create':
                $this->@@SERVICE_PROPERTY@@->createByDto($dto);
                break;
            case 'update':
                $this->@@SERVICE_PROPERTY@@->updateByDto($dto);
                break;
            case 'delete':
                $this->@@SERVICE_PROPERTY@@->deleteByDto($dto);
                break;
        }

        unset($_SESSION['@@DTO_SESSION_KEY@@']);
        $view->setRendered(true);

        $host  = $_SERVER['HTTP_HOST'];
        $uri   = $_SERVER['PHP_SELF'];
        $extra = "?mod={$request->getModule()}&act=@@ACTION_NAME@@";
        header("Location: http://$host$uri$extra");
        exit;
    }

    public function set@@SERVICE_INTERFACE@@(@@SERVICE_INTERFACE@@ $service){
        $this->@@SERVICE_PROPERTY@@ = $service;
    }
}
?>
