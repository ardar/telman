<?php /* Smarty version Smarty-3.0.7, created on 2011-09-22 15:16:29
         compiled from "/www/ardar/tel/Views/_table.csv" */ ?>
<?php /*%%SmartyHeaderCode:19644098454e7ae0cd974200-34926276%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'a33b0061596892f226a3be8fdf62615b9537cab5' => 
    array (
      0 => '/www/ardar/tel/Views/_table.csv',
      1 => 1314886819,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '19644098454e7ae0cd974200-34926276',
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