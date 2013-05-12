<?php
class DeviceMonitorJSController extends ControllerBase
{
	const ModuleRight='device_monitor';
	const ModuleRightOP='device_operate';
	const ObjName = '设备状态';
	
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		
		$this->CheckPermission(self::ModuleRight);
	}
	
	public function Message($message, $nextlocation=LOCATION_BACK, $isForceDown=ForceDown)
	{
		echo $message;
		if ($isForceDown)
		{
			exit();
		}
	}
	
	public function screenimageAction()
	{
		$deviceid = $this->GetRequest()->GetInput('deviceid','int');
		$isthumbnail = $this->GetRequest()->GetInput('isthumbnail','int');
		$device = new Device($deviceid);
		if (!$device->GetData())
		{
			$this->Message('没有找到设备 '.$deviceid);
		}
		ob_clean();
		$imagepath = Device::ScreenImageUrl($device->devicemac, $isthumbnail);
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-type: image/jpeg");
		if(file_exists($imagepath))
		{
			echo OpenDev::readfromfile($imagepath);
		}
		$this->GetApp()->AppExit();
	}
		
	public function controlAction()
	{
		$deviceid = $this->GetRequest()->GetInput('deviceid','int');
		$op = $this->GetRequest()->GetInput('op','string');
		$param = $this->GetRequest()->GetInput('param','string');
		$device = new Device($deviceid);
		if (!$device->GetData() && $op!='update')
		{
			$this->Message('没有找到设备');
		}
	
	    if(!Device::CheckDeviceRight($device->GetData(), $this->GetAccount()->GetAuthorizedDeviceIds()))
	    {
	        $this->Message('没有权限操作该设备');
	    }
		    
		$client = new MonitorClient($device->devicemac);
		$client->Connect($this->GetApp()->GetSetting()->MonitorServerIP, 
			$this->GetApp()->GetSetting()->MonitorServerPort);
		$client->Init();
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		switch ($op)
		{
			case 'screen': 
				$ret = $client->RequestScreen(); 
				if ($ret===true)
				{
					FWLog::DeviceLog(OPLog::ActionScreen, true, $device->GetData(), $this->GetAccount()->GetId());
					$this->Message('OK');
				}
				else
				{
					FWLog::DeviceLog(OPLog::ActionScreen, false, $device->GetData(), $this->GetAccount()->GetId());
					$this->Message($ret);
				}
				break;
			case 'state': 
				$this->CheckPermission(self::ModuleRightOP);
				$ret = $client->RequestState();
				if ($ret===true)
				{
					$rs = $device->GetStateFieldsDesc();
				
					foreach ($rs as $key=>$val)
					{
						$showval = $val;
						$ret.= $ret ? ",$key=$showval" : "$key=$showval";
					}
					FWLog::DeviceLog(OPLog::ActionState, true, $device->GetData(), $this->GetAccount()->GetId());
					$this->Message("OK $ret");
				}
				else 
				{
					FWLog::DeviceLog(OPLog::ActionState, false, $device->GetData(), $this->GetAccount()->GetId());
					
					$this->Message($ret);
				}
				break;
			case 'close': 
				$this->CheckPermission(self::ModuleRightOP);
				$ret = $client->RequestClose(intval($param)); 
				FWLog::DeviceLog(OPLog::ActionCloseScreen, $ret, $device->GetData(), $this->GetAccount()->GetId());
					
				$this->Message("OK $ret");
				break;
			case 'update': 
				$this->CheckPermission(self::ModuleRightOP);
				$isemergency = $this->GetRequest()->GetInput('isemergency','int');
				$client->RequestUpdate($isemergency); 
				FWLog::DeviceLog(OPLog::ActionNotifyUpdate, true, $device->GetData(), $this->GetAccount()->GetId());
					
				$this->Message('更新通知已发送 ');
				break;
			default: 
				$this->Message("未定义控制操作" .$op);
				break;
		}
		$this->GetApp()->AppExit();
	}
	
	public function querystateAction()
	{
		$rss = Device::GetFailDevices();
		foreach ($rss as $key=>$rs)
		{
		    if(!Device::CheckDeviceRight($rs, $this->GetAccount()->GetAuthorizedDeviceIds()))
		    {
		        unset($rss[$key]);
    	        continue;
		    }
		}
		if(count($rss)>0)
		{
			//echo '<A href="index.php?controller=DeviceMonitor&action=faillist" target=frame_main
   // onclick="Reset()"><img src="images/warning.png"> 当前有'.count($rss).'个设备异常, 请点击查看</a>';
		}
		$this->GetApp()->AppExit();
	}
	
}