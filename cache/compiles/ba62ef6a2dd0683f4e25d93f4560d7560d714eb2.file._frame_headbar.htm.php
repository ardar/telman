<?php /* Smarty version Smarty-3.0.7, created on 2011-07-26 12:00:39
         compiled from "C:\TMWWW/Views/_frame_headbar.htm" */ ?>
<?php /*%%SmartyHeaderCode:281804e2e3be775ff80-88109050%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ba62ef6a2dd0683f4e25d93f4560d7560d714eb2' => 
    array (
      0 => 'C:\\TMWWW/Views/_frame_headbar.htm',
      1 => 1311651412,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '281804e2e3be775ff80-88109050',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><HEAD><TITLE>Bar</TITLE>
<META http-equiv=Content-Type content="text/html; charset=utf-8" 
MSSmartTagsPreventParsing?&gt;
<META http-equiv=MSThemeCompatible content=Yes><LINK 
href="style.css" type=text/css rel=stylesheet>
<META content="MSHTML 6.00.3790.218" name=GENERATOR>
<SCRIPT LANGUAGE="JavaScript" src="ui/common.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
function killErrors()
{
  return true;
}
window.onerror = killErrors;

var ctroltime;

function UpdateState()
{
	LoadUrlContent("index.php?controller=DeviceMonitorJS&action=querystate"+'&temp='+Math.random(), UpdateCallBack, null);
}

function UpdateCallBack(callback_param, content)
{
	if(content!="OK")
	{
		window.document.getElementById('state_exception').innerHTML = content;
		window.parent.document.getElementById('frmHeadBarTD').style.height='25px';
  		ctroltime=setTimeout("UpdateState()",60000);
	}
	else
	{
		window.document.getElementById('state_exception').innerHTML = '';
		window.parent.document.getElementById('frmHeadBarTD').style.height='0px';
  		ctroltime=setTimeout("UpdateState()",60000);
	}
}

function MyLoad()
{
	return;
  ctroltime=setTimeout("UpdateState()",3000);
}

function Reset()
{
	window.document.getElementById('state_exception').innerHTML = '';
	window.parent.document.getElementById('frmHeadBarTD').style.height='0px';
  	//ctroltime=setTimeout("UpdateState()",30000);
}

</script>
</HEAD>
<BODY leftMargin=0 topMargin=0 onLoad="MyLoad();">
<TABLE height="100%" cellSpacing=0 cellPadding=2 width="100%" bgColor='#CCCCCC'
border=0>
  <TBODY>
  <TR vAlign=center>
    <TD width="33%" style="padding-left:10px"> <span id=state_exception></span>
    <span id="new_msg"></span>
    </TD>
    <TD align=middle width="33%"><B><?php echo $_smarty_tpl->getVariable('project')->value;?>
<?php echo $_smarty_tpl->getVariable('version')->value;?>

    </B></TD>
    <TD align=right width="34%"><A href="<?php echo $_smarty_tpl->getVariable('powerbyurl')->value;?>
" 
      target=_blank><?php echo $_smarty_tpl->getVariable('powerby')->value;?>
</A></TD></TR></TBODY></TABLE>
    </BODY></HTML>
    <!--<iframedddd name="ref_new_msg" src="<?php echo $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['url_helper'][0][0]->_urlhelper(array('type'=>'proc','param1'=>'Msg','param2'=>'ref_new'),$_smarty_tpl);?>
" width="0" height="0"></iframe>
-->