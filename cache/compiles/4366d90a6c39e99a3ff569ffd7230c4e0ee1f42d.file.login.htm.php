<?php /* Smarty version Smarty-3.0.7, created on 2011-07-20 15:08:55
         compiled from "E:\www\fw/Views/login.htm" */ ?>
<?php /*%%SmartyHeaderCode:284564e267f070e94f0-38516557%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '4366d90a6c39e99a3ff569ffd7230c4e0ee1f42d' => 
    array (
      0 => 'E:\\www\\fw/Views/login.htm',
      1 => 1303358006,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '284564e267f070e94f0-38516557',
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
<style type="text/css">
<!--
.style1 {color: #FF6600}
-->
</style>
</head>

<body>
<p>&nbsp;</p>
<div align="center" style="vertical-align:middle; height:600">
<table width="37%"  border="0" align="center" valign=center cellpadding="2" cellspacing="1" class="border">
  <form method="post" action="">
  <tr>
    <td width="27%"><div align="right">用户名</div></td>
    <td width="73%"><input name="username" type="text" class="border" id="username"></td>
  </tr>
  <tr>
    <td><div align="right">密码</div></td>
    <td><input name="password" type="password" class="border" id="password"></td>
  </tr>
    <tr>
    <td><div align="right">验证码</div></td>
    <td><input name="confirmcode" type="text" class="border" id=logincode"> <img src="<?php echo $_smarty_tpl->getVariable('confirmcode')->value;?>
"></td>
  </tr>
  <tr>
    <td><div align="right"></div></td>
    <td><input name="Submit" type="submit" class="border" value="登陆">
    <input name="controller" type="hidden" id="module" value="Account"> 
    <input name="action" type="hidden" id="proc" value="dologin"> 
    <input name="return_url" type="hidden" id="return_url" value="<?php echo $_smarty_tpl->getVariable('return_url')->value;?>
"> 
    <a href="<?php echo $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['url_helper'][0][0]->_urlhelper(array('type'=>"proc",'param1'=>"Account",'param2'=>"register"),$_smarty_tpl);?>
" target="_top"><span class="style1">注册</span></a></td>
  </tr></form>
</table>
</div>
</body>
</html>
