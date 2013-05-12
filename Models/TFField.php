<?php
class TFField extends DataObject
{
	public static function GetTable()
	{
		return 'tf_field';
	}
	
	public static function GetPK()
	{
		return 'fieldid';
	}
	
	public static function GetObjName()
	{
		return "格式字段";
	}
	
	public function Delete()
	{
		$sql = "delete from ".TFFieldData::GetTable()." where fieldid='".$this->GetId()."' ";
		if(DBHelper::ExecQuery($sql))
		{
			return parent::Delete();
		}
		else
		{
			return false;
		}
	}
}