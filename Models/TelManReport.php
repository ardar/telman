<?php
class TelManReport extends DataObject
{
	public static function GetTable()
	{
		return 'telman_report';
	}
	
	public static function GetPK()
	{
		return 'reportid';
	}
	
	public static function GetObjName()
	{
		return '上报名单';
	}
	
	public function Delete()
	{
	    $this->GetData();
	    if ($this->isreported)
	    {
	        throw new PCException('名单已上报, 无法删除');
	    }    
	    if ($this->isresulted)
	    {
	        throw new PCException('名单已返回结果, 无法删除');
	    }
	    $sql = "update ".TelManBaby::GetTable()." set reportid=0,babyisactive=0 where reportid='".$this->GetId()."'";
		DBHelper::ExecQuery($sql);	    
	    //DBHelper::DeleteRecord(TelManBaby::GetTable(), 'reportid', $this->GetId());
	    return parent::Delete();
	}
	
	public function AdminDelete()
	{
	    $this->GetData();
	    if ($this->isresulted)
	    {
	        throw new PCException('名单已返回结果, 无法删除');
	    }
	    $sql = "update ".TelManBaby::GetTable()." set reportid=0,babyisactive=0 where reportid='".$this->GetId()."'";
		DBHelper::ExecQuery($sql);
		return parent::Delete();
	}
}