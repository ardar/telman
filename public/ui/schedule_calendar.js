// JavaScript Document
function OnCellMouseOver(obj)
{
	//obj = window.document.getElementById(objid);
	if(obj.className=="calendar_week_cell")
		obj.className = "calendar_week_cell_over";
}

function OnCellMouseOut(obj)
{
	//obj = window.document.getElementById(objid);
	if(obj.className=="calendar_week_cell_over")
		obj.className = "calendar_week_cell";
}

function SelectAllDates()
{
	var year_obj = window.document.getElementById('year');
	var month_obj = window.document.getElementById('month');
	var year = year_obj.value;
	var month = month_obj.value;
	for(var i=1;i<=31;i++)
	{
		var datestr = String(i);
		if(datestr.length==1) datestr = "0"+datestr;
		var tempid = "c_"+year+'_'+month+'_'+datestr;
		var tempobj = window.document.getElementById(tempid);
		if(tempobj)
		{
			SelectCell(tempobj.id,1);
		}
	}
	UpdateBtnState();
}

function SelectAllCells()
{
	var begin_date_obj = window.document.getElementById('begin_date');
	var arrdate = begin_date_obj.value.split('-');
	var year = parseInt(arrdate[0],10);
	var month = parseInt(arrdate[1],10);
	var day = parseInt(arrdate[2],10);
	var dt = new Date(year,month-1,day,0,0,0);
	for(var i=0;i<7;i++)
	{
		var monstr = String(dt.getMonth()+1);
		if(monstr.length==1) monstr = "0"+monstr;
		var datestr = String(dt.getDate());
		if(datestr.length==1) datestr = "0"+datestr;
		var tempid = "col_"+dt.getFullYear()+'_'+monstr+'_'+datestr;
		var tempobj = window.document.getElementById(tempid);
		if(tempobj)
		{
			SelectDateColumn(tempobj.id,1);
		}
		dt.setDate(dt.getDate()+1);
	}
	UpdateBtnState();
}

function CancelSelectAllCells()
{
	var begin_date_obj = window.document.getElementById('begin_date');
	var arrdate = begin_date_obj.value.split('-');
	var year = parseInt(arrdate[0],10);
	var month = parseInt(arrdate[1],10);
	var day = parseInt(arrdate[2],10);
	var dt = new Date(year,month-1,day,0,0,0);
	for(var i=0;i<7;i++)
	{
		var monstr = String(dt.getMonth()+1);
		if(monstr.length==1) monstr = "0"+monstr;
		var datestr = String(dt.getDate());
		if(datestr.length==1) datestr = "0"+datestr;
		var tempid = "col_"+dt.getFullYear()+'_'+monstr+'_'+datestr;
		var tempobj = window.document.getElementById(tempid);
		if(tempobj)
		{
			SelectDateColumn(tempobj.id,-1);
		}
		dt.setDate(dt.getDate()+1);
	}
	for(var i=0;i<24;i++)
	{
		var tempid = "row_"+i;
		var tempobj = window.document.getElementById(tempid);
		if(tempobj)
		{
			SelectHourRow(tempobj.id,-1);
		}
	}
	CancelSelection();
	UpdateBtnState();
}

function CancelSelection()
{
	var selectedfield = window.document.getElementById('selected_id');
	if(selectedfield.value!='')
	{
		var arr = selectedfield.value.split(',');
		for(var i=0;i<arr.length;i++)
		{
			var obj = window.document.getElementById(arr[i]);
			if(obj)
			{
				SelectCell(obj.id, -1);
			}
		}
		UpdateBtnState();
	}
}

function SelectDateColumn(col_id, isForceTrue)
{
	var selctrl = window.document.getElementById(col_id);
	if(isForceTrue==null)
	{
		if(selctrl.className!="calendar_week_head_selected")
		{
			selctrl.className = "calendar_week_head_selected";
			isForceTrue = 1;
		}
		else
		{
			selctrl.className = "calendar_week_head";
			isForceTrue = -1;
		}
	}
	else
	{
		if(isForceTrue==1)
		{
			selctrl.className = "calendar_week_head_selected";
			isForceTrue = 1;
		}
		else
		{
			selctrl.className = "calendar_week_head";
			isForceTrue = -1;
		}
	}
	for(var i=0;i<24;i++)
	{
		var arr = col_id.split('_');
		var tempid = "c_"+arr[1]+'_'+arr[2]+'_'+arr[3]+'_'+i;
		var tempobj = window.document.getElementById(tempid);
		if(tempobj )
		{
			SelectCell(tempobj.id,isForceTrue);
		}
	}
	UpdateBtnState();
}

