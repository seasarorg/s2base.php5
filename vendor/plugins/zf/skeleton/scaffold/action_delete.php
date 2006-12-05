
    public function @@PRE_ACTION_METHOD_NAME@@() {
        $filter = new RegexpValidator($this->getRequest(), $this->getResponse(), $this->view);
        $filter->validate();
    }

    public function @@ACTION_METHOD_NAME@@(){
        $pk = $this->getRequest()->getParam('@@UNIQUE_KEY_NAME@@');
        $dto = $this->service->getById($pk);
        if (count($dto) == 0) {
            throw new Exception("primarty key not found.[$pk]");
        }
        $this->view->assign('dto', $dto);
        $this->view->assign('func', 'delete');
        session_start();
        session_regenerate_id(true);
        $_SESSION['@@DTO_SESSION_KEY@@'] = $dto;
        $this->view->render('@@ACTION_NAME@@Confirm.html');
    }
    /** S2BASE_PHP5 ACTION METHOD **/