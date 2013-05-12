// JavaScript Document

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

function FindZoneById(zoneId)
{
	for(var i=0;i<g_zonedata.length;i++)
	{
		var zone = g_zonedata[i];
		if(zone['id']==zoneId)
		{
			return zone;
		}
	}
	return null;
}

function GetPercentNumber(percentstr)
{
	arr = percentstr.split('%');
	return parseInt(arr[0], 10);
}

function SetZoneRatio(zoneId, ratiostr)
{
	var arr=ratiostr.split(":");
	var widthRatio = arr[0];
	var heightRatio = arr[1];
	var ratio = widthRatio/heightRatio;
	//var newwidth = (100*widthRatio/heightRatio)+'%';
	//alert(newwidth);
	var zone = FindZoneById(zoneId);
	if(!zone)
	{
		return;
	}
	var oriwidth = zone['width'];
	var oriheight =zone['height'];
	var oriratio = oriwidth/oriheight;
	
	var screenratioval = window.document.getElementById('screenratio').value;
	var arrscreen = screenratioval.split(':');
	var scrwidthratio = arrscreen[0];
	var scrheightratio = arrscreen[1];
	var scr_ratio = scrwidthratio/scrheightratio;
	
	var newwidth = (oriheight*ratio/scr_ratio);
	var newheight = oriheight;
	
	if(newwidth>100)
	{
		newwidth = oriwidth;
		newheight = oriwidth / ratio / scr_ratio;
	}
	
	
	SetZoneProperty(zoneId, "width", newwidth);
	SetZoneProperty(zoneId, "height", newheight);
	CorrectZonePos(zoneId);
}

function DisplayZoneDetail(zoneId)
{
	var table = window.document.getElementById('zone_detail');
	table.style.display = '';
	//alert(zoneId);
	var zone = FindZoneById(zoneId);
	if(!zone || zone['type'] == 'deleted')
	{
		return;
	}
	ShowZoneTypeTr(zone['type']);
	window.document.getElementById('zonename').value = zone['name'];
	window.document.getElementById('zoneid').value = zone['id']
	window.document.getElementById('zoneleft').value =  zone['left'];
	window.document.getElementById('zonetop').value =  zone['top'];
	window.document.getElementById('zonewidth').value =  zone['width'];
	window.document.getElementById('zoneheight').value =  zone['height'];
	window.document.getElementById('zonetype').value =  zone['type'];
	
	window.document.getElementById('zonebgcolor').value =  zone['bgcolor'];
	window.document.getElementById('zonebgcolor').style.backgroundColor =  zone['bgcolor'];
		
	window.document.getElementById('zonebgmediaid').value =  zone['bgmediaid'];
	window.document.getElementById('zonebgmedia').value =  zone['bgmedia'];
	window.document.getElementById('zonebgmediapath').value =  zone['bgmediapath'];
}

function CorrectZonePos(zoneId)
{
	return;
	//alert("correct zone pos "+zoneId);
	var item = FindZoneById(zoneId);
	if(!item)
	{
		return;
	}
	var width = GetPercentNumber(item.style.width);
	var height = GetPercentNumber(item.style.height);
	var left = GetPercentNumber(item.style.left);
	var top = GetPercentNumber(item.style.top);
	if(width>100)width=100;
	if(height>100)height=100;
	if(left<0 || left>=100)left=0;
	if(top<0 || top>=100)top=0;
	if( left + width >100)
	{
		width = 100 - left;
	}
	if(top + height >100)
	{
		height = 100 - top;
	}
	var cmd = item.id+'.style.width="'+width+'%"';
	//alert(cmd);
	eval(cmd);
	var cmd = item.id+'.style.height="'+height+'%"';
	//alert(cmd);
	eval(cmd);
	var cmd = item.id+'.style.left="'+left+'%"';
	//alert(cmd);
	eval(cmd);
	var cmd = item.id+'.style.top="'+top+'%"';
	//alert(cmd);
	eval(cmd);
	//alert("finish correct");
	DisplayZoneDetail(zoneId);
}

function SetScreenProperty(field, val)
{
	var scr = window.document.getElementById('container');
	if(!scr)
	{
		return;
	}
	if(field=='bgcolor')
	{
		scr.style.backgroundColor = val;
	}
	else if(field=='bgmedia')
	{
		var arr = val.split('*');
		scr.style.backgroundImage = "url("+arr[2]+")";
	}
}

