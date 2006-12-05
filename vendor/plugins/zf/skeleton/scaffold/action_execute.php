
    public function @@ACTION_METHOD_NAME@@(){
        $func = $this->getRequest()->getParam('func');
        session_start();
        session_regenerate_id(true);
        if (!isset($_SESSION['@@DTO_SESSION_KEY@@'])) {
            throw new Exception("session dto not found.[@@DTO_SESSION_KEY@@]");
        }
        $dto = $_SESSION['@@DTO_SESSION_KEY@@'];

        switch ($func) {
            case 'create':
                $this->service->createByDto($dto);
                break;
            case 'update':
                $this->service->updateByDto($dto);
                break;
            case 'delete':
                $this->service->deleteByDto($dto);
                break;
        }
        unset($_SESSION['@@DTO_SESSION_KEY@@']);
        $extra = "/{$this->getRequest()->getControllerName()}/@@ACTION_NAME@@";
        $this->_redirect($extra);
    }
    /** S2BASE_PHP5 ACTION METHOD **/