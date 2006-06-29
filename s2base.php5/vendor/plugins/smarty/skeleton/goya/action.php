<?php
class @@CLASS_NAME@@
    implements S2Base_Action {
    private $service;

    public function execute(S2Base_Request $request,
                            S2Base_View $view){
    }

    public function setService(@@SERVICE_INTERFACE@@ $service){
        $this->service = $service;
    }
}
?>
