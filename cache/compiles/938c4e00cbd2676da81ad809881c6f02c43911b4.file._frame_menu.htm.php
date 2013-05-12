<?php /* Smarty version Smarty-3.0.7, created on 2011-09-13 23:13:57
         compiled from "/www/ardar/tel/Views/_frame_menu.htm" */ ?>
<?php /*%%SmartyHeaderCode:16181389534e6f733534e739-68980531%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '938c4e00cbd2676da81ad809881c6f02c43911b4' => 
    array (
      0 => '/www/ardar/tel/Views/_frame_menu.htm',
      1 => 1314886819,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '16181389534e6f733534e739-68980531',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<LINK href="style.css" type=text/css rel=stylesheet>
<title>Menu</title>
</head>
<body>
<table width="100%"  border="0" cellpadding="0" cellspacing="0" class=menu_table>
<tr class="tdbg">
<td><div align="center"><?php echo $_smarty_tpl->getVariable('groupname')->value;?>
</div></td>
</tr>
<tr class="">
<td><div align="center"><?php echo $_smarty_tpl->getVariable('username')->value;?>
</div></td>
</tr>
<?php unset($_smarty_tpl->tpl_vars['smarty']->value['section']['i']);
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['name'] = 'i';
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['loop'] = is_array($_loop=$_smarty_tpl->getVariable('roots')->value) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['show'] = true;
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['max'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['loop'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['step'] = 1;
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['start'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['step'] > 0 ? 0 : $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['loop']-1;
if ($_smarty_tpl->tpl_vars['smarty']->value['section']['i']['show']) {
    $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['total'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['loop'];
    if ($_smarty_tpl->tpl_vars['smarty']->value['section']['i']['total'] == 0)
        $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['show'] = false;
} else
    $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['total'] = 0;
if ($_smarty_tpl->tpl_vars['smarty']->value['section']['i']['show']):

            for ($_smarty_tpl->tpl_vars['smarty']->value['section']['i']['index'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['start'], $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['iteration'] = 1;
                 $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['iteration'] <= $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['total'];
                 $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['index'] += $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['step'], $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['iteration']++):
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['rownum'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['iteration'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['index_prev'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['index'] - $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['index_next'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['index'] + $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['first']      = ($_smarty_tpl->tpl_vars['smarty']->value['section']['i']['iteration'] == 1);
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['last']       = ($_smarty_tpl->tpl_vars['smarty']->value['section']['i']['iteration'] == $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['total']);
?>
		<!-- <table width="100%"  border="0" cellpadding="2" cellspacing="1" class="title"> -->
		<tr class="menutitle" >
		<td><div align="left">
		 <b><span onClick="if(item<?php echo $_smarty_tpl->getVariable('roots')->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['menuid'];?>
.style.display=='none') item<?php echo $_smarty_tpl->getVariable('roots')->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['menuid'];?>
.style.display=''; else item<?php echo $_smarty_tpl->getVariable('roots')->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['menuid'];?>
.style.display='none';" title="点击展开">
         <?php if ($_smarty_tpl->getVariable('roots')->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['image']){?>
         <IMG SRC="images/menu/<?php echo $_smarty_tpl->getVariable('roots')->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['image'];?>
" BORDER="0">
         <?php }else{ ?>
         <IMG SRC="images/menu/default.png" BORDER="0">
         <?php }?> 
         <?php echo $_smarty_tpl->getVariable('roots')->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['title'];?>
</span></b></div></td>
		</tr>
		<tbody id="item<?php echo $_smarty_tpl->getVariable('roots')->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['menuid'];?>
" <?php if ($_smarty_tpl->getVariable('roots')->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['showflag']==0){?>style="display:none"<?php }?>>
		<?php unset($_smarty_tpl->tpl_vars['smarty']->value['section']['j']);
$_smarty_tpl->tpl_vars['smarty']->value['section']['j']['name'] = 'j';
$_smarty_tpl->tpl_vars['smarty']->value['section']['j']['loop'] = is_array($_loop=$_smarty_tpl->getVariable('roots')->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['subs']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$_smarty_tpl->tpl_vars['smarty']->value['section']['j']['show'] = true;
$_smarty_tpl->tpl_vars['smarty']->value['section']['j']['max'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['loop'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['j']['step'] = 1;
$_smarty_tpl->tpl_vars['smarty']->value['section']['j']['start'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['step'] > 0 ? 0 : $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['loop']-1;
if ($_smarty_tpl->tpl_vars['smarty']->value['section']['j']['show']) {
    $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['total'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['loop'];
    if ($_smarty_tpl->tpl_vars['smarty']->value['section']['j']['total'] == 0)
        $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['show'] = false;
} else
    $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['total'] = 0;
if ($_smarty_tpl->tpl_vars['smarty']->value['section']['j']['show']):

            for ($_smarty_tpl->tpl_vars['smarty']->value['section']['j']['index'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['start'], $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['iteration'] = 1;
                 $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['iteration'] <= $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['total'];
                 $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['index'] += $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['step'], $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['iteration']++):
$_smarty_tpl->tpl_vars['smarty']->value['section']['j']['rownum'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['iteration'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['j']['index_prev'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['index'] - $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['j']['index_next'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['index'] + $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['j']['first']      = ($_smarty_tpl->tpl_vars['smarty']->value['section']['j']['iteration'] == 1);
$_smarty_tpl->tpl_vars['smarty']->value['section']['j']['last']       = ($_smarty_tpl->tpl_vars['smarty']->value['section']['j']['iteration'] == $_smarty_tpl->tpl_vars['smarty']->value['section']['j']['total']);
?>
		<tr>
		<td align="center" class="menuitem" onMouseOver="this.className='menuitem_hover'" onMouseOut="this.className='menuitem'">
        <div style="width:80px; text-align:left;"><a href="<?php echo $_smarty_tpl->getVariable('roots')->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['subs'][$_smarty_tpl->getVariable('smarty')->value['section']['j']['index']]['url'];?>
" style="display:block" target="frame_main">
        <?php if ($_smarty_tpl->getVariable('roots')->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['subs'][$_smarty_tpl->getVariable('smarty')->value['section']['j']['index']]['image']){?>
         <IMG SRC="images/menu/<?php echo $_smarty_tpl->getVariable('roots')->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['subs'][$_smarty_tpl->getVariable('smarty')->value['section']['j']['index']]['image'];?>
" BORDER="0">
         <?php }else{ ?>
         <IMG SRC="images/menu/default.png" BORDER="0">
         <?php }?> 
         <?php echo $_smarty_tpl->getVariable('roots')->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['subs'][$_smarty_tpl->getVariable('smarty')->value['section']['j']['index']]['title'];?>

         </a></div>
         </td>
		</tr>
		<?php endfor; endif; ?>
		</tbody>
		<tr >
		<td height=3 align="center"></td>
		</tr>
<?php endfor; endif; ?>
		<tr class=tdbg>
		<td align="center"> <div></div>
</td>
		</tr>
</table>
</body>
</html>
