<?php /* Smarty version Smarty-3.0.7, created on 2011-09-09 22:40:51
         compiled from "/www/ardar/telman/Views/_table.csv" */ ?>
<?php /*%%SmartyHeaderCode:15617553834e6a25739e2490-77989572%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ee36b994154429553cb141af85d89bcc18151c89' => 
    array (
      0 => '/www/ardar/telman/Views/_table.csv',
      1 => 1314886819,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '15617553834e6a25739e2490-77989572',
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