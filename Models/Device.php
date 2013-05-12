<?php
class Device extends DataObject
{	
	const DeviceRoot = 'root';
	const DeviceRepeater = 'repeater';
	const DeviceTerminal = 'terminal';
		
	public static $DeviceTypes = array(
		self::DeviceRepeater => '中继服务器',
		self::DeviceTerminal => '终端机');
	
	public static function GetTable()
	{
		return 'device';
	}
	
	public static function GetPK()
	{
		return 'deviceid';
	}
	
	public static function GetObjName()
	{
		return '设备';
	}
	
	public static function GetFailDevices()
	{
		$sql = "select * from ".Device::GetTable()." d 
			where devicetype='".Device::DeviceTerminal."' 
			and deviceisactive=1 
			order by parentid,deviceid";
		$rss = DBHelper::GetQueryRecords($sql);
		$errlist = array();
		foreach($rss as $rs)
		{
			$rs['st_message'] = Device::GetDeviceMessage($rs);
			if ($rs['st_message'])
			{
				$errlist[] = $rs;
			}
		}
		unset($rss);
		return $errlist;
	}
	
	public static function GetDeviceMessage(array $rs)
	{
		$time = time();
		if($rs['st_status']==0)
		{
			return "关机";
		}
		if($time-$rs['st_lastbeattime']>30000)
		{
			return "连接超时";
		}
		if($time-$rs['st_lastupdatetime']>86400)
		{
			return "长期未更新";
		}
		if($rs['st_reconntimes']>200)
		{
			return "重连次数过多";
		}
		if($rs['st_reboottimes']>10)
		{
			return "重启次数过多";
		}
		if($rs['st_diskfree']< 100*1024*1024)
		{
			return "磁盘空间不足100M";
		}
		return '';
	}
	
	/**
	 * return new instance of Device by mac.
	 * @param string $mac
	 * @return Device
	 */
	public static function DeviceFromMac($mac)
	{
		$rs = DBHelper::GetRecord(self::GetTable(), " where devicemac='$mac'");
		if($rs)
		{
			return new Device($rs);
		}
		else
		{
			return null;
		}
	}
	
	public static function ScreenImageUrl($devicemac, $isthumbnail=false)
	{
		if ($isthumbnail)
		{
			$path = AppHost::GetAppSetting()->ThumbnailDir."devices/".$devicemac."_small.jpg";
		}
		else
		{
			$path = AppHost::GetAppSetting()->ThumbnailDir."devices/".$devicemac.".jpg";
		}
		return $path;
	}
	
	public function AssignToGroup($logicalGroupId)
	{
		if($this->logicalgroupid==$logicalGroupId)
		{
			return true;
		}
		if($this->devicetype==Device::DeviceRepeater)
		{
			return true;
		}
		if ($logicalGroupId>0)
		{
			$logicalGroup = new LogicalGroup($logicalGroupId);
			if (!$logicalGroup->GetData())
			{
				throw new PCException('没有找到逻辑组'.$logicalGroup->GetId());
			}
		}
		$input['logicalgroupid'] = $logicalGroupId;
		if (!$this->Update($input)) 
		{
			throw new PCException('更新终端的逻辑组失败,逻辑组ID:'.$logicalGroupId);
		}
		return true;
	}
	
	public function AssignToRepeater($repeaterId)
	{
		if($this->parentid==$repeaterId)
		{
			return true;
		}
		if ($repeaterId>0)
		{
			$repeater = new Device($repeaterId);
			if (!$repeater->GetData())
			{
				throw new PCException('没有找到中继服务器:'.$repeaterId);
			}
		}
		$input['parentid'] = $repeaterId;
		if (!$this->Update($input)) 
		{
			throw new PCException('更新终端的中继服务器失败,中继服务器ID:'.$repeaterId);
		}
		return true;
	}

	public static function BuildFullList($rootid=0)
	{
//		$filter[] = DBHelper::FilterOption(Device::GetTable(),'deviceisactive',1,'==');
//		$devices = DBHelper::GetJoinRecordSet(Device::GetTable(), 
//			$join_options, $filters, 
//			$this->GetSortOption('devicetype,devicename','asc'),
//			$this->_offset, $this->_perpage, $totalcount);
		$devices = DBHelper::GetRecords(Device::GetTable(),'deviceisactive',1);
		$list = array();
		self::_getFullListByParent($rootid, $list, $devices);
		return $list;
	}
	
	private static function _getFullListByParent($parentid, &$retlist, &$devices)
	{
		$list = array();
		foreach ($devices as $rs)
		{
			if ($rs['parentid']==$parentid)
			{
				$retlist[] = $rs;
				self::_getFullListByParent($rs['deviceid'], $retlist, $devices);
			}
		}
	}
	
	public static function BuildFullTree($includeterminal=false)
	{
		$devices = DBHelper::GetRecords(Device::GetTable(),'deviceisactive',1);
		$tree = self::_getFullTreeByParent(0, $devices);
		return $tree;
	}
	
	private static function _getFullTreeByParent($parentid, &$devices)
	{
		$list = array();
		foreach ($devices as $rs)
		{
			if ($rs['parentid']==$parentid)
			{
				$rs['childs'] = self::_getFullTreeByParent($rs['deviceid'], $devices);
			}
			$list[] = $rs;
		}
		return $list;
	}
	
