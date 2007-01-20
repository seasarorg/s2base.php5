<?php
class @@CLASS_NAME@@ extends TPage {
    private $@@LOGIC_PROPERTY@@;

    public function set@@LOGIC_INTERFACE@@(@@LOGIC_INTERFACE@@ $logic){
        $this->@@LOGIC_PROPERTY@@ = $logic;
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
