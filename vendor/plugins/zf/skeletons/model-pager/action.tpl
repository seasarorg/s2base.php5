
    public function @@ACTION_METHOD_NAME@@() {
        $listLimit = 10;
        $pageLimit = 5;
        $offset = 0;
        if (!S2Base_ZfValidateSupportPlugin::hasError($this->_request, 's2pager_offset') and
            $this->_request->has('s2pager_offset')) {
            $offset = $this->_request->getParam('s2pager_offset');
        }

        $keyword = null;
        if (!S2Base_ZfValidateSupportPlugin::hasError($this->_request, 's2base_keyword') and
            $this->_request->has('s2base_keyword') ) {
            $keyword = $this->_request->getParam('s2base_keyword');
            $offset = 0;
        }

        Zend_Session::start();
        $support = new S2Dao_PagerSupport($listLimit, '@@CONDITION_DTO_NAME@@', '@@CONDITION_DTO_SESSION_KEY@@');
        $conditionDto = $support->getPagerCondition();
        $conditionDto->setOffset($offset);
        if ($this->_request->has('s2base_keyword')) {
            $conditionDto->setKeyword($keyword);
        }
        $dtos = $this->@@MODEL_PROPERTY@@->getByConditionDto($conditionDto);
        $this->view->assign('dtos',$dtos);

        $helper = new S2Dao_PagerViewHelper($conditionDto, $pageLimit);
        $this->view->assign('helper', $helper);
        $begin = $helper->getDisplayPageIndexBegin();
        $end   = $helper->getDisplayPageIndexEnd();
        $pageIndex = array();
        for ( $i = $begin; $i <= $end; $i++ ) {
            $pageIndex[] = $i;
        }
        $this->view->assign('pageIndex', $pageIndex);
        $this->view->assign('keyword', $conditionDto->getKeyword() == null ? 
                                      'none' : $conditionDto->getKeyword());
    }
    /** S2BASE_PHP5 ACTION METHOD **/

    private $@@MODEL_PROPERTY@@;
    public function set@@MODEL_CLASS@@(@@MODEL_CLASS@@ $service) {
        $this->@@MODEL_PROPERTY@@ = $service;
    }