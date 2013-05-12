<?php
class TFFieldData extends DataObject
{
	public static function GetTable()
	{
		return 'tf_fielddata';
	}
	
	public static function GetPK()
	{
		return 'fielddataid';
	}
	
	public static function GetObjName()
	{
		return "字段内容";
	}
		
}