<?php /* Smarty version Smarty-3.0.7, created on 2011-07-28 15:56:24
         compiled from "C:\TMWWW/Views/_table.csv" */ ?>
<?php /*%%SmartyHeaderCode:118144e3116287c1036-73729626%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '4b47d432b47b3627003938731d40b3b26207f00e' => 
    array (
      0 => 'C:\\TMWWW/Views/_table.csv',
      1 => 1311594199,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '118144e3116287c1036-73729626',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php  $_smarty_tpl->tpl_vars['field'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('fields')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['field']->key => $_smarty_tpl->tpl_vars['field']->value){
?><?php if ($_smarty_tpl->tpl_vars['field']->value['type']!="hidden"){?><?php echo $_smarty_tpl->tpl_vars['field']->value['title'];?>
,<?php }?><?php }} ?>

<?php  $_smarty_tpl->tpl_vars['rs'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('rows')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['rs']->key => $_smarty_tpl->tpl_vars['rs']->value){
?>
<?php  $_smarty_tpl->tpl_vars['field'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['fieldkey'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('fields')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['field']->key => $_smarty_tpl->tpl_vars['field']->value){
 $_smarty_tpl->tpl_vars['fieldkey']->value = $_smarty_tpl->tpl_vars['field']->key;
?><?php if ($_smarty_tpl->tpl_vars['field']->value['type']!="hidden"){?><?php echo $_smarty_tpl->tpl_vars['rs']->value[$_smarty_tpl->tpl_vars['fieldkey']->value];?>
,<?php }?><?php }} ?>

<?php }} ?>