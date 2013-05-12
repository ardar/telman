<?php /* Smarty version Smarty-3.0.7, created on 2011-09-13 10:44:08
         compiled from "/www/ardar/telman/Views/_ui_calendar.htm" */ ?>
<?php /*%%SmartyHeaderCode:20752322554e6ec378e807b9-48460048%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '55d8861f6d1a1804ad2bbea27e18af8ae522997a' => 
    array (
      0 => '/www/ardar/telman/Views/_ui_calendar.htm',
      1 => 1314886820,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '20752322554e6ec378e807b9-48460048',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<html>
<head>
<title>日历</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="style.css" rel="stylesheet" type="text/css">


<script language="JavaScript">
/*****************************************************************************
                                   日期资料
*****************************************************************************/

var lunarInfo=new Array(
0x4bd8,0x4ae0,0xa570,0x54d5,0xd260,0xd950,0x5554,0x56af,0x9ad0,0x55d2,
0x4ae0,0xa5b6,0xa4d0,0xd250,0xd255,0xb54f,0xd6a0,0xada2,0x95b0,0x4977,
0x497f,0xa4b0,0xb4b5,0x6a50,0x6d40,0xab54,0x2b6f,0x9570,0x52f2,0x4970,
0x6566,0xd4a0,0xea50,0x6a95,0x5adf,0x2b60,0x86e3,0x92ef,0xc8d7,0xc95f,
0xd4a0,0xd8a6,0xb55f,0x56a0,0xa5b4,0x25df,0x92d0,0xd2b2,0xa950,0xb557,
0x6ca0,0xb550,0x5355,0x4daf,0xa5b0,0x4573,0x52bf,0xa9a8,0xe950,0x6aa0,
0xaea6,0xab50,0x4b60,0xaae4,0xa570,0x5260,0xf263,0xd950,0x5b57,0x56a0,
0x96d0,0x4dd5,0x4ad0,0xa4d0,0xd4d4,0xd250,0xd558,0xb540,0xb6a0,0x95a6,
0x95bf,0x49b0,0xa974,0xa4b0,0xb27a,0x6a50,0x6d40,0xaf46,0xab60,0x9570,
0x4af5,0x4970,0x64b0,0x74a3,0xea50,0x6b58,0x5ac0,0xab60,0x96d5,0x92e0,
0xc960,0xd954,0xd4a0,0xda50,0x7552,0x56a0,0xabb7,0x25d0,0x92d0,0xcab5,
0xa950,0xb4a0,0xbaa4,0xad50,0x55d9,0x4ba0,0xa5b0,0x5176,0x52bf,0xa930,
0x7954,0x6aa0,0xad50,0x5b52,0x4b60,0xa6e6,0xa4e0,0xd260,0xea65,0xd530,
0x5aa0,0x76a3,0x96d0,0x4afb,0x4ad0,0xa4d0,0xd0b6,0xd25f,0xd520,0xdd45,
0xb5a0,0x56d0,0x55b2,0x49b0,0xa577,0xa4b0,0xaa50,0xb255,0x6d2f,0xada0,
0x4b63,0x937f,0x49f8,0x4970,0x64b0,0x68a6,0xea5f,0x6b20,0xa6c4,0xaaef,
0x92e0,0xd2e3,0xc960,0xd557,0xd4a0,0xda50,0x5d55,0x56a0,0xa6d0,0x55d4,
0x52d0,0xa9b8,0xa950,0xb4a0,0xb6a6,0xad50,0x55a0,0xaba4,0xa5b0,0x52b0,
0xb273,0x6930,0x7337,0x6aa0,0xad50,0x4b55,0x4b6f,0xa570,0x54e4,0xd260,
0xe968,0xd520,0xdaa0,0x6aa6,0x56df,0x4ae0,0xa9d4,0xa4d0,0xd150,0xf252,
0xd520);


/*****************************************************************************
                                      日期计算
*****************************************************************************/

