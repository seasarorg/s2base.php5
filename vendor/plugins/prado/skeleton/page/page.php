<?php
class @@CLASS_NAME@@ extends TPage {
    private $logic;

    /*
    public function setLogic(#-Logic_Interface-# $logic){
        $this->logic = $logic;
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
