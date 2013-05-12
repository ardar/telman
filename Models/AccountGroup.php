<?php
class AccountGroup extends DataObject
{	
	private $_moduleRights = array();

	public function __construct($id)
	{
		parent::__construct($id);
	}
	
	public static function GetTable()
	{
		return 'accountgroup';
	}
	
	public static function GetPK()
	{
		return 'groupid';
	}
	
	public static function GetObjName()
	{
		return '用户组';
	}
	
	public function GetData($refresh=false)
	{
		parent::GetData($refresh);
		
		$this->_moduleRights = explode('|', $this->moduleright);
		
		return $this->_data;
	}
	
	public function GetModuleRights()
	{
		return $this->_moduleRights;
	}
}