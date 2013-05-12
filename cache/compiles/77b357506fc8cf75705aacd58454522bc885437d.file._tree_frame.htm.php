<?php /* Smarty version Smarty-3.0.7, created on 2011-09-02 17:37:07
         compiled from "/www/ardar/telman/Views/_tree_frame.htm" */ ?>
<?php /*%%SmartyHeaderCode:4767510594e60a3c393c007-83025436%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '77b357506fc8cf75705aacd58454522bc885437d' => 
    array (
      0 => '/www/ardar/telman/Views/_tree_frame.htm',
      1 => 1314886819,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '4767510594e60a3c393c007-83025436',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<LINK href="style.css" type=text/css rel=stylesheet>
<title>tree_frame</title></HEAD>
<SCRIPT language=javascript>
var x=0;
function switchSysBar()
{
	if (x==0)
	{
	switchPoint.innerHTML="<img src=images/b.gif>";
	document.all("main_left").style.display="none";
	x=1;
	}
	else
	{
	switchPoint.innerHTML="<img src=images/a.gif>";
	document.all("main_left").style.display="";
	x=0;
	}
}
</SCRIPT>
</head>
<body scroll="no" style="MARGIN: 0px">
<table border=0 cellspacing=0 cellpadding=0 width=100% height=100% id=tblTotal Name = tblTotal align=center>
<tr>
<td id="td_left" name="td_left" nowrap valign="center" align="middle"  width="<?php if ($_smarty_tpl->getVariable('left_width')->value>0){?><?php echo $_smarty_tpl->getVariable('left_width')->value;?>
<?php }else{ ?>20%<?php }?>" height='100%'>
<iframe  scrolling="auto" id="main_left" name="main_left"  style="HEIGHT:100%; VISIBILITY: inherit; WIDTH: 100%; Z-INDEX: 1" 
	frameborder="0" 
	src="<?php echo $_smarty_tpl->getVariable('left_url')->value;?>
"></iframe>
</td>
<!--TD id=cmenu BACKGROUND="images/bg-10.gif" width="10">
<SPAN id=Label1><SPAN id=switchPoint title="view / hide" style="CURSOR: hand" onclick="switchSysBar();">
<IMG src="images/a.gif"></SPAN></span>
</TD-->
<td width='100%'>
<table  border=0 cellspacing=0 cellpadding=0 width=100% height=100% align="center">
<tr>
<td width='100%' nowrap>
<iframe id="main_right" name="main_right" style="height: 100%; visibility: inherit; width: 100%;z-index: 1" 
	frameborder=0 marginwidth="0" marginheight="0" 
	src="<?php echo $_smarty_tpl->getVariable('right_url')->value;?>
"></iframe>
    
    <iframe id="main_hidden" name="main_hidden" style="height:0%; visibility:hidden; width:0%;z-index: 0" 
	frameborder=0 marginwidth="0" marginheight="0" 
	src="<?php echo $_smarty_tpl->getVariable('right_url')->value;?>
"></iframe>
</td>
</tr>
</table>
</td></tr></table>
</body>
</HTML>