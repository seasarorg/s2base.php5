<?php
class @@CLASS_NAME@@ extends TPage {
    private $service;

    /*
    public function setService(#-Service_Interface-# $service){
        $this->service = $service;
    }
    */

	/**
	 * event Procedure Sample
	 */
	public function buttonClicked($sender,$param){
		$this->gotoPage($this->TextBox1->Text);
	}
	
	protected function gotoPage($pageName)
	{
		$url = $this->getApplication()->getRequest()->constructUrl('page',$pageName);
		$this->getApplication()->getResponse()->redirect($url);
	}

}
?>
