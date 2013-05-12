<?php /* Smarty version Smarty-3.0.7, created on 2011-07-23 16:46:36
         compiled from "E:\www\fw/Views/schedule_frame.htm" */ ?>
<?php /*%%SmartyHeaderCode:81274e2a8a6cdd3c63-88362490%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '915af12e2189b3eb803b4262a4cbe4e237cbcaa3' => 
    array (
      0 => 'E:\\www\\fw/Views/schedule_frame.htm',
      1 => 1310737980,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '81274e2a8a6cdd3c63-88362490',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<LINK href="style.css" type=text/css rel=stylesheet>
<SCRIPT language=javascript src="ui/schedule_calendar.js"/>
</SCRIPT>
<title>tree_frame</title>
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
function returnUp(ret_val)
{
	window.returnValue = ret_val;
	window.close();
}
</SCRIPT>
</head>
<body scroll="no" style="MARGIN: 0px">
<table border=0 cellspacing=0 cellpadding=0 width=100% height=100% id=tblTotal Name = tblTotal align=center>
<tr>
<td id="td_left" name="td_left" nowrap valign="center" align="middle"  width="20%" height='100%'>
<table border=0 cellspacing=0 cellpadding=0 width=100% height=100% align=center>
<tr><td height="30" class=tdbg>
选择逻辑分组
</td></tr>
<tr><td height="40%">
<iframe  scrolling="auto" id="main_left_top" name="main_left_top"  style="HEIGHT:100%; VISIBILITY: inherit; WIDTH: 100%; Z-INDEX: 1" 
	frameborder="0" 
	src="<?php echo $_smarty_tpl->getVariable('left_url_top')->value;?>
"></iframe>
</td></tr>
<tr><td height="30" class=tdbg align="right" style="padding:3px;">

<input type="hidden" id="selmediaid" value=''/>
<?php if ($_smarty_tpl->getVariable('canedit')->value){?>
<a class="button" href="#" onClick="if(selmediaid.value!='')AddToSchedule(selmediaid.value);"><img src="images/additem.png" border=0 width=15> 加入到节目时间表</a>
<?php }?>
</td></tr>
<tr><td height="60%">
<?php if ($_smarty_tpl->getVariable('canedit')->value){?>
<iframe  scrolling="auto" id="main_left_bottom" name="main_left_bottom"  style="HEIGHT:100%; VISIBILITY: inherit; WIDTH: 100%; Z-INDEX: 1" 
	frameborder="0" 
	src="<?php echo $_smarty_tpl->getVariable('left_url_bottom')->value;?>
"></iframe>
<?php }?>
</td></tr>
</table>
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
</td>
</tr>
</table>
</td></tr></table>
<iframe  scrolling="auto" id="main_hidden" name="main_hidden" style="display:none"></iframe>
</body>
</HTML>