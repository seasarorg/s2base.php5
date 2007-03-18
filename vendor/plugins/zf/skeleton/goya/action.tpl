
    public function @@ACTION_NAME@@() {
        $this->view->assign('dtos', $this->service->getWithLimit(10));
    }
    /** S2BASE_PHP5 ACTION METHOD **/