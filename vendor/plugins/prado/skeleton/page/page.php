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
		if($param instanceof TCommandEventParameter)
			$sender->Text="Name: {$param->CommandName}, Param: {$param->CommandParameter}";
		else
			$sender->Text="I'm clicked";
			
		$this->Result1->Text = $this->TextBox1->Text;		
	}

}
?>