//====================================== 返回农历 y年的总天数
function lYearDays(y) {
 var i, sum = 348;
 for(i=0x8000; i>0x8; i>>=1) sum += (lunarInfo[y-1900] & i)? 1: 0;
 return(sum+leapDays(y));
}

//====================================== 返回农历 y年闰月的天数
function leapDays(y) {
 if(leapMonth(y)) return( (lunarInfo[y-1899]&0xf)==0xf? 30: 29);
 else return(0);
}

//====================================== 返回农历 y年闰哪个月 1-12 , 没闰返回 0
function leapMonth(y) {
 var lm = lunarInfo[y-1900] & 0xf;
 return(lm==0xf?0:lm);
}

//====================================== 返回农历 y年m月的总天数
function monthDays(y,m) {
 return( (lunarInfo[y-1900] & (0x10000>>m))? 30: 29 );
}


//====================================== 算出农历, 传入日期控件, 返回农历日期控件
//                                       该控件属性有 .year .month .day .isLeap
function Lunar(objDate) {

   var i, leap=0, temp=0;
   var offset   = (Date.UTC(objDate.getFullYear(),objDate.getMonth(),objDate.getDate()) - Date.UTC(1900,0,31))/86400000;

   for(i=1900; i<2100 && offset>0; i++) { temp=lYearDays(i); offset-=temp; }

   if(offset<0) { offset+=temp; i--; }

   this.year = i;

   leap = leapMonth(i); //闰哪个月
   this.isLeap = false;

   for(i=1; i<13 && offset>0; i++) {
      //闰月
      if(leap>0 && i==(leap+1) && this.isLeap==false)
         { --i; this.isLeap = true; temp = leapDays(this.year); }
      else
         { temp = monthDays(this.year, i); }

      //解除闰月
      if(this.isLeap==true && i==(leap+1)) this.isLeap = false;

      offset -= temp;
   }

   if(offset==0 && leap>0 && i==leap+1)
      if(this.isLeap)
         { this.isLeap = false; }
      else
         { this.isLeap = true; --i; }

   if(offset<0){ offset += temp; --i; }

   this.month = i;
   this.day = offset + 1;
}
function MM_findObj(n, d) { //v4.0
  var p,i,x;
  if(!d)
     d=document;
  if((p=n.indexOf("?"))>0 && parent.frames.length) 
  {
     d=parent.frames[n.substring(p+1)].document;
     n=n.substring(0,p);
  }
  if(!(x=d[n])&&d.all)
     x=d.all[n];
  for (i=0;!x&&i<d.forms.length;i++)
     x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++)
     x=MM_findObj(n,d.layers[i].document);
  if(!x && document.getElementById)
     x=document.getElementById(n);
  return x;
}

function doCal()
{
  n=new Date();
  cm="<?php echo $_smarty_tpl->getVariable('init_month')->value;?>
";
  n.setFullYear(<?php echo $_smarty_tpl->getVariable('init_year')->value;?>
);
  n.setMonth(cm-1);

  writeMonth(n);
  focus();
}

function set_year(op)
{
  if(op==-1 && YEAR.selectedIndex==0)
     return;
  if(op==1 && YEAR.selectedIndex==(YEAR.options.length-1))
     return;

  YEAR.selectedIndex=YEAR.selectedIndex+op;

  yr=YEAR.value;
  cm=MONTH.value;
  doOther(yr,cm);
}

function set_mon(op)
{
  if(op==-1 && MONTH.selectedIndex==0)
  {
     if(YEAR.selectedIndex>0)
     {
        MONTH.selectedIndex=MONTH.selectedIndex=11;
        YEAR.selectedIndex=YEAR.selectedIndex-1;
     }
  }
  else if(op==1 && MONTH.selectedIndex==11)
  {
     if(YEAR.selectedIndex<200)
     {
        MONTH.selectedIndex=MONTH.selectedIndex=0;
        YEAR.selectedIndex=YEAR.selectedIndex+1;
     }
  }
  else
     MONTH.selectedIndex=MONTH.selectedIndex+op;

  yr=YEAR.value;
  cm=MONTH.value;
  doOther(yr,cm);
}

