<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp">
<title>@@MODULE_NAME@@</title>
</head>
<body>

This is @@PAGE_NAME@@ template. <br>
<!-- Sample (don't use page class method)-->
TTextHighlighter:<br>
<com:TTextHighlighter ShowLineNumbers="true" EnableCopyCode="true">
<?php
	$str = 'one|two|three|four';
	// will output an array
	print_r(explode('|', $str, 2));
?>
</com:TTextHighlighter><br>
TStatements:
<com:TStatements>
	<prop:Statements>
		$uid=$this->UniqueID;
		echo "UniqueID is '$uid'.";
	</prop:Statements>
</com:TStatements><br>
THyperLink:<com:THyperLink NavigateUrl="http://s2base.php5.sandbox.seasar.org/" Text="Welcome to S2Base.PHP5." Target="_blank"/><br>
TDatePicker:<br>
<com:TDatePicker />
<com:TForm>
<!-- Sample 02 (use page class method with Component) -->
TButton:<com:TButton Text="click me" OnClick="buttonClicked" /><br>
</com:TForm>
</body>
</html>