function SelectHourRow(row_id, isForceTrue)
{
	var selctrl = window.document.getElementById(row_id);
	if(isForceTrue==null)
	{
		if(selctrl.className!="calendar_week_time_selected")
		{
			selctrl.className = "calendar_week_time_selected";
			isForceTrue = 1;
		}
		else
		{
			selctrl.className = "calendar_week_time";
			isForceTrue = -1;
		}
	}
	else
	{
		if(isForceTrue==1)
		{
			selctrl.className = "calendar_week_time_selected";
			isForceTrue = 1;
		}
		else
		{
			selctrl.className = "calendar_week_time";
			isForceTrue = -1;
		}
	}
	
	var arrhour = row_id.split('_');
	var hour = arrhour[1];
	var begin_date_obj = window.document.getElementById('begin_date');
	var arrdate = begin_date_obj.value.split('-');
	var year = parseInt(arrdate[0],10);
	var month = parseInt(arrdate[1],10);
	var day = parseInt(arrdate[2],10);
	var dt = new Date(year,month-1,day,0,0,0);
	//dt.setYear(year);
	//dt.setMonth(month,date);
	for(var i=0;i<7;i++)
	{
		var monstr = String(dt.getMonth()+1);
		if(monstr.length==1) monstr = "0"+monstr;
		var datestr = String(dt.getDate());
		if(datestr.length==1) datestr = "0"+datestr;
		var tempid = "c_"+dt.getFullYear()+'_'+monstr+'_'+datestr+'_'+hour;
		//alert(tempid);
		var tempobj = window.document.getElementById(tempid);
		if(tempobj)
		{
			SelectCell(tempobj.id,isForceTrue);
		}
		dt.setDate(dt.getDate()+1);
	}
	UpdateBtnState();
}

function SelectCell(cellid, isForceTrue)
{
	var obj = window.document.getElementById(cellid);
	if(!obj)
	{
		return;
	}
	var selidctrl = window.document.getElementById('selected_id');
	if(obj.className!='calendar_week_cell_selected')
	{
		if(isForceTrue==-1)
		{
			return;
		}
		obj.className = "calendar_week_cell_selected";
		var addval = selidctrl.value=='' ? cellid : (","+cellid);
		selidctrl.value = selidctrl.value+addval;
	}
	else
	{
		if(isForceTrue==1)
		{
			return;
		}
		obj.className = "calendar_week_cell";
		var arr = selidctrl.value.split(',');
		var newval = "";
		for(var i=0;i<arr.length;i++)
		{
			if(arr[i]=='' || arr[i]==cellid)
			{
				continue;
			}
			else
			{
				newval += (newval=='') ? arr[i] : (","+arr[i]);
			}
		}
		selidctrl.value = newval;
	}
	//UpdateBtnState();
	//alert(selidctrl.value);
}

function AddToSchedule(playlistid)
{
	var selgroupobj = window.frames['main_right'].document.getElementById('logicalgroupid');
	if(selgroupobj.value=='' || selgroupobj.value==0)
	{
		alert("请先选择要编排界面的逻辑分组");
		return false;
	}
	var selhourobj = window.frames['main_right'].document.getElementById('selected_id');
	if(selhourobj.value=='' || selhourobj.value==0)
	{
		alert("请先在日历中选择要将播放列表加入的时段");
		return false;
	}
	window.frames['main_right'].AddPlaylist(playlistid);
}

function AddPlaylist(playlistid)
{
	window.document.getElementById('playlistid').value=playlistid;
	window.document.getElementById('action').value="addplaylist";
	window.document.getElementById('addtoschedule_form').submit();
	ShowWaitBoxAfterSubmit();
}

function DisableAll()
{
	for(var i=0;i<window.document.body.childNodes.length;i++)
	{
		var obj = window.document.body.childNodes[i];
		if(obj)
		{
			obj.disabled = true;
		}
	}
}

function ShowWaitBoxAfterSubmit()
{
	DisableAll();
	//window.document.body.innerHTML = window.document.body.innerHTML
	//	+('<div id="msg_box">'+"正在提交请求, 请稍后...."+'</div>');
}

function DelSelectItem(item_id)
{
	var field = window.document.getElementById('scheduleitemid');
	field.value = item_id;
	window.document.getElementById('action').value="delscheduleitem";
	window.document.getElementById('addtoschedule_form').submit();	
	ShowWaitBoxAfterSubmit();
}