function doOther(yr,cm)
{
  n=new Date();
  n.setFullYear(yr);
  n.setDate(1);
  n.setMonth(cm-1);
  writeMonth(n);
}

function writeMonth(n)
{
  yr=YEAR.value;
  cm=MONTH.value;
  n.setDate(1);dow=n.getDay();moy=n.getMonth();

  for (i=0;i<41;i++)
  {
    if ((i<dow)||(moy!=n.getMonth()))
       dt="&nbsp;";
    else
    {
      dt=n.getDate();
      n.setDate(n.getDate()+1);

      if(dt==<?php echo $_smarty_tpl->getVariable('curr_day')->value;?>
&&cm==<?php echo $_smarty_tpl->getVariable('curr_month')->value;?>
&&yr==<?php echo $_smarty_tpl->getVariable('curr_year')->value;?>
)
         dt="<div onclick='dateClick("+dt+")' style='width:100%;height:100%;cursor:hand;'><font color=red><b>"+dt+"</b></font></div>";
      else
         dt="<div onclick='dateClick("+dt+")' style='width:100%;height:100%;cursor:hand;'><b>"+dt+"</b></div>";
    }

    MM_findObj('day')[i].innerHTML="<b>"+dt+"</b>";
  }
}

function setPointer(theRow, thePointerColor)
{
   theRow.bgColor = thePointerColor;
}

var parent_window = window.dialogArguments;

function dateClick(theDate)
{
   yr=YEAR.value;
   cm=MONTH.value;

   if(theDate<10)
      theDate="0"+theDate;
   date_str=yr+"-"+cm+"-"+theDate;

   if(true)
      window.returnValue=date_str;
   else
      window.returnValue=date_str+date_time.substr(len);
   if(0==1)
	{
	   var sDObj;
	   if(parent_window.form1.BEGIN_DATE.value!='')
	   {
			sDObj = new Date(parent_window.form1.BEGIN_DATE.value.replace("-",","));

			 lDObj = new Lunar(sDObj);     //农历
			 if(lDObj.month<10) lDObj.month="0"+lDObj.month;
			 if(lDObj.day<10) lDObj.day="0"+lDObj.day;
			  parent_window.form1.BEGIN_DATE1.value=lDObj.year+"-"+lDObj.month+"-"+lDObj.day;
	   }
	}
   window.close();
}

function thisMonth()
{
   YEAR.selectedIndex=(<?php echo $_smarty_tpl->getVariable('curr_year')->value;?>
-1900);
   MONTH.selectedIndex=(<?php echo $_smarty_tpl->getVariable('curr_month')->value;?>
-1);
   doCal();
}

function add_time()
{
    var date_time=parent_window.form1.BEGIN_DATE.value;
    var cur_time=hour.value+":"+minite.value+":"+second.value;

    if(parent_window.form1.BEGIN_DATE.value=="")
       parent_window.form1.BEGIN_DATE.value=cur_time;
    else
    {
       var len=date_time.indexOf(" ");
       if(len<0)
          len=date_time.length;
       parent_window.form1.BEGIN_DATE.value=date_time.substr(0,len)+" "+cur_time;
	   var sDObj;
	   if(0==1)
	   {
	   if(parent_window.form1.BEGIN_DATE.value!='')
	   {
			sDObj = new Date(parent_window.form1.BEGIN_DATE.value.replace("-",","));

			 lDObj = new Lunar(sDObj);     //农历
			  parent_window.form1.BEGIN_DATE1.value=lDObj.year+"-"+lDObj.month+"-"+lDObj.day;
	   }
	   }
    }
    window.close();
}

function del_time()
{
    var date_time=parent_window.form1.BEGIN_DATE.value;
    if(parent_window.form1.BEGIN_DATE.value!="")
    {
       var len=date_time.indexOf(" ");
       if(len<0)
          len=date_time.length;
       parent_window.form1.BEGIN_DATE.value=date_time.substr(0,len);
    }
    window.close();
}
</script>
</head>

