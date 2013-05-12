<?php /* Smarty version Smarty-3.0.7, created on 2011-07-25 11:21:40
         compiled from "C:\TMWWW/Views/_frame.htm" */ ?>
<?php /*%%SmartyHeaderCode:14254e2ce144e39a54-18206542%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '1aa45035b2d82c4efbd9448e8af7dfaec9c4c652' => 
    array (
      0 => 'C:\\TMWWW/Views/_frame.htm',
      1 => 1311145861,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '14254e2ce144e39a54-18206542',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<LINK href="style.css" type=text/css rel=stylesheet>
<title><?php echo $_smarty_tpl->getVariable('sitetitle')->value;?>
</title></HEAD>
<SCRIPT language=javascript>
var x=0;
function switchSysBar()
{
	if (x==0)
	{
	switchPoint.innerHTML="<img src=images/b.gif>";
	document.all("frmMenu").style.display="none";
	x=1;
	}
	else
	{
	switchPoint.innerHTML="<img src=images/a.gif>";
	document.all("frmMenu").style.display="";
	x=0;
	}
}
</SCRIPT>
</head>
<body scroll="no" style="MARGIN: 0px">
<table border=0 cellspacing=0 cellpadding=0 width=100% height=100% id=tblTotal Name = tblTotal align=center>
<tr>
<td colspan="3" id="frmHead" name="frmHead" nowrap valign="center" align="middle"  height='50'>
<iframe  scrolling="no" id="left" name="left"  style="HEIGHT:100%; VISIBILITY: inherit; WIDTH: 100%; Z-INDEX: 1" 
	frameborder="0" 
	src="?controller=Frame&action=head"></iframe>
</td>
</tr>
<tr height="*">
<td id="frmMenu" name="frmMenu" nowrap valign="center" align="middle"  height='100%'>
<iframe  scrolling="auto" id="left" name="left"  style="HEIGHT:100%; VISIBILITY: inherit; WIDTH: 165px; Z-INDEX: 1" 
	frameborder="0" 
	src="?controller=Frame&action=menu"></iframe>
</td>
<TD id=cmenu BACKGROUND="images/bg-10.gif" width="10">
<SPAN id=Label1><SPAN id=switchPoint title="view / hide" style="CURSOR: hand" onClick="switchSysBar();">
<IMG src="images/a.gif"></SPAN></span>
</TD>
<td width='100%'>
<table  border=0 cellspacing=0 cellpadding=0 width=100% height=100% align="center">
<tr>
<td id="frmHeadBarTD" width='100%' height="25" style="height:0px">
<iframe id="frmHeadBar" name="frmHeadBar" scrolling="no" style="height: 100%; visibility: inherit; width: 100%;z-index: 1" 
	width="100%" height="100%" frameborder=0 marginwidth="0" marginheight="0" 
	src="?controller=Frame&action=headbar"></iframe>
</td>
</tr>
<tr>
<td width='100%' nowrap>
<iframe id="frame_main" name="frame_main" style="height: 100%; visibility: inherit; width: 100%;z-index: 1" 
	frameborder=0 marginwidth="0" marginheight="0" 
	src="<?php echo $_smarty_tpl->getVariable('mainurl')->value;?>
"></iframe>
</td>
</tr>
</table>
</td></tr></table>
</body>
</HTML>