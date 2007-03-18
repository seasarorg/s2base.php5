
    public function @@ACTION_METHOD_NAME@@() {
        $listLimit = 10;
        $pageLimit = 5;
        $support = new S2Dao_PagerSupport($listLimit,
                           '@@CONDITION_DTO_NAME@@',
                           '@@CONDITION_DTO_SESSION_KEY@@');
        $conditionDto = $support->getPagerCondition();
        if ($this->getRequest()->has('s2pager_offset') and
            !S2Base_ZfValidateSupportPlugin::hasError($this->getRequest())){
            $conditionDto->setOffset($this->getRequest()->getParam('s2pager_offset'));
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
    }
    /** S2BASE_PHP5 ACTION METHOD **/