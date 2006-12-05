
    public function @@ACTION_METHOD_NAME@@() {
        $this->view->assign('func','create');
        $this->view->assign('dto',new @@ENTITY_CLASS_NAME@@());
        $this->view->render('@@ACTION_NAME@@Input.html');
    }
    /** S2BASE_PHP5 ACTION METHOD **/