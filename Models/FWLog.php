<?php
class FWLog
{
	const LogDevice = "device";
	const LogMedia = "media";
	const LogPublish = "publish";
	const LogSecurity = "security";
	
	public static $LogTypes = array(
		self::LogDevice => '设备日志',
		self::LogMedia => '媒体日志',
		self::LogPublish => '发布日志',
		self::LogSecurity => '安全日志',
		);
		
	const ActionAdd = "创建";
	const ActionUpdate = "修改";
	const ActionDelete = "删除";
	
	const ActionLogin = "登录";
	const ActionLogout = "登出";
	const ActionChgPW = "修改密码";
	
	const ActionPlay = "播放";
	const ActionAudit = "通过审核";
	const ActionDisAudit = "取消审核";
	const ActionMove = "移动";
	
	const ActionAssign = "分配";
	const ActionReboot = "重启";
	const ActionState = "更新状态";
	const ActionScreen = "截屏";
	const ActionCloseScreen = "关闭屏幕";
	const ActionOpenScreen = "开启屏幕";
	const ActionNotifyUpdate = "通知更新";
	const ActionHeartBeat = "心跳连接";
	const ActionInvalidNode = "未知设备登记";
	
	const ActionPublish = "通过发布";
	const ActionCancelPublish = "取消发布";
	
	public static function GetPK()
	{
		return 'logid';
	}
		
	public static function GetLogTable($logtype)
	{
		if(!key_exists($logtype, self::$LogTypes))
		{
			throw new PCException("未定义日志类型: $logtype");
		}
		return "log_".$logtype;		
	}
	private $_logtable;
	private $_logtype;
	private $_logid;
	
	public $_logop;
	public $_logparam;
	public $_logtext;
	public $_targetid;
	public $_sourceid;
	public $_loguserid;
	public $_logtime;
	
	public function __construct($logtype)
	{
		$this->_logtype = $logtype;
		$this->_logtable = self::GetLogTable($logtype);
	}
	
	public function GetLog($id)
	{
		$rs= DBHelper::GetSingleRecord($this->_logtable, self::GetPK(), $id);
		
		$this->_logid = $rs['logid'];
		$this->_logop = $rs['logop'];
		$this->_logparam = $rs['logparam'];
		$this->_target = $rs['target'];
		$this->_source = $rs['source'];
		$this->_targetid = $rs['targetid'];
		$this->_sourceid = $rs['sourceid'];
		$this->_loguserid = $rs['loguserid'];
		$this->_logtime = $rs['logtime'];
		$this->_logtext = $rs['logtext'];
		return $rs;
	}
	
	public function SaveLog($logop, $logparam, $source, $sourceid, $target, $targetid, $loguserid, $logtext=null)
	{
		$this->_logop = $logop;
		$this->_logparam = $logparam;
		$this->_targetid = $targetid;
		$this->_sourceid = $sourceid;
		$this->_loguserid = $loguserid;
		$this->_logtime = time();
		$this->_logtext = serialize($logtext);
				
		$rs = array();
		$rs['logtype'] = $this->_logtype;
		$rs['logop'] = $logop;
		$rs['logparam'] = $logparam;
		$rs['target'] =  ($target);
		$rs['source'] =  ($source);
		$rs['targetid'] =  intval($targetid);
		$rs['sourceid'] =  intval($sourceid);
		$rs['loguserid'] =  intval($loguserid);
		$rs['logtime'] =  time();
		if($logtext)
		{
			$rs['logtext'] = serialize($logtext);
		}
		$result = DBHelper::InsertRecord($this->_logtable, $rs);
		
		if ($result)
		{
			$this->_logid = $result;
		}
		return $result;
	}

	public static function PlayLog($playcount, $devicers, $mediars)
	{
		$log = new FWLog(FWLog::LogMedia);
		return $log->SaveLog(FWLog::ActionPlay, $playcount, $mediars['medianame'], $mediars['mediaid'], 
			$devicers['devicename'], $devicers['deviceid'], Account::SYSTEM_USERID);
	}

	public static function MediaAuditLog($isaudit, $mediars, $opuserid)
	{
		try 
		{
			$log = new FWLog(FWLog::LogMedia);
			return $log->SaveLog($isaudit?FWLog::ActionAudit:FWLog::ActionDisAudit, 
				'', $mediars['medianame'], $mediars['mediaid'], '', 0, $opuserid);
		}
		catch (PCException $e)
		{
			return false;
		}
	}
	
	public static function MediaLog($logop, $logopparam, $mediars, $target,$targetid, $opuserid, $detail=null)
	{
		try 
		{
			$log = new FWLog(FWLog::LogMedia);
			return $log->SaveLog($logop, $logopparam, $mediars['medianame'], $mediars['mediaid'], 
				$target,$targetid, $opuserid, $detail);
		}
		catch (PCException $e)
		{
			return false;
		}
	}

	public static function DeviceLog($logop, $issuccess, $devicers, $opuserid,$target=null, $targetid=0)
	{
		try 
		{
			$log = new FWLog(FWLog::LogDevice);
			return $log->SaveLog($logop, $issuccess?"成功":"失败", 
				$devicers['devicename'], $devicers['deviceid'], $target, $targetid, $opuserid, $devicers);
		}
		catch (PCException $e)
		{
			return false;
		}
	}

	public static function SecurityLog($logop, $issuccess, $userrs, $opuserrs=null)
	{
		try 
		{
			if($opuserrs==null)
			{
				$opuserrs = $userrs;
			}
			$log = new FWLog(FWLog::LogSecurity);
			unset($userrs['password']);
			return $log->SaveLog($logop, $issuccess?"成功":"失败", $userrs['username'], $userrs['userid'], 
				'', 0, $opuserrs['userid'],$userrs);
		}
		catch (PCException $e)
		{
			return false;
		}
	}

	public static function PublishLog($logop, $logicalgrouprs, $publish_dateblock, $publishcontent, $opuserid)
	{
		try 
		{
			$log = new FWLog(FWLog::LogPublish);
			return $log->SaveLog($logop, $publish_dateblock, 
				$logicalgrouprs['logicalgroupname'], $logicalgrouprs['logicalgroupid'], 
				'', 0, $opuserid, $publishcontent);
		}
		catch (PCException $e)
		{
			return false;
		}
	}
}