<?php
class PlayLog extends FWLog
{
	public static function GetTable()
	{
		return 'log_play';
	}
		
	public static function GetObjName()
	{
		return '播放日志';
	}
	
	const LogAction = "播放";
	
	public static function Log($deviceid, $mediaid, $playcount)
	{
		$log = new PlayLog(FWLog::LogPlay);
		return $log->SaveLog(self::LogAction, $playcount, $mediaid, $deviceid);
	}
}