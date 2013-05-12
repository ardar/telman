<?php
class OPLog extends FWLog
{
	public static function GetTable()
	{
		return 'log_op';
	}
		
	public static function GetObjName()
	{
		return '设备操作日志';
	}
	
	const ActionReboot = "重启";
	const ActionState = "更新状态";
	const ActionScreen = "截屏";
	const ActionCloseScreen = "关闭屏幕";
	const ActionOpenScreen = "开启屏幕";
	
	public static function Log($action, $issuccess, $deviceid, $opuserid)
	{
		$log = new OPLog(FWLog::LogOP);
		return $log->SaveLog($action, $issuccess?"成功":"失败", $deviceid, $opuserid);
	}
}