	public static function BuildMacTree($includeterminal=false)
	{
		$devices = DBHelper::GetRecords(Device::GetTable(),'deviceisactive',1);
		$content = self::_getMacTreeByParent(0, $devices);
		return $content;
	}
	
	private static function _getMacTreeByParent($parentid, &$devices)
	{
		$content = '';
		foreach ($devices as $rs)
		{
			if ($rs['parentid']==$parentid)
			{
				if($rs['devicetype']==Device::DeviceRepeater)
				{
					$content .= "mac=".$rs['devicemac']." ";
					$childs = self::_getMacTreeByParent($rs['deviceid'], $devices);
					if($childs)
					{
						$content .= "\n{\n$childs}";
					}
					$content .= ";\n";
				}
				else 
				{
					$content .= "mac=".$rs['devicemac'].";\n";
				}
			}
		}
		return $content;
	}
	
	private static function _getTreeByParent($parentid, &$devices)
	{
		$thistree = array();
		foreach ($devices as $rs)
		{
			if ($rs['parentid']==$parentid)
			{
				if($rs['devicetype']==Device::DeviceRepeater)
				{
					$rs['childs'] = self::_getListByParent($rs['deviceid'], $devices);
				}
				else 
				{
					$rs['childs'] = array();
				}
				$thistree[] = $rs;
			}
		}
		return $thistree;
	}
	
	public static function BuildRepeaterComboList($rootid=0)
	{
		$rss = DBHelper::GetRecords(Device::GetTable(),'devicetype',self::DeviceRepeater);
		$list = array();
		self::_getListByParent($rootid, $rss, $list, "|");
		//print_r($list);
		return $list;
	}
	
	private static function _getListByParent($parentid, &$rss, &$list, $prefix)
	{
		foreach ($rss as $rs)
		{
			if ($rs['parentid']==$parentid)
			{
				$rs['title'] = $prefix.'－'.$rs['devicename'];
				$list[] = $rs;
				self::_getListByParent($rs['deviceid'], $rss, $list, $prefix."　|");
			}
		}
	}
	
	public function GetParentList()
	{
		$lastnode = $this->GetData();
		$list = array();
		while(true)
		{
			if(!$lastnode['parentid'])
			{
				break;
			}
			$parent = new Device($lastnode['parentid']);
			$lastnode = $parent->GetData();
			if(!$lastnode)
			{
				break;
			}
			$list[] = $lastnode;
		}
		return $list;
	}
	
	public function GetParentPath($controller='Device',$action='list',$field='parentid')
	{
		$parentpath = HtmlHelper::Link('Root', 
				urlhelper('action',$controller,$action,'&parentid=0'));
		$parentlist = array_reverse($this->GetParentList());
		foreach ($parentlist as $re)
		{
			$parentpath .= " => ".HtmlHelper::Link($re['devicename'], 
				urlhelper('action',$controller,$action,"&$field=".$re['deviceid']));
		}
		$parentpath .= " => ".HtmlHelper::Link($this->devicename, 
			urlhelper('action',$controller,$action,"&$field=".$this->deviceid));
		return $parentpath;
	}
	
	public function GetStateFieldsDesc()
	{
		$rs =array();
		foreach ($this->_data as $key=>$val)
		{
			$showval = $val;
			$key = substr($key, 3);
			switch ($key)
			{
				case 'disktotal': 
					$rs[$key]=OpenDev::GetFileSizeDesc($val*1024); 
					break;
				case 'diskfree': 
					$rs[$key]=OpenDev::GetFileSizeDesc($val*1024); 
					break;
				case 'diskuse': 
					$rs[$key]=OpenDev::GetFileSizeDesc($val*1024); 
					break;
				case 'cpunice':  
				case 'cpuidle':  
					$rs[$key] = $val;
					break;				
				case 'memtotalmem': 
					$rs[$key]=OpenDev::GetFileSizeDesc($val*1024); 
					break;
				case 'membuffers': 
					$rs[$key]=OpenDev::GetFileSizeDesc($val*1024); 
					break;
				case 'memfreemem': 
					$rs[$key]=OpenDev::GetFileSizeDesc($val*1024); 
					break;
				case 'memcached': 
					$rs[$key]=OpenDev::GetFileSizeDesc($val*1024); 
					break;
				case 'memtotalswap': 
					$rs[$key]=OpenDev::GetFileSizeDesc($val*1024); 
					break;
				case 'memfreeswap': 
					$rs[$key]=OpenDev::GetFileSizeDesc($val*1024); 
					break;							
				case 'procpri': 
				case 'procnice': 
				case 'procsize': 
				case 'procrss': 
				case 'procstate':
				case 'proctime':
				case 'procpcpu':
				case 'procwcpu':
					$rs[$key] = $val;
					break;			
				default: 
					break;
			}
			$ret.= $ret ? ",$key=$showval" : "$key=$showval";
		}
		return $rs;
	}
	
	public static function CheckDeviceRight($devicers, array $authorized_ids)
	{
	    if(in_array('ALL', $authorized_ids))
	    {
	        return true;
	    }
	    if($devicers['devicetype']==Device::DeviceRepeater)
	    {
	        if(in_array($devicers['deviceid'], $authorized_ids))
	        {
	            return true;
	        }
	    }
	    elseif($devicers['devicetype']==Device::DeviceTerminal)
	    {
	        if(in_array($devicers['parentid'], $authorized_ids))
	        {
	            return true;
	        }
	    }
	    return false;
	}
}