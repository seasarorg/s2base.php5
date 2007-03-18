
    public function @@ACTION_METHOD_NAME@@() {
        $pk = $this->getRequest()->getParam('@@UNIQUE_KEY_NAME@@');
        $dto = $this->service->getById($pk);
        if (count($dto) == 0) {
            throw new Exception("primarty key not found.[$pk]");
        }
        $this->view->assign('dto', $dto);
        $this->view->assign('func', 'delete');

        $sn = new Zend_Session_Namespace('action_@@ACTION_NAME@@');
        $sn->@@DTO_SESSION_KEY@@ = $dto;
        $this->view->setTpl('@@ACTION_NAME@@-confirm.html');
    }
    /** S2BASE_PHP5 ACTION METHOD **/