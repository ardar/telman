<?php /* Smarty version Smarty-3.0.7, created on 2012-06-28 11:29:07
         compiled from "/www/ardar/tel/Views/locoroco/login.htm" */ ?>
<?php /*%%SmartyHeaderCode:481384634febcf8387a653-02213056%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '661b57a0a6ac73e94e58c44225f2c60a52665fc0' => 
    array (
      0 => '/www/ardar/tel/Views/locoroco/login.htm',
      1 => 1340854144,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '481384634febcf8387a653-02213056',
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
    <td width="27%"><div align="right">用 户 名</div></td>
    <td width="73%"><input name="username" type="text" class="inputbox" id="username" style="width:105px; height:20px; vertical-align:text-bottom;"></td>
  </tr>
  <tr>
    <td><div align="right">密 码</div></td>
    <td><input name="password" type="password" class="inputbox" id="password" style="width:105px; height:20px; vertical-align:text-bottom;"></td>
  </tr>
    <tr>
    <td><div align="right">自动登录</div> </td>
    <td><input name="savestate" type="checkbox" class="inputbox" id=savestate >  (勿在公共电脑选择)</td>
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