function DelSelBlocks()
{
	var field = window.document.getElementById('selected_id');
	if(field.value=='')
	{
		alert("请先选择要删除的时段");
		return false;
	}
	var firstidarr = field.value.split(',');
	if(firstidarr.length>0 && firstidarr[0])
	{	
		var idarr = field.value.split('_');
		var begin_date_obj = window.document.getElementById('begin_date');
		var begin_date = begin_date_obj.value;
		if(idarr.length==4)
		{
			begin_date = idarr[1]+'-'+idarr[2]+'-'+idarr[3];
			begin_date_obj.value = begin_date;
		}
		window.document.getElementById('action').value="delblock";
		window.document.getElementById('addtoschedule_form').submit();
		ShowWaitBoxAfterSubmit();
	}
	
}

function EditSelBlocks()
{
	var field = window.document.getElementById('selected_id');
	if(field.value=='')
	{
		alert("请先选择要编辑的源单元格");
		return false;
	}
	var arr = field.value.split(',');
	if(arr.length>1)
	{
		alert("只能选择一个单元格进行编辑");
		return;
	}
	var begin_date_obj = window.document.getElementById('begin_date');
	var begin_date = begin_date_obj.value;
	var action = 'itemlist';
	var idarr = field.value.split('_');
	if(idarr.length==4)
	{
		action = 'week';
		begin_date = idarr[1]+'-'+idarr[2]+'-'+idarr[3];
		var group_obj = window.document.getElementById('logicalgroupid');
		var url = "index.php?controller=Schedule&action="+action
			+"&logicalgroupid="+group_obj.value+"&begin_date="+begin_date;
		window.location.href = url;
	}
	else
	{	
		var group_obj = window.document.getElementById('logicalgroupid');
		var url = "index.php?controller=Schedule&action="+action+"&selected_id="+field.value
			+"&logicalgroupid="+group_obj.value+"&begin_date="+begin_date;
		window.location.href = url;
	}
	return true;
}

function OnClickSelectDate(field_id, curr_date)
{
	if(CalendarDialog(field_id,curr_date))
	{	
		GoCalendarDate();
	}
	return false;
}

function GoCalendarDate()
{
	var date_obj = window.document.getElementById('begin_date');
	var group_obj = window.document.getElementById('logicalgroupid');
	var url = "index.php?controller=Schedule&action=week&begin_date="+date_obj.value+"&logicalgroupid="+group_obj.value;
	window.location.href = url;
	ShowWaitBoxAfterSubmit();
}

function OnClickCell(cellid)
{
	//if(window.event.ctrlKey)
	{
	SelectCell(cellid);
	}
	UpdateBtnState();
}

function DBClickCell(cellid)
{
	var obj = window.document.getElementById(cellid);
	if(!obj)
	{
		return;
	}
	var selidctrl = window.document.getElementById('selected_id');
	selidctrl.value = cellid;
	EditSelBlocks();
}

function CopySelBlocks()
{
	var field = window.document.getElementById('selected_id');
	if(field.value=='')
	{
		alert("请先选择要复制的源单元格");
		return false;
	}
	var arr = field.value.split(',');
	if(arr.length>1)
	{
		alert("只能选择一个源单元格进行复制");
		return;
	}
	var source_cell = window.document.getElementById(field.value);
	if(!source_cell || source_cell.innerHTML=='')
	{
		alert("该单元格没有节目, 无法复制");
		return false;
	}
	var sourcefield = window.document.getElementById('copysource_id');
	sourcefield.value = field.value;
	SelectCell(field.value, -1);
	UpdateBtnState();
	alert("复制成功, 下一步请选择一个或多个要粘贴的目的单元格");
	return true;
}

function PasteSelBlocks()
{
	var sourcefield = window.document.getElementById('copysource_id');
	if(!sourcefield || sourcefield.value=='')
	{
		alert("请先选中要复制的源单元格并点击复制按钮");
		return false;
	}
	var field = window.document.getElementById('selected_id');
	if(field.value=='')
	{
		alert("请先选择要粘贴的目的单元格");
		return false;
	}
	window.document.getElementById('action').value="copyblock";
	window.document.getElementById('addtoschedule_form').submit();
	ShowWaitBoxAfterSubmit();
}

function AuditSelBlocks()
{
	var sourcefield = window.document.getElementById('selected_id');
	if(!sourcefield || sourcefield.value=='')
	{
		alert("请先选择通过审核的时间段单元格");
		return false;
	}
	window.document.getElementById('action').value="auditblock";
	window.document.getElementById('addtoschedule_form').submit();
	ShowWaitBoxAfterSubmit();
}

function DisAuditSelBlocks()
{
	var sourcefield = window.document.getElementById('selected_id');
	if(!sourcefield || sourcefield.value=='')
	{
		alert("请先选择要取消审核的时间段单元格");
		return false;
	}
	window.document.getElementById('action').value="disauditblock";
	window.document.getElementById('addtoschedule_form').submit();
	ShowWaitBoxAfterSubmit();
}

