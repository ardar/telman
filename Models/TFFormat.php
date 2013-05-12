<?php
class TFFormat extends DataObject
{
	private $_fields = array();
	public static function GetTable()
	{
		return 'tf_format';
	}
	
	public static function GetPK()
	{
		return 'formatid';
	}
	
	public static function GetObjName()
	{
		return "信息格式";
	}
	
	public function GetFieldList()
	{
		$this->_fields = DBHelper::GetRecords(TFField::GetTable(), 'formatid',$this->GetId(),'fieldorder','asc');
		return $this->_fields;
	}
	
	public function Delete()
	{
		$this->GetFieldList();
		foreach ($this->_fields as $field)
		{
			$fieldobj = new TFField($field);
			$fieldobj->Delete();
		}
		return parent::Delete();
	}
}