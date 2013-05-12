<?php /* Smarty version Smarty-3.0.7, created on 2011-07-25 11:21:53
         compiled from "C:\TMWWW/Views/_tree_view.htm" */ ?>
<?php /*%%SmartyHeaderCode:264064e2ce151a280a0-03756178%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '6f53554d2a5ec1c2a0666ba4d3731c7e684b2e82' => 
    array (
      0 => 'C:\\TMWWW/Views/_tree_view.htm',
      1 => 1310737980,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '264064e2ce151a280a0-03756178',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<title>Treeview</title>

	<link rel="StyleSheet" href="dtree.css" type="text/css" />
    <script type="text/javascript" src="ui/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="ui/tree.js"></script>
</head>
<body topmargin="3" leftmargin="5" rightMargin="0" bgcolor="#F7FCF3" marginwidth="0" marginheight="0" class=bodycolor>
<div id="hoverdiv"></div>
<div class="dtree" id="tree" >
	<script type="text/javascript">
		<!--
		//alert("asdf111");
		d = new Tree('d','tree',"<?php echo $_smarty_tpl->getVariable('treeview_url')->value;?>
");
		//d.Show();
		//-->
	</script>
 </div>
</body>

</html>