function SelectAll()
{
	var selectedfield = window.document.getElementById('selected_id');
	if(selectedfield.value!='')
	{
		var arr = selectedfield.value.split(',');
		for(var i=0;i<arr.length;i++)
		{
			var obj = window.document.getElementById(arr[i]);
			if(obj)
			{
				SelectCell(obj.id, -1);
			}
		}
		UpdateBtnState();
	}
}

function UpdateBtnState()
{
	var sourcefield = window.document.getElementById('copysource_id');
	var selectedfield = window.document.getElementById('selected_id');
	if(sourcefield.value!='')
	{
		window.document.getElementById('btn_paste').disabled = '';
	}
	else
	{
		window.document.getElementById('btn_paste').disabled = 'true';
	}
	if(selectedfield.value!='')
	{
		window.document.getElementById('btn_edit').disabled = '';
		window.document.getElementById('btn_copy').disabled = '';
		window.document.getElementById('btn_delete').disabled = '';
		window.document.getElementById('btn_cancelselect').disabled = '';
		window.document.getElementById('btn_audit').disabled = '';
		window.document.getElementById('btn_disaudit').disabled = '';
	}
	else
	{
		window.document.getElementById('btn_edit').disabled = 'true';
		window.document.getElementById('btn_copy').disabled = 'true';
		window.document.getElementById('btn_delete').disabled = 'true';
		window.document.getElementById('btn_cancelselect').disabled = 'true';
		window.document.getElementById('btn_audit').disabled = 'true';
		window.document.getElementById('btn_disaudit').disabled = 'true';
	}
}

function OnLoadSchedules()
{
	var ids_obj = window.document.getElementById('selected_id');
	if(ids_obj)
	{
		var val = ids_obj.value;
		var arr = val.split(',');
		for(var i=0;i<arr.length;i++)
		{
			var obj = window.document.getElementById(arr[i]);
			if(obj)
			{
				if(obj.className!='calendar_week_cell_selected')
				{
					obj.className = "calendar_week_cell_selected";
				}
				else
				{
					obj.className = "calendar_week_cell";
				}
			}
		}
	}
	UpdateBtnState();
}

function OnItemMouseOver(item_id)
{
	var cp_id = "cell_item_"+item_id;
	var obj = window.document.getElementById(cp_id);
	if(obj)
	{
		//obj.style.backgroundColor="#eee";
		//obj.style.padding = "2px";
	}
	var cp_id = "cell_cp_"+item_id;
	var obj = window.document.getElementById(cp_id);
	if(obj)
	{
		obj.style.display="";
		
		var cp_id = "img_"+item_id;
		var obj = window.document.getElementById(cp_id);
		if(obj)
		{
			obj.style.display="none";
		}
	}
	return false;
}

function OnItemMouseOut(item_id)
{
	var cp_id = "cell_item_"+item_id;
	var obj = window.document.getElementById(cp_id);
	if(obj)
	{
		//obj.style.backgroundColor="";
		//obj.style.padding = "0px";
	}
	var cp_id = "cell_cp_"+item_id;
	var obj = window.document.getElementById(cp_id);
	if(obj)
	{
		obj.style.display="none";
		
		var cp_id = "img_"+item_id;
		var obj = window.document.getElementById(cp_id);
		if(obj)
		{
			obj.style.display="";
		}
	}
	return false;
}


function SetYear(op)
{
	var year = window.document.getElementById('year');
  if(op==-1 && year.selectedIndex==0)
     return;
  if(op==1 && year.selectedIndex==(year.options.length-1))
     return;

  year.selectedIndex=year.selectedIndex+op;
  
	var month = window.document.getElementById('month');

}

function SetMonth(op)
{
	var year = window.document.getElementById('year');
	var month = window.document.getElementById('month');
  if(op==-1 && month.selectedIndex==0)
  {
     if(year.selectedIndex>0)
     {
        month.selectedIndex=11;
        year.selectedIndex=year.selectedIndex-1;
     }
  }
  else if(op==1 && month.selectedIndex==11)
  {
     if(year.selectedIndex<200)
     {
        month.selectedIndex=month.selectedIndex=0;
        year.selectedIndex=hyear.selectedIndex+1;
     }
  }
  else
     month.selectedIndex=month.selectedIndex+op;
}

function GoMonthView(year, month)
{
	var group_obj = window.document.getElementById('logicalgroupid');
	var url = "index.php?controller=Schedule&action=month&year="+year+"&month="+month+"&logicalgroupid="+group_obj.value;
	window.location.href = url;
	ShowWaitBoxAfterSubmit();
}

