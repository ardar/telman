<?php
class TelManBrand extends DataObject
{
	public static function GetTable()
	{
		return 'telman_brand';
	}
	
	public static function GetPK()
	{
		return 'brandid';
	}
	
	public static function GetObjName()
	{
		return '奶粉品牌';
	}
	
	public static function GetActiveBrands()
	{
	    return DBHelper::GetRecords(self::GetTable(), 'brandisactive',1,'brandorder','asc');
	}
}