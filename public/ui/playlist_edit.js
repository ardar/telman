// JavaScript Document

function InitLayout()
{
	var ratio = window.document.getElementById('screenratio');
	SetScreenRatio(ratio.value);
	var layout = window.document.getElementById('layout');
	LoadZones(layout.value);
}

function PickTreeFile(return_field_id, fileid)
{
	alert(fieldid);
	var parent_field = window.parent.document.getElementById(return_field_id);
	if(!parent_field)
	{
		alert("parent_field_no");
	}
	parent_field.value = fileid;
}

function SelectZoneBtn(zoneId, zoneType)
{
	var btnlist = window.document.getElementById('zonebtnlist');
	var selected_btn_id = 'zonebtn_'+zoneId;
	var zonefield = window.document.getElementById('sel_zoneid');
	zonefield.value = zoneId;
	for(var i=0;i<btnlist.childNodes.length;i++)
	{
		var btn = btnlist.childNodes[i];
		if(btn.tagName!='A')
		{
			continue;
		}
		if(btn.id==selected_btn_id)
		{
			btn.className='selzone_button';
		}
		else
		{
			btn.className = 'zone_button';
		}
	}
	var zone_container = window.document.getElementById('zone_container');
	//alert(zone_container.childNodes.length);
	var selected_frame_id = 'zone_div_'+zoneId;
	for(var i=0;i<zone_container.childNodes.length;i++)
	{
		var btn = zone_container.childNodes[i];
		if(btn.tagName!='DIV')
		{
			continue;
		}
		//alert(btn.id);
		if(btn.id==selected_frame_id)
		{
			btn.style.display='';
		}
		else
		{
			btn.style.display='none';
		}
	}
	//alert('finish');
	var currzoneType=window.document.getElementById('sel_zonetype').value;
	var mediatype = '';
	if(zoneType=='video')
	{
		mediatype='video|audio';
	}
	else if(zoneType=='text')
	{
		mediatype='text|image';
	}
	var newurl = "?controller=Folder&action=picktree&return_field=sel_mediaid&showfile=1&mediatype="+mediatype;

	if(currzoneType!=zoneType)
	{
		window.document.getElementById('sel_zonetype').value = zoneType;
		window.frames['main_left'].location.href = newurl;
	}
}

function AddMediaToZone(mediaid, zoneid)
{
	var framename = 'main_right_'+zoneid;
	var frame = window.frames[framename];
	if(frame)
	{
	frame.document.getElementById('mediaid').value=mediaid;
	frame.document.getElementById('zoneid').value=zoneid;
	frame.document.getElementById('addtozone_form').submit();
	}
}

function ShowPicDiv(img_src, x, y)
{
	window.document.body.innerHTML+=("<div style=\"position:absolute; z-index:100; top:"+x+"; left:"+y+"\"><img src='"+img_src+"'></div>");
}