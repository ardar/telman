<?php /* Smarty version Smarty-3.0.7, created on 2011-07-26 11:22:10
         compiled from "C:\TMWWW/Views/locoroco/login.htm" */ ?>
<?php /*%%SmartyHeaderCode:159824e2e32e29ff3b8-32203438%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '6f7042d9aa9fa68730edde16613d83b27533175b' => 
    array (
      0 => 'C:\\TMWWW/Views/locoroco/login.htm',
      1 => 1311649922,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '159824e2e32e29ff3b8-32203438',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $_smarty_tpl->getVariable('pagetitle')->value;?>
</title>
<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body style="background-color: #333; margin:0px; padding:0px;" onLoad="form_login.username.focus()">
<div style="position: relative; border: dashed 1px; vertical-align:middle; background-image:url(images/themes/locoroco/login_bg.png); height:217px; width:600px; left:50%; top:50%;margin-left:-300px; margin-top:-108px; ">
<table width="37%"  border="0" align="center" valign=center cellpadding="2" cellspacing="1" style="position:relative; left:150px; top:50px;">
  <form method="post" action="" id="form_login">
  <tr>
    <td width="27%"><div align="right">用户名</div></td>
    <td width="73%"><input name="username" type="text" class="inputbox" id="username" style="width:105px; height:20px; vertical-align:text-bottom;"></td>
  </tr>
  <tr>
    <td><div align="right">密 码</div></td>
    <td><input name="password" type="password" class="inputbox" id="password" style="width:105px; height:20px; vertical-align:text-bottom;"></td>
  </tr>
    <tr>
    <td><div align="right">验证码</div></td>
    <td><input name="confirmcode" type="text" class="inputbox" id=logincode style="width:50px; height:20px; vertical-align:text-bottom;"> <img src="<?php echo $_smarty_tpl->getVariable('confirmcode')->value;?>
"></td>
  </tr>
  <tr>
    <td><div align="right"></div></td>
    <td><input name="Submit" type="submit" class="button" value="登陆">
    <input name="controller" type="hidden" id="module" value="Account"> 
    <input name="action" type="hidden" id="proc" value="dologin">
    <input name="return_url" type="hidden" id="return_url" value="<?php echo $_smarty_tpl->getVariable('return_url')->value;?>
"> 
    </td>
  </tr></form>
</table>
</div>
</body>
</html>
