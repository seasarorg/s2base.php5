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
		if($param instanceof TCommandEventParameter)
			$sender->Text="Name: {$param->CommandName}, Param: {$param->CommandParameter}";
		else
			$sender->Text="I'm clicked";
			
		$this->Result1->Text = $this->TextBox1->Text;	
	}

}
?>
