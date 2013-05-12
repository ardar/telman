<?php /* Smarty version Smarty-3.0.7, created on 2011-09-13 23:14:02
         compiled from "/www/ardar/tel/Views/_tree_view.xml" */ ?>
<?php /*%%SmartyHeaderCode:16395057524e6f733aa624e1-11486352%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c1f65d79868c80225a5af101ad32766dc3e62d42' => 
    array (
      0 => '/www/ardar/tel/Views/_tree_view.xml',
      1 => 1314886820,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '16395057524e6f733aa624e1-11486352',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php echo '<?xml';?> version="1.0" <?php echo '?>';?>

<TreeNode id="<?php echo $_smarty_tpl->getVariable('root')->value['id'];?>
" text="<?php echo $_smarty_tpl->getVariable('root')->value['text'];?>
" icon="<?php echo $_smarty_tpl->getVariable('root')->value['image'];?>
" iconopen="<?php echo $_smarty_tpl->getVariable('root')->value['image'];?>
" url="<?php echo $_smarty_tpl->getVariable('root')->value['href'];?>
" target="<?php echo $_smarty_tpl->getVariable('root')->value['target'];?>
" 
	subnodelink="<?php echo $_smarty_tpl->getVariable('root')->value['xml'];?>
">
	<?php  $_smarty_tpl->tpl_vars['rs'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('list')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['rs']->key => $_smarty_tpl->tpl_vars['rs']->value){
?>
<TreeNode id="<?php echo $_smarty_tpl->tpl_vars['rs']->value['id'];?>
" text="<?php echo $_smarty_tpl->tpl_vars['rs']->value['text'];?>
" icon="<?php echo $_smarty_tpl->tpl_vars['rs']->value['image'];?>
" iconopen="<?php echo $_smarty_tpl->tpl_vars['rs']->value['image'];?>
" url="<?php echo $_smarty_tpl->tpl_vars['rs']->value['href'];?>
" target="<?php echo $_smarty_tpl->tpl_vars['rs']->value['target'];?>
" 
<?php if ($_smarty_tpl->tpl_vars['rs']->value['xml']!=''){?>subnodelink="<?php echo $_smarty_tpl->tpl_vars['rs']->value['xml'];?>
"<?php }?> <?php if ($_smarty_tpl->tpl_vars['rs']->value['hover']!=''){?>hover="<?php echo $_smarty_tpl->tpl_vars['rs']->value['hover'];?>
"<?php }?> clickjs="<?php echo $_smarty_tpl->tpl_vars['rs']->value['clickjs'];?>
" dbclickjs="alert('dbclick')">
	</TreeNode>
	<?php }} ?>
</TreeNode>