<body onLoad="doCal();" topmargin="0" leftmargin="0" rightmargin="0" bottommargin="0">
<table width="100%" height="100%" border="0" cellspacing="1" class="border" bgcolor="#CCC" cellpadding="0" align="center" style="padding:3px">
  <tr class="title">
    <td colspan="7" >
    <table width=100%&gt;<tr><td>
    <input type="button" value=" < " style="border:solid 1px #666" title="上一年" onClick="set_year(-1);"><select name="YEAR" class="SmallSelect" style="font-weight:bold" onChange="set_year(0);">
    <?php  $_smarty_tpl->tpl_vars['i'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['i']->value = 1900;
  if ($_smarty_tpl->getVariable('i')->value<2100){ for ($_foo=true;$_smarty_tpl->getVariable('i')->value<2100; $_smarty_tpl->tpl_vars['i']->value++){
?>
          <option value="<?php echo $_smarty_tpl->tpl_vars['i']->value;?>
" <?php if ($_smarty_tpl->tpl_vars['i']->value==$_smarty_tpl->getVariable('curr_year')->value){?>selected<?php }?> ><?php echo $_smarty_tpl->tpl_vars['i']->value;?>
</option>
    <?php }} ?>
        </select><input type="button" value=" > " style="border:solid 1px #666" title="下一年" onClick="set_year(1);"><b>年</b><!-------------- 月 ------------><input type="button" value=" < "  style="border:solid 1px #666" title="上一月" onClick="set_mon(-1);"><select name="MONTH" class="SmallSelect"  style="border:solid 1px #666" onChange="set_mon(0);">
    <?php  $_smarty_tpl->tpl_vars['i'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['i']->value = 1;
  if ($_smarty_tpl->getVariable('i')->value<13){ for ($_foo=true;$_smarty_tpl->getVariable('i')->value<13; $_smarty_tpl->tpl_vars['i']->value++){
?>
          <option value="<?php if ($_smarty_tpl->tpl_vars['i']->value<10){?>0<?php }?><?php echo $_smarty_tpl->tpl_vars['i']->value;?>
" <?php if ($_smarty_tpl->tpl_vars['i']->value==$_smarty_tpl->getVariable('curr_month')->value){?>selected<?php }?> ><?php echo $_smarty_tpl->tpl_vars['i']->value;?>
</option>
    <?php }} ?>
        </select><input type="button" value=" > "  style="border:solid 1px #666" title="下一月" onClick="set_mon(1);"><b>月</b>
        </td><td>
<a href="#" onClick="thisMonth();" style="float:right" class=button><b>显示本月</b></a>
</td></tr></table>
    </td>
  </tr>
  <tr align="center" class="tdbg">
    <td width="10%" bgcolor="#FFCCFF"><b>日</b></td>
    <td width="10%"><b>一</b></td>
    <td width="10%"><b>二</b></td>
    <td width="10%"><b>三</b></td>
    <td width="10%"><b>四</b></td>
    <td width="10%"><b>五</b></td>
    <td width="10%" bgcolor="#CCFFCC"><b>六</b></td>
  </tr>
  <tr bgcolor="#FFFFFF" align="center" style="cursor:hand">
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
  </tr>
  <tr bgcolor="#FFFFFF" align="center" style="cursor:hand">
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
  </tr>
  <tr bgcolor="#FFFFFF" align="center" style="cursor:hand">
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
  </tr>
  <tr bgcolor="#FFFFFF" align="center" style="cursor:hand">
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
  </tr>
  <tr bgcolor="#FFFFFF" align="center" style="cursor:hand">
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
  </tr>
  <tr bgcolor="#FFFFFF" align="center" style="cursor:hand">
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" id="day" onMouseOver="setPointer(this,'#E2E8FA')" onMouseOut="setPointer(this,'')"></td>
    <td width="10%" height=22></td>
  </tr>
</table>

</body>
</html>
