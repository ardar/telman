<?php
class PlayerConfig extends DataObject
{	
	const TargetGlobal = 'global';
	const TargetGroup = 'logicalgroup';
	const TargetDevice = 'device';
	
	public static $Targets = array(
		self::TargetGroup => '逻辑分组',
		self::TargetDevice => '设备');
	
	public static function GetTable()
	{
		return 'playerconfig';
	}
	
	public static function GetPK()
	{
		return 'configid';
	}
	
	public static function GetObjName()
	{
		return '播放器设置';
	}
		
	public static function GetConfig($groupid, $deviceid)
	{
		$config = new PlayerConfig(self::TargetDevice, $deviceid);
		if($deviceid>0 && $config->GetData())
		{
			return $config;
		}
		$config = new PlayerConfig(self::TargetGroup, $groupid);
		if($groupid>0 && $config->GetData())
		{
			return $config;
		}
		$config = new PlayerConfig(self::TargetGlobal, 0);
		if($config->GetData())
		{
			return $config;
		}
		return null;
	}
	
	public function __construct($target,$targetid)
	{
		$sql = "select * from ".self::GetTable()." 
			where target='".$target."' and targetid='".$targetid."' 
			limit 1";
		$rs = DBHelper::GetQueryRecord($sql);
		if ($rs)
		{
			return parent::__construct($rs);
		}
		else
		{
			$this->target = $target;
			$this->targetid = $targetid;
		}
	}
	
	public static function GetXmlPath($devicemac)
	{
		return "outputs/configs/".$devicemac.".xml";
	}
	
	public function SaveXml()
	{
		$devices = array();
		$device = null;
		if ($this->target==self::TargetDevice)
		{
			$device = new Device($this->targetid);
			if ($device->GetData())
			{
				$devices[0] = $device->GetData();
			}
			else
			{
				return false;
			}
		}
		elseif ($this->target==self::TargetGroup)
		{
			$sql = "select d.* from device d 
				left join playerconfig c on (c.target='device' and d.deviceid=c.targetid)
				where c.configid is null 
				and d.logicalgroupid='".$this->targetid."'";
			$devices = DBHelper::GetQueryRecords($sql);				
		}
		elseif ($this->target==self::TargetGlobal)
		{
			$sql = "select d.* from (device d 
				left join playerconfig c on (c.target='device' and d.deviceid=c.targetid))
				left join playerconfig c2 on (c2.target='logicalgroup' and d.logicalgroupid=c2.targetid)
				where c.configid is null and c2.configid is null";
			$devices = DBHelper::GetQueryRecords($sql);
		}
		else 
		{
			return false;
		}
		$rs = $this->GetData();
		if(!$rs)
		{
			if ($this->target==self::TargetDevice)
			{
				$config = PlayerConfig::GetConfig($device->logicalgroupid, $device->GetId());
			}
			elseif ($this->target==self::TargetGroup)
			{
				$config = PlayerConfig::GetConfig($this->targetid,0);
			}
			else
			{
				$config = PlayerConfig::GetConfig(0,0);
			}
			$rs = $config ->GetData();
		}
		if ($rs)
		{
			AppHost::GetApp()->GetView()->Assign('rs',$rs);
			$content = AppHost::GetApp()->GetView()->GetOutput('WebService/player_config.xml');
		}
		else 
		{
			return false;
		}
		foreach ($devices as $device)
		{
			$filename = self::GetXmlPath($device['devicemac']);
			//echo $filename;
			OpenDev::writetofile($filename, $content);
		}
	}
}