function SetZoneProperty(zoneId, field, val)
{
	var zone = FindZoneById(zoneId);
	if(!zone)
	{
		return;
	}
	
	if(field=='bgcolor')
	{
		zone['bgcolor'] = val;
		FullScreen.SetBkColor(zoneId, val);
	}
	else if(field=='bgmedia')
	{
		var arr = val.split('*');
		zone['bgmediaid'] = arr[0];
		zone['bgmedia'] = arr[1];
		zone['bgmediapath'] = arr[2];
		FullScreen.SetBkImg(zoneId, arr[2]);
	}
	else if(field=='type')
	{
		zone['type'] = val;
		var iconurl = "images/"+val+".png";
		var option={icon:iconurl};
		FullScreen.SetOption(option);
	}
	else if(field=='name')
	{
		var div = FindDivByName(val);
		if(div!=null && div.id!=zoneId)
		{
			alert("已存在相同名字的区域, 请重新命名");
			return false;
		}
		zone['name'] = val;
		
		var li = window.document.getElementById('li_'+zoneId);
		if(li)
		{
			li.innerHTML="<a href=\"javascript:SelectZone('"+zoneId+"');\">"+val+"</a>";
		}
		
		var option={name:val};
		FullScreen.SetOption(option);
	}
	else if(field=='left')
	{
		var ret = FullScreen.SetLeft(zoneId, parseInt(val));
		if(ret)
		{
			zone[field] = parseInt(val);
		}
	} 
	else if(field=='top')
	{
		var ret = FullScreen.SetTop(zoneId, parseInt(val));
		if(ret)
		{
			zone[field] = parseInt(val);
		}
	} 
	else if(field=='width')
	{
		var ret = FullScreen.SetWidth(zoneId, parseInt(val));
		if(ret)
		{
			zone[field] = parseInt(val);
		}
	} 
	else if(field=='height')
	{
		var ret = FullScreen.SetHeight(zoneId, parseInt(val));
		if(ret)
		{
			zone[field] = parseInt(val);
		}
	} 
}


function ShowZoneTypeTr(zone_type)
{
	if(zone_type=='video')
	{
		window.document.getElementById('video_tr').style.display='';
		window.document.getElementById('text_tr').style.display='none';
		window.document.getElementById('html_tr').style.display='none';
	}
	else if(zone_type=='text')
	{
		window.document.getElementById('video_tr').style.display='none';
		window.document.getElementById('text_tr').style.display='';
		window.document.getElementById('html_tr').style.display='none';
	}
	else if(zone_type=='html')
	{
		window.document.getElementById('video_tr').style.display='none';
		window.document.getElementById('text_tr').style.display='none';
		window.document.getElementById('html_tr').style.display='';
	}
	else
	{
		window.document.getElementById('video_tr').style.display='none';
		window.document.getElementById('text_tr').style.display='none';
		window.document.getElementById('html_tr').style.display='none';
	}
}

function FindDivByName(name)
{
	for(var i=0;i<g_zonedata.length;i++)
	{
		var zone = g_zonedata[i];
		if(zone['name']==name)
		{
			return zone;
		}
	}
	return null;
}

function CreateElementId(prefix)
{
	var newid = null;
	var index = 1;
	while(true)
	{
		var idstr = prefix+index;
		if(!window.document.getElementById(idstr)
			&& !FindDivByName(idstr))
		{
			newid = idstr;
			break;
		}
		index ++;
		if(index>100)
		{
			return prefix+"null";
		}
	}
	return newid;
}

function AddNewZone()
{
	var zoneId = CreateElementId("newzone_");	
	var tpl = window.document.getElementById('container');
	
	var maxz = 0;
	var left = 5;
	var top = 5;
	for(var i=0;i<tpl.childNodes.length;i++)
	{
		var div = tpl.childNodes[i];
		if(div.tagName!='DIV')
		{
			continue;
		}
		maxz = div.style.zIndex>maxz ? div.style.zIndex : maxz;
		if(GetPercentNumber(div.style.left)==left)
		{
			if(left<=70) left += 5;
			else {left-=3; top-=3};
		}
		if(GetPercentNumber(div.style.top)==top)
		{
			if(left<=70) top += 5;
			else {left-=3; top-=3};
		}
	}
	var zindex = maxz+1;
	
	var width = height = 20;
	FullScreen.AddZone(zoneId,zoneId,"images/video.png",left,top,width,height,'','');
	
	var zone = new Array;
	zone['id'] = zoneId;
	zone['name'] = zoneId;
	zone['type'] = 'video';
	zone['left'] = left;
	zone['top'] = top;
	zone['width'] = width;
	zone['height'] = height;
	zone['bgcolor'] = '';
	zone['bgmediaid'] = '';
	zone['bgmedia'] = '';
	zone['bgmediapath'] = '';
	g_zonedata[g_zonedata.length] = zone;
	
	//tpl.innerHTML = tpl.innerHTML + '<div id="'+zoneId+'" class="video" style="z-index:'+zindex+';width:20%;height:20%;left: '+left+'%;top: '+top+'%;" onClick="SelectZone(this.id); return false;">'+zoneId+"</div>";
	
	var list = window.document.getElementById('zindexlist');
	list.innerHTML = list.innerHTML + "<li id='li_"+zoneId+"'><a href=\"javascript:SelectZone('"+zoneId+"');\">"+zoneId+"</a></li>";
	return zoneId;
}

function SelectZoneLi(zoneId)
{	
	var list = window.document.getElementById('zindexlist');
	for(var i=0;i<list.childNodes.length;i++)
	{
		var li = list.childNodes[i];
		if(li.tagName!='LI')
		{
			continue;
		}
		if(li.id=='li_'+zoneId)
		{
			li.className = 'selctedli';
		}
		else
		{
			li.className = '';
		}
	}
}

