
    public function @@ACTION_METHOD_NAME@@(){

        $pk = $this->getRequest()->getParam('@@UNIQUE_KEY_NAME@@');
        $dto = $this->service->getById($pk);
        if (count($dto) == 0) {
            throw new Exception("primarty key not found.[$pk]");
        }
        $this->_view->assign('dto',$dto);
        $this->_view->assign('func', 'update');
        $this->_view->setTpl('@@ACTION_NAME@@-input.html');
    }
    /** S2BASE_PHP5 ACTION METHOD **/