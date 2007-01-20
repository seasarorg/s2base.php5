<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp">
<title>@@MODULE_NAME@@</title>
</head>
<body>

This is @@PAGE_NAME@@ template. <br>
<com:TForm>
	<!-- Sample -->
	Please input page name and click button. <br>
	<com:TTextBox ID="TextBox1" Text="text" />
	<com:TButton Text="click me" OnClick="buttonClicked" /><br>
	<com:TLiteral Encode="true" ID="Result1" /><br>
</com:TForm>
</body>
</html>
