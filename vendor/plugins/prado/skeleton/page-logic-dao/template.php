<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp">
<title>@@MODULE_NAME@@</title>
</head>
<body>

This is @@PAGE_NAME@@ template. <br>
<com:TForm>
<!-- Sample 01 (use page class method with Component) -->
TButton:<com:TButton Text="click me" OnClick="buttonClicked" /><br>
TButton:<com:TButton Text="click me" OnCommand="buttonClicked" CommandName="test" CommandParameter="value"/><br>
TLinkButton:<com:TLinkButton Text="click me" OnClick="buttonClicked" /><br>	
TCheckBox:<com:TCheckBox AutoPostBack="true" Text="click me" OnCheckedChanged="buttonClicked"/><br>
TRadioButton:<com:TRadioButton AutoPostBack="true" Text="click me" OnCheckedChanged="buttonClicked"/><br>

<!-- Sample 02 (use page class method with Components) -->
TTextBox/TButton:
<com:TTextBox ID="TextBox1" Text="text" />
<com:TButton Text="Submit" OnClick="submitText" /><br>
THtmlArea/TButton/TLiteral:<br>
<com:THtmlArea ID="HtmlArea1" />
<com:TButton Text="Submit" OnClick="button2Clicked" /><br>
<com:TLiteral Encode="true" ID="Result1" /><br>

</com:TForm>
</body>
</html>
