<?php
class @@CLASS_NAME@@
    implements S2Base_Action {
    private $@@DAO_PROPERTY@@;

    public function execute(S2Base_Request $request,
                            S2Base_View $view){
    }

    public function set@@DAO_INTERFACE@@(@@DAO_INTERFACE@@ $dao){
        $this->@@DAO_PROPERTY@@ = $dao;
    }
}
?>
