<?php
class @@CLASS_NAME@@
    implements S2Base_Action {
    private $@@SERVICE_PROPERTY@@;
    const LIST_LIMIT = 10;
    const PAGE_LIMIT = 5;

    public function execute(S2Base_Request $request,
                            S2Base_View $view){
        $support = new S2Dao_PagerSupport(self::LIST_LIMIT,
                           '@@CONDITION_DTO_NAME@@',
                           '@@CONDITION_DTO_SESSION_KEY@@');
        $conditionDto = $support->getPagerCondition();
        if($request->getParam('s2pager_offset') != null){
            $conditionDto->setOffset((integer)$request->getParam('s2pager_offset'));
        }
        $dtos = $this->@@SERVICE_PROPERTY@@->getByConditionDto($conditionDto);
        $view->assign('dtos',$dtos);

        $helper = new S2Dao_PagerViewHelper($conditionDto,self::PAGE_LIMIT);
        $view->assign('helper', $helper);

        $begin = $helper->getDisplayPageIndexBegin();
        $end   = $helper->getDisplayPageIndexEnd();
        $pageIndex = array();
        for ( $i = $begin; $i <= $end; $i++ ) {
            $pageIndex[] = $i;
        }
        $view->assign('pageIndex',$pageIndex);
    }

    public function set@@SERVICE_INTERFACE@@(@@SERVICE_INTERFACE@@ $service){
        $this->@@SERVICE_PROPERTY@@ = $service;
    }
}
?>
