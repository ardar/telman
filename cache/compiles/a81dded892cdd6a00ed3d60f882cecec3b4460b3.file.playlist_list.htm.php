<?php /* Smarty version Smarty-3.0.7, created on 2011-07-20 15:16:58
         compiled from "E:\www\fw/Views/playlist_list.htm" */ ?>
<?php /*%%SmartyHeaderCode:43904e2680ea0488e5-71427355%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'a81dded892cdd6a00ed3d60f882cecec3b4460b3' => 
    array (
      0 => 'E:\\www\\fw/Views/playlist_list.htm',
      1 => 1310987803,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '43904e2680ea0488e5-71427355',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
<link href="style.css" rel="stylesheet" type="text/css">
<SCRIPT language=javascript src="ui/common.js">
</SCRIPT>
<style type="text/css">
.cellbox
{
	 background-color:#efefef;
}
</style>
</head>

<body>

<table width='100%'  border=0 cellpadding=2 cellspacing=1 class='data_table' >
<TR><TD bgcolor=#ffffff class=tabletitle colspan=5>
<table width=100% height=10 cellpadding=0 cellspacing=0 border=0 class=tdabletitle>
			<form id=navform action='' method=GET><TR><TD align=left valign=center >
<B>节目包 列表</B>
</TD><TD align=left style='padding-left:20px;'></TD></TR></form></table>
</TD></TR>
<TR><TD class=headbar >
<table width=100%&gt;<form id=headform action='' method=GET><TR>
<TD align=left> <input type='button' onClick="window.location.href='index.php?controller=Playlist&action=add';" class='button' value='创建节目包'> 名称<input name='playlistname' type='text' class='inputbox' id='playlistname' value='' >
<input name='action' type='hidden' class='inputbox' id='action' value='index' >
<input name='controller' type='hidden' class='inputbox' id='controller' value='Playlist' >
<input name='submit' type='submit'  class='button' value='查找'></TD></TR></form></table>
</TD></TR>
<form id=dataform name=dataform method=Post>
<tr class=tdbg>
<td >排序方式 : <a href="#">节目包名字</a> | <a href="#">宽高比</a></td>
</tr>
<tr class=celltr>
<td class=celltd>
<div nowrap style="text-align: left;">
<?php  $_smarty_tpl->tpl_vars['rs'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('list')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['rs']->key => $_smarty_tpl->tpl_vars['rs']->value){
?>
<div style="float:left; padding:8px; margin:1px; text-align: center;  " onClick="if(this.className=='celltr_selected')this.className='cellbox';else {this.className='celltr_selected';}" onMouseOver="if(this.className!='celltr_selected')this.className='celltr_hover';" 
onmouseout="if(this.className!='celltr_selected')this.className='cellbox';" class="cellbox">
                
<div style="padding:5px"><a href="index.php?controller=Playlist&action=playedit&playlistid=<?php echo $_smarty_tpl->tpl_vars['rs']->value['playlistid'];?>
"><img width=130 border=0 id='' src='<?php echo $_smarty_tpl->tpl_vars['rs']->value['preview'];?>
' ></a></div>
<div><a href="index.php?controller=Playlist&action=playedit&playlistid=<?php echo $_smarty_tpl->tpl_vars['rs']->value['playlistid'];?>
"><b><?php echo $_smarty_tpl->tpl_vars['rs']->value['playlistname'];?>
</b></a></div>  <div >播放时间: <?php echo $_smarty_tpl->tpl_vars['rs']->value['duration'];?>
</div>   
<div> <a class=button href="index.php?controller=Playlist&action=playedit&playlistid=<?php echo $_smarty_tpl->tpl_vars['rs']->value['playlistid'];?>
"> 详情 </a> <a class=button target=_blank href="index.php?controller=Playlist&action=preview&playlistid=<?php echo $_smarty_tpl->tpl_vars['rs']->value['playlistid'];?>
"> 预览 </a> <?php if ($_smarty_tpl->getVariable('canedit')->value){?><a class=button href="index.php?controller=Playlist&action=delete&playlistid=<?php echo $_smarty_tpl->tpl_vars['rs']->value['playlistid'];?>
"> 删除 </a><?php }?></div>
</div>
<?php }} ?>
</div>
</td>
</tr>
<TR class=pager><TD colspan=5>
<?php echo $_smarty_tpl->getVariable('pager')->value;?>
</table></TD></TR>
</table>

</BODY>
</HTML>