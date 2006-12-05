
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
    }
    /** S2BASE_PHP5 ACTION METHOD **/