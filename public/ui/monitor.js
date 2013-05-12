function UpdateDeviceThumbnail(deviceid)
{
	var ret = LoadUrlContent('index.php?controller=DeviceMonitorJS&action=control&op=screen&deviceid='+deviceid+'&temp='+Math.random(), UpdateDeviceThumbnail_CallBack, deviceid);
	//alert("获取截图命令已发送");
	return ret;
}

function UpdateDeviceThumbnail_CallBack(deviceid, ret_content)
{
	if(ret_content!=null)
	{
		var code = ret_content.substring(0,2);
		if(code=="OK")
		{	
			var img = window.document.getElementById('device_image');
	
			img.src = "index.php?controller=DeviceMonitorJS&action=screenimage&deviceid="+deviceid+"&isthumbnail=1&temp="+Math.random();
			//img.reload();
			alert("截图已更新");
		}
		else
		{
			alert(ret_content);
		}
	}
	else
	{
		alert(ret_content);
	}
}

function UpdateDeviceState(deviceid)
{
	var ret = LoadUrlContent('index.php?controller=DeviceMonitorJS&action=control&op=state&deviceid='+deviceid+'&temp='+Math.random(), UpdateDeviceState_Callback, deviceid);
	//alert("获取截图命令已发送");
	return ret;
}

function UpdateDeviceState_Callback(deviceid, ret_content)
{
	if(ret_content!=null && ret_content.substring(0,2)=="OK")
	{
		var statestr = ret_content.substring(3,ret_content.length);
		var arr = statestr.split(',');
		for(var i=0;i<arr.length;i++)
		{
			var subarr = arr[i].split('=');
			if(subarr.length==2 && subarr[0]!=null)
			{
				var spanid = subarr[0];
				var val = subarr[1];
				var ctrl = window.document.getElementById("st_"+spanid);
				if(ctrl)
				{
					//alert(ctrl);
					ctrl.innerHTML = val;
				}
			}
		}
		alert("状态已更新");
	}
	else
	{
		alert(ret_content);
	}
}

function CloseScreen(deviceid, tostate)
{
	var url = 'index.php?controller=DeviceMonitorJS&action=control&op=close&deviceid='+deviceid
		+'&param='+tostate+'&temp='+Math.random();
	//alert(url);
	var ret = LoadUrlContent(url, CloseScreen_Callback, deviceid);
	//alert("获取截图命令已发送");
	return ret;
}

function CloseScreen_Callback(deviceid, ret_content)
{
	if(ret_content!=null && ret_content.substring(0,2)=="OK")
	{
		var statestr = ret_content.substring(3,ret_content.length);
		var state = parseInt(statestr);
		if(state==0)
		{
			alert("操作成功, 屏幕已关闭");
			window.document.getElementById('st_status').innerHTML = "运行";
			window.document.getElementById('st_screenstate').innerHTML = "关闭";
			//window.document.getElementById('btn_closescreen').style.display = "none";
			//window.document.getElementById('btn_openscreen').style.display = "";
			window.document.getElementById('btn_reboot').innerHTML = "重启";
		}
		else if(state==1)
		{
			alert("操作成功, 屏幕已开启");
			window.document.getElementById('st_status').innerHTML = "运行";
			window.document.getElementById('st_screenstate').innerHTML = "开启";
			//window.document.getElementById('btn_closescreen').style.display = "";
			//window.document.getElementById('btn_openscreen').style.display = "none";
		}
		else if(state==2)
		{
			alert("操作成功, 设备正在重启");
			window.document.getElementById('st_status').innerHTML = "运行";
			window.document.getElementById('st_screenstate').innerHTML = "开启";
			//window.document.getElementById('btn_closescreen').style.display = "";
			//window.document.getElementById('btn_openscreen').style.display = "none";
		}
		else if(state==3)
		{
			alert("操作成功, 设备已关机");
			window.document.getElementById('st_status').innerHTML = "关机";
			window.document.getElementById('st_screenstate').innerHTML = "关闭";
			//window.document.getElementById('btn_closescreen').style.display = "none";
			//window.document.getElementById('btn_openscreen').style.display = "";
		}
	}
	else
	{
		alert(ret_content);
	}
}


function UpdateSchedule(deviceid, isemergency)
{
	var ret = LoadUrlContent('index.php?controller=DeviceMonitorJS&action=control&op=update&deviceid='+deviceid+'&isemergency='+isemergency+'&temp='+Math.random(), UpdateSchedule_Callback, deviceid);
	//alert("获取截图命令已发送");
	return ret;
}

function UpdateSchedule_Callback(deviceid, ret_content)
{
	if(ret_content!=null && ret_content.substring(0,2)=="OK")
	{
		
		alert("更新通知已发送");
	}
	else
	{
		alert(ret_content);
	}
}