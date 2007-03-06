
    public static function create@@ENTITY_CLASS_NAME@@FromRequest($request){
        $dto = new @@ENTITY_CLASS_NAME@@();
        @@CREATE_DTO_METHOD@@
        return $dto;
    }

    public function @@ACTION_METHOD_NAME@@(){
        $this->_view->assign('func', $this->getRequest()->getParam('func'));
        $dto = $this->create@@ENTITY_CLASS_NAME@@FromRequest($this->getRequest());
        $this->_view->assign('dto', $dto);
        $sn = new Zend_Session_Namespace('action_@@ACTION_NAME@@');
        $sn->@@DTO_SESSION_KEY@@ = $dto;
    }
    /** S2BASE_PHP5 ACTION METHOD **/