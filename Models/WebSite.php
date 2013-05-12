<?php
class WebSite extends DataObject
{	
	const ObjName = '站点设置';
	
	public static function GetObjName()
	{
		return self::ObjName;
	}
	public static function GetTable()
	{
		return 'website';
	}
	
	public static function GetPK()
	{
		return 'websiteid';
	}
	
}