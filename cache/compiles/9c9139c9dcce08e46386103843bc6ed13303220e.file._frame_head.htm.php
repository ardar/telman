<?php /* Smarty version Smarty-3.0.7, created on 2011-09-13 23:13:57
         compiled from "/www/ardar/tel/Views/_frame_head.htm" */ ?>
<?php /*%%SmartyHeaderCode:617832834e6f7335304ac2-91243306%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '9c9139c9dcce08e46386103843bc6ed13303220e' => 
    array (
      0 => '/www/ardar/tel/Views/_frame_head.htm',
      1 => 1314886818,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '617832834e6f7335304ac2-91243306',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<LINK href="style.css" type="text/css" rel="stylesheet">
<title>Head</title>
</head>
<body><table width="100%" border="0" cellspacing="0" cellpadding="0" height="50px">
  <tr style="background-image: url(images/pagetitlebk.png); vertical-align:top; text-align:left;">
    <td></td>
    <td width=50% style="text-align:right; vertical-align:bottom; padding:8px; padding-right:25px; ">
    <!--a style="color:white;padding-left:10px" >已登录为: [<?php echo $_smarty_tpl->getVariable('groupname')->value;?>
]<?php echo $_smarty_tpl->getVariable('username')->value;?>
 </a-->
    <a style="color:white;padding-left:10px" target="frame_main" href='<?php echo $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['url_helper'][0][0]->_urlhelper(array('type'=>'action','param1'=>'Account','param2'=>'profile'),$_smarty_tpl);?>
'><img src="images/profile.png" > 个人资料</a>
    <a style="color:white;padding-left:10px" href='<?php echo $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['url_helper'][0][0]->_urlhelper(array('type'=>'action','param1'=>'Account','param2'=>'logout'),$_smarty_tpl);?>
'><img src="images/logout.png" > 注销登录</a></td>
  </tr>
</table>
</body>
</html>
