<?php /* Smarty version Smarty-3.0.7, created on 2011-09-14 13:45:48
         compiled from "/www/ardar/tel/Views/_form.htm" */ ?>
<?php /*%%SmartyHeaderCode:3012558004e703f8c644ae3-94268512%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '10f46e9fb8b97d6a325d1aa05da5d0e19615b6dc' => 
    array (
      0 => '/www/ardar/tel/Views/_form.htm',
      1 => 1314886818,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '3012558004e703f8c644ae3-94268512',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<table width='<?php echo $_smarty_tpl->getVariable('form')->value['_width'];?>
' border=0 cellpadding=2 cellspacing=1 bgcolor='<?php echo $_smarty_tpl->getVariable('form')->value['_bgcolor'];?>
' class='<?php echo $_smarty_tpl->getVariable('form')->value['_class'];?>
' <?php echo $_smarty_tpl->getVariable('form')->value['_ext'];?>
>
<form id='<?php echo $_smarty_tpl->getVariable('form')->value['_id'];?>
' name='<?php echo $_smarty_tpl->getVariable('form')->value['_name'];?>
' action='' method='<?php echo $_smarty_tpl->getVariable('form')->value['_method'];?>
'  enctype='multipart/form-data'>
<TR >
<TD colspan=2 class=tabletitle>
	<b><?php echo $_smarty_tpl->getVariable('form')->value['_title'];?>
</b>
</TD>
</TR>
<?php  $_smarty_tpl->tpl_vars['field'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('form')->value['_fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['field']->key => $_smarty_tpl->tpl_vars['field']->value){
?>
    <?php if ($_smarty_tpl->tpl_vars['field']->value['type']=='html'){?>
    	<?php echo $_smarty_tpl->tpl_vars['field']->value['body'];?>

    <?php }else{ ?>
        <tr class=fieldtr <?php if ($_smarty_tpl->getVariable('form')->value['_isreadonly']){?>Disabled='true'<?php }?> >
        <td class=fieldtd>
        	<?php echo $_smarty_tpl->tpl_vars['field']->value['title'];?>

        </td>
        <td class=valuetd>
        	<?php echo $_smarty_tpl->tpl_vars['field']->value['body'];?>

        </td>
        </tr>
    <?php }?>
<?php }} ?>
<tr >
<td height=30> </td><td>
<?php echo $_smarty_tpl->getVariable('form')->value['_hidden'];?>

<?php if ($_smarty_tpl->getVariable('form')->value['_buttons']!=''){?>
	<?php echo $_smarty_tpl->getVariable('form')->value['_buttons'];?>

<?php }else{ ?>
    <?php if ($_smarty_tpl->getVariable('form')->value['_isreadonly']){?>
        <input name='back' type='button' class='button' value='返回' onclick="window.history.back()">
    <?php }else{ ?>
        <input name='Submit' type='submit' class='button' value='提交' 
            onclick="this.form.submit();this.disabled=true;this.value='正在提交...'">
        <input name='reset' type='reset' class='button' value='重填'>
        <input name='back' type='button' class='button' value='取消' onclick="window.history.back()">
    <?php }?>
<?php }?>
</td>
</tr>
</form>
</table>
