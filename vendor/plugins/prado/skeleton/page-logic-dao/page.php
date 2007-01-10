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
		if($param instanceof TCommandEventParameter)
			$sender->Text="Name: {$param->CommandName}, Param: {$param->CommandParameter}";
		else
			$sender->Text="I'm clicked";

		$this->Result1->Text = $this->TextBox1->Text;
	}

}
?>
