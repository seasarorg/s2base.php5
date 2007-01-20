<?php
class @@CLASS_NAME@@ extends TPage {
    private $@@DAO_PROPERTY@@;

    public function set@@DAO_INTERFACE@@(@@DAO_INTERFACE@@ $dao){
        $this->@@DAO_PROPERTY@@ = $dao;
    }

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
