// JavaScript Document

function CalendarDialog(ret_fieldId, originalvalue)
{
	var ret_val = showModalDialog("index.php?controller=UIUtility&action=calendar&ori_value="+originalvalue, 
		"选择日期", "dialogWidth:400px; dialogHeight:250px; status:0; help:0");
	if(ret_val)
	{
		var item = window.document.getElementById(ret_fieldId);
		if(item!=null)
		{
			item.value = ret_val;
		}
		return ret_val;
	}
	return null;
}

function SelColorDialog(fieldId)
{
	var val = showModalDialog("wbTextBox/layout_selcolor.htm", "", "dialogWidth:18.5em; dialogHeight:17.5em; status:0; help:0");
	if(val)
	{
		window.document.getElementById(fieldId).value = val;
		window.document.getElementById(fieldId).style.backgroundColor = val;
	}
}

function SelMediaDialog(fieldId, fieldname, fieldpath, oriMediaId, mediatype)
{
	var mediaparam = mediatype ? mediatype : '';
	var val = showModalDialog("index.php?controller=Media&action=selindex&mediatype="+mediaparam+"&return_field="+fieldId, "从媒体库中选择", "dialogWidth:800px; dialogHeight:600px; status:0; help:0");
	if(val)
	{
		var arr = val.split('*');
		var mediaid = arr[0];
		var medianame = arr[1];
		var mediapath = arr[2];
		var item = window.document.getElementById(fieldId);
		if(item!=null)
		{
			item.value = mediaid;
		}
		var item = window.document.getElementById(fieldname);
		if(item!=null)
		{
			item.value = medianame;
		}
		var item = window.document.getElementById(fieldpath);
		if(item!=null)
		{
			item.value = mediapath;
		}
		return val;
	}
	return '';
}

function unselectall(form, fieldname)
{
	var checked = false;
	var item = null;
	for (var i=0;i<form.elements.length;i++)
    {
		var e = form.elements[i];
		if (e.type=='checkbox' && e.name == 'chkAll_'+fieldname)
		{
		   checked = e.checked;
		   item = e;
		   break;
		}
    }
    if(item.checked){
			item.checked = false;
    } 	
}

function CheckAll(form, fieldname)
{
	var checked = false;
	for (var i=0;i<form.elements.length;i++)
    {
		var e = form.elements[i];
		if (e.type=='checkbox' && e.name == 'chkAll_'+fieldname)
		{
		   checked = e.checked;
		   break;
		}
    }
	
  	for (var i=0;i<form.elements.length;i++)
    {
		var e = form.elements[i];
		if (e.type=='checkbox' && e.name != 'chkAll' && e.name==fieldname)
		   e.checked = checked;
    }
}

function CheckAllBool(form, fieldname)
{
	var checked = false;
	for (var i=0;i<form.elements.length;i++)
    {
		var e = form.elements[i];
		if (e.type=='checkbox' && e.name == 'chkAll_'+fieldname)
		{
		   checked = e.checked;
		   break;
		}
    }
	
  	for (var i=0;i<form.elements.length;i++)
    {
		var e = form.elements[i];
		if (e.type=='checkbox' && e.name=='checkbool_'+fieldname)
		   e.checked = checked;
		if (e.type=='hidden' && e.name==fieldname)
		{
		   e.value = checked ? '1' : '0';
		}
    }
}

function GetFileNameFromPath(path)
{
	var startpos = path.lastIndexOf("\\");
	if(startpos>0)
	{
		return path.substring(startpos+1, path.length);
	}
	else
	{
		startpos = path.lastIndexOf("/");
		return path.substring(startpos+1, path.length);
	}
}

function LoadUrlContent(url, callback, callback_param) 　　
{ 
	var content = null;
	var xmlhttp = null;  
	try  
	{  
		xmlhttp = new ActiveXObject("MSXML2.XMLHTTP");
	}  
	catch(e)  
	{
		try  
		{  
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); 
		}  
		catch(e2)
		{
			alert(e2);
			return false;	
		}  
	}
	try
	{
		xmlhttp.open("GET", url);
		xmlhttp.onreadystatechange = function() 
		{
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
			{
				content = xmlhttp.responseText;
				//alert(content);
				callback(callback_param, content);
			}
		}
		xmlhttp.send(null); 
	}
	catch(e)
	{
		alert("XMLHttp open error "+e.name + ": " + e.message );
		return false;
	}
	return true;
}