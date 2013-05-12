<?php
class DeployArea extends DataObject
{
	public static function GetTable()
	{
		return 'deployarea';
	}
	
	public static function GetPK()
	{
		return 'deployareaid';
	}
}