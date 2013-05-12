<?php
class SysCode extends DataObject
{
	const MediaType = "媒体类型";
	const DeployArea = '部署区域';
	const ScreenSize = '分辨率';
	const ScreenRatio = '宽高比';
	const TFCetegoryType = '查询信息格式';
	const TFFieldType = '查询字段类型';
	const TelManTelCom = '电话运营商';
	
	private	static $_groups = array(
		self::MediaType,
		self::DeployArea,
		self::ScreenSize,
		self::ScreenRatio,
		self::TFCetegoryType,
		self::TFFieldType,
		self::TelManTelCom,
		);
		
	public static function GetGroups()
	{
		return array_combine(self::$_groups, self::$_groups);
	}
	
	public static function GetTable()
	{
		return 'syscode';
	}
	
	public static function GetPK()
	{
		return 'syscodeid';
	}
	
	public static function GetObjName()
	{
		return '系统常量';
	}
	
	public static function GetCodeList($group)
	{
		$result = array();
		$rss = DBHelper::GetRecords(self::GetTable(), 'syscodegroup', $group, 'syscodeorder asc, syscode ','asc');
		foreach ($rss as $key=>$rs)
		{
			$result[$rs['syscode']] = $rs;
		}
		unset($rss);
		return $result;
	}
	
	public static function GetCodeData($group, $code)
	{
		$sql = "select * from ".self::GetTable()." where syscodegroup='$group' and syscode='$code'";
		return DBHelper::GetQueryRecord($sql);
	}
	
	public static function GetCodeName($group, $code)
	{
		$sql = "select * from ".self::GetTable()." where syscodegroup='$group' and syscode='$code'";
		$rs = DBHelper::GetQueryRecord($sql);
		return $rs['syscodename'];
	}
	
	public static function GetCodeParam($group, $code)
	{
		$sql = "select * from ".self::GetTable()." where syscodegroup='$group' and syscode='$code'";
		$rs = DBHelper::GetQueryRecord($sql);
		return $rs['syscodeparam'];
	}
	
	public static function CodeExist($group, $code)
	{
		$sql = "select count(*) as count from ".self::GetTable()." where syscodegroup='$group' and syscode='$code'";
		$rs = DBHelper::GetQueryRecord($sql);
		return $rs['count'];
	}
}