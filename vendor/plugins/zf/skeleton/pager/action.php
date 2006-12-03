
    public function @@ACTION_NAME@@()
    {
        $listLimit = 10;
        $pageLimit = 5;
        $request = $this->getRequest();
        $support = new S2Dao_PagerSupport($listLimit,
                           '@@CONDITION_DTO_NAME@@',
                           '@@CONDITION_DTO_SESSION_KEY@@');
        $conditionDto = $support->getPagerCondition();
        if($request->getParam('s2pager_offset') != null){
            $conditionDto->setOffset((integer)$request->getParam('s2pager_offset'));
        }
        $dtos = $this->service->getByConditionDto($conditionDto);
        $this->view->assign('dtos',$dtos);

        $helper = new S2Dao_PagerViewHelper($conditionDto, $pageLimit);
        $this->view->assign('helper', $helper);

        $begin = $helper->getDisplayPageIndexBegin();
        $end   = $helper->getDisplayPageIndexEnd();
        $pageIndex = array();
        for ( $i = $begin; $i <= $end; $i++ ) {
            $pageIndex[] = $i;
        }
        $this->view->assign('pageIndex',$pageIndex);

        $this->getResponse()->setBody($this->view->render('@@TEMPLATE_NAME@@'));
    }
    /** S2BASE_PHP5 ACTION METHOD **/

