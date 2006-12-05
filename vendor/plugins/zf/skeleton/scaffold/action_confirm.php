
    public function @@PRE_ACTION_METHOD_NAME@@() {
        $filter = new @@VALIDATOR_CLASS_NAME@@($this->getRequest(), $this->getResponse(), $this->view);
        $filter->validate();
    }

    public static function create@@ENTITY_CLASS_NAME@@FromRequest($request){
        $dto = new @@ENTITY_CLASS_NAME@@();
        @@CREATE_DTO_METHOD@@
        return $dto;
    }

    public function @@ACTION_METHOD_NAME@@(){
        $this->view->assign('func', $this->getRequest()->getParam('func'));
        $dto = $this->create@@ENTITY_CLASS_NAME@@FromRequest($this->getRequest());
        $this->view->assign('dto', $dto);
        session_start();
        session_regenerate_id(true);
        $_SESSION['@@DTO_SESSION_KEY@@'] = $dto;
        $this->view->render('@@ACTION_NAME@@Confirm.html');
    }
    /** S2BASE_PHP5 ACTION METHOD **/