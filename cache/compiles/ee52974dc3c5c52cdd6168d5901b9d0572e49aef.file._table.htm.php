<?php /* Smarty version Smarty-3.0.7, created on 2011-09-13 23:13:59
         compiled from "/www/ardar/tel/Views/_table.htm" */ ?>
<?php /*%%SmartyHeaderCode:13390905704e6f7337303530-90329107%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ee52974dc3c5c52cdd6168d5901b9d0572e49aef' => 
    array (
      0 => '/www/ardar/tel/Views/_table.htm',
      1 => 1314886819,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '13390905704e6f7337303530-90329107',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>

<?php if ($_smarty_tpl->getVariable('table')->value['_toolbar']!=''){?>
<table class=toolbar><tr><td>
<?php echo $_smarty_tpl->getVariable('table')->value['_toolbar'];?>

</td></tr></table>
<?php }?>
<table width="<?php echo $_smarty_tpl->getVariable('table')->value['_width'];?>
"  border=0 cellpadding=2 cellspacing=1 class='<?php echo $_smarty_tpl->getVariable('table')->value['_class'];?>
' <?php echo $_smarty_tpl->getVariable('table')->value['table_ext'];?>
>
<TR>
<TD bgcolor=#ffffff class=tabletitle colspan="<?php echo $_smarty_tpl->getVariable('table')->value['_fieldcount'];?>
">
    <table width=100% height=10 cellpadding=0 cellspacing=0 border=0>
    <form id="navform_<?php echo $_smarty_tpl->getVariable('table')->value['_id'];?>
" action="" method=GET><TR><TD align=left valign=center >
    <B><?php echo $_smarty_tpl->getVariable('table')->value['_title'];?>
</B>
    </TD>
    <TD class="navbar">
    <?php echo $_smarty_tpl->getVariable('table')->value['_navbar'];?>

    </TD></TR>
    </form>
	</table>
</TD>
</TR>
<?php if ($_smarty_tpl->getVariable('table')->value['_headbar']!=''){?>
<form id="headform_<?php echo $_smarty_tpl->getVariable('table')->value['_id'];?>
" action='' method=GET>
<TR><TD class=headbar colspan="<?php echo $_smarty_tpl->getVariable('table')->value['_fieldcount'];?>
">
    <?php echo $_smarty_tpl->getVariable('table')->value['_headbar'];?>

</TD></TR>
 </form>
<?php }?>
<form id="dataform_<?php echo $_smarty_tpl->getVariable('table')->value['_id'];?>
" name=dataform method=Post>
<tr class=tdbg>
<?php  $_smarty_tpl->tpl_vars['field'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('table')->value['_fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['field']->key => $_smarty_tpl->tpl_vars['field']->value){
?> 
<?php if ($_smarty_tpl->tpl_vars['field']->value['type']!="hidden"){?>
<td><div align='center'><?php echo $_smarty_tpl->tpl_vars['field']->value['title'];?>
</div></td>
<?php }?>
<?php }} ?>
</tr>
<?php  $_smarty_tpl->tpl_vars['rs'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('table')->value['_rows']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['rs']->key => $_smarty_tpl->tpl_vars['rs']->value){
?>
<tr class=celltr 
onclick="if(this.className=='celltr_selected')this.className='celltr';else {this.className='celltr_selected';}" 
onmouseover="if(this.className!='celltr_selected')this.className='celltr_hover';" 
onmouseout="if(this.className!='celltr_selected')this.className='celltr';" >
    <?php  $_smarty_tpl->tpl_vars['field'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['fieldkey'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('table')->value['_fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['field']->key => $_smarty_tpl->tpl_vars['field']->value){
 $_smarty_tpl->tpl_vars['fieldkey']->value = $_smarty_tpl->tpl_vars['field']->key;
?> 
	<?php if ($_smarty_tpl->tpl_vars['field']->value['type']!="hidden"){?>
    <td class=celltd <?php echo $_smarty_tpl->tpl_vars['field']->value['ext'];?>
 ><div align="<?php echo $_smarty_tpl->tpl_vars['field']->value['align'];?>
"><?php echo $_smarty_tpl->tpl_vars['rs']->value[$_smarty_tpl->tpl_vars['fieldkey']->value];?>
</div></td>
    <?php }?>
    <?php }} ?>
</tr>
<?php }} ?>
<?php echo $_smarty_tpl->getVariable('table')->value['_hidden'];?>

<?php if ($_smarty_tpl->getVariable('table')->value['_footer']!=''){?>
<TR> 
<TD class=footbar colspan="<?php echo $_smarty_tpl->getVariable('table')->value['_fieldcount'];?>
">
    <?php echo $_smarty_tpl->getVariable('table')->value['_footer'];?>

</TD></TR>
</form>
<?php }?>
<TR>
<TD class=pager colspan="<?php echo $_smarty_tpl->getVariable('table')->value['_fieldcount'];?>
">
<?php echo $_smarty_tpl->getVariable('table')->value['_pager'];?>
</TD></TR>
</table>
