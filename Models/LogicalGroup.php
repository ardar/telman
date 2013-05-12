<?php
class LogicalGroup extends DataObject
{	
	public static function GetTable()
	{
		return 'logicalgroup';
	}
	
	public static function GetPK()
	{
		return 'logicalgroupid';
	}
	
	public static function GetObjName()
	{
		return '逻辑分组';
	}
	
	public function GetDevices()
	{
		return DBHelper::GetRecords(Device::GetTable(), 'logicalgroupid', $this->GetId(), 'deviceid');
	}
}