function SelectZone(zoneId)
{
	FullScreen.Select(zoneId);
}

function OnZoneSelected(zoneId)
{
	window.document.getElementById('zoneid').value=zoneId;
	SelectZoneLi(zoneId);
	DisplayZoneDetail(zoneId);
}



function LoadZones(initVal)
{
	g_zonedata=new Array;
	
	var tpl = window.document.getElementById('container');	
	var list = window.document.getElementById('zindexlist');
	
	tpl.innerHTML = '';
	list.innerHTML = '';
	
	var lines = initVal.split('*');
	for(var i=0;i<lines.length; i++)
	{
		if(lines[i].length==0)
		{
			continue;
		}
		var arr = lines[i].split(',');
		var zone = new Array;
		zone['id'] = arr[0];
		zone['name'] = arr[1];
		zone['left'] = arr[2];
		zone['top'] = arr[3];
		zone['width'] = arr[4];
		zone['height'] = arr[5];
		zone['zindex'] = arr[6];
		zone['type'] = arr[7];
		zone['bgcolor'] = arr[8];
		zone['bgmediaid'] = parseInt(arr[9]);
		zone['bgmedia'] = arr[10];
		zone['bgmediapath'] = arr[11];
		//alert(html);
		if(window.document.getElementById(zone['id'])!=null)
		{
			continue;
		}
		var bgmedia_str = '';
		var label = '';
		list.innerHTML = list.innerHTML + "<li id='li_"+zone['id']+"'><a href=\"javascript:SelectZone('"+zone['id']+"');\">"+zone['name']+'</a></li>';
		
		g_zonedata[g_zonedata.length] = zone;
		
		FullScreen.AddZone(zone['id'],zone['name'],"images/"+zone['type']+".png",zone['left'],zone['top'],zone['width'],zone['height'],zone['bgcolor'],zone['bgmediapath']);
		
	}
}

function CombineBgMediaStr(mediaid, medianame, mediapath)
{
	return mediaid+'*'+medianame+'*'+mediapath;
}

function DeleteSelZone(zoneId)
{
	//alert('delete '+zoneId);
	var li = window.document.getElementById('li_'+zoneId);
	if(!li)
	{
		alert("没有找到要删除的区域 "+zoneId);
		return;
	}
	li.style.display = "none";
	var table = window.document.getElementById('zone_detail');
	table.style.display = 'none';
	
	var zone = FindZoneById(zoneId);
	if(zone!=null)
	{
		zone.type = 'deleted';
	}
	
	FullScreen.Delete(zoneId);
}

function SetScreenRatio(ratiovalue)
{
	
	var arr = ratiovalue.split(':');
	var widthRatio = arr[0];
	var heightRatio = arr[1];
	var newwidthpx = (FullScreen.maxH*widthRatio/heightRatio);
	
	var option={width:newwidthpx};
	FullScreen.SetOption(option);
}

function LoadTemplateVals()
{
	var bgcolor = window.document.getElementById('bgcolor');
	var bgmediaid = window.document.getElementById('bgmediaid');
	var bgmedia = window.document.getElementById('bgmedia');
	var bgmediapath = window.document.getElementById('bgmediapath');	
	
	var ratio = window.document.getElementById('screenratio_ori');
	var ratioval = '4：3';
	if(ratio!=null)
	{
		ratioval = ratio.value;
	}
	var ratioselect = window.document.getElementById('screenratio');
	ratioselect.value=ratioval;
	
	var arr = ratioval.split(':');
	var widthRatio = arr[0];
	var heightRatio = arr[1];
	var newwidthpx = parseInt(FullScreen.maxH*widthRatio/heightRatio);
	var option={width:newwidthpx,height:FullScreen.maxH,bkimg:bgmediapath.value,bgcolor:bgcolor.value};
	FullScreen.SetOption(option);
}

function InitPage()
{
	LoadTemplateVals();
	var layout = window.document.getElementById('layout');
	LoadZones(layout.value);
}

function ParseZones()
{
	var str = '';
	for(var i=0;i<g_zonedata.length;i++)
	{
		var zone = g_zonedata[i];
		
		//alert(div.tagName);
		var id = zone['id'];
		var name = zone['name'];
		if(zone['type']=='deleted')
		{
			name = '/deleted/';
		}
		var left = zone['left'];
		var top = zone['top'];
		var width = zone['width'];
		var height = zone['height'];
		var zindex = i;
		var type = zone['type'];
		var bgcolor = zone['bgcolor'];
		
		var bgmediaid = zone['bgmediaid'];
		var bgmedia = zone['bgmedia'];
		var bgmediapath = zone['bgmediapath'];
		
		var thisstr = id+ ','+name+','+left+','+top+','
		+width+','+height+','+zindex+','+type+','
		+bgcolor+','+bgmediaid+','+bgmedia+','+bgmediapath;
		//alert(thisstr);
		str += (str=='') ? thisstr : ('*'+thisstr);
	}
	var layouthidden = window.document.getElementById( 'layout');
	layouthidden.value = str;
	//alert('layout:'+layouthidden.value);
}
var isclicksubmit=false;
var g_zonedata=new Array;