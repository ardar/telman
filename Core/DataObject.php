<?php
abstract class DataObject
{
	protected $_data = null;
	protected $_id = null;
	protected $_dirtyFields = array();
		
	public function __construct($id_or_array=null)
	{
		if(!$this->GetTable())
		{
			throw New PCException("Not given _table for ".get_class($this));
		}
		if(!$this->GetPK())
		{
			throw New PCException("Not given _pk for ".get_class($this));
		}
		if (is_array($id_or_array))
		{
//			if (!isset($id_or_array[$this->GetPK()]) || !$id_or_array[$this->GetPK()])
//			{
//				throw new PCException('构造函数主键值不正确 :'. $this->GetPK());
//			}
			$this->_id = $id_or_array[$this->GetPK()];
			$this->_data = $id_or_array;
		}
		else
		{
			$this->_id = $id_or_array;
		}
	}
	
	public function GetId()
	{
		return $this->_id;
	}
	
	protected function ResetId($id)
	{
		if ($id!=$this->_id)
		{
			$this->_data = null;
		}
		$this->_id = $id;
	}
	
	public static function GetTable()
	{
		throw new PCException("Need Override GetTable() function.");
	}
	
	public static function GetPK()
	{
		throw new PCException("Need Override GetPrimaryKey() function.");
	}
	
	public function GetName()
	{
		throw new PCException("Need Override GetName() function.");
	}
	
	public function __get($property) 
	{
		if (strtolower($property)=="id" || strtolower($property)==strtolower($this->GetPK()))
		{
			return $this->GetId();
		}
        if (isset($this->_data[$property])) 
        {
            return $this->_data[$property];
        } 
        else 
        {
            return null;
        }
    }
    
    public function __set($property, $value) 
    {
    	if(strtolower($property)=="id" || strtolower($property)==strtolower($this->GetPK()))
    	{
    		throw new PCException("Cannot modify the id");
    	}
    	$this->_dirtyFields[$property] = 1;
        $this->_data[$property] = $value;
    }
    
    public function IsDirty()
    {
    	return count($this->_dirtyFields)>0 ? true : false;
    }
	
	public function GetData($refresh=false)
	{
		if (!$refresh && $this->GetId() && $this->_data)
		{
			return $this->_data;
		}
		if (!$this->GetId())
		{
			return null;
		}
		return $this->_data = DBHelper::GetSingleRecord($this->GetTable(), $this->GetPK(), $this->GetId());
	}
	
	public function SaveData()
	{
		$result = false;
		if ($this->GetId())
		{
			$rs = array();
			foreach ($this->_dirtyFields as $field=>$nouse)
			{
				if ($field!=$this->GetPK() && isset($this->_data[$field]))
				{
					$rs[$field] = $this->_data[$field];
				}
			}
			$result = DBHelper::UpdateRecord($this->GetTable(), $rs, $this->GetPK(), $this->GetId());
		}
		else
		{
			$rs = array();
			if($this->_data)
			{
				foreach ($this->_data as $field=>$value)
				{
					$rs[$field] = $value;
				}
				$result = DBHelper::InsertRecord($this->GetTable(), $rs);
			}
			if ($result)
			{
				$this->_id = $result;
				$this->_dirtyFields = array();
			}
		}
		return $result;
	}
	
	public function Insert($fields)
	{
		$result = DBHelper::InsertRecord($this->GetTable(), $fields);
		if( $result)
		{
			$this->_id = $result;
			foreach ($fields as $key=>$value)
			{
    		    $this->_data[$key]= $value;
			}
		}
		return $result;
	}
	
	public function Update($fields)
	{
		foreach ($fields as $field=>$value)
		{
			if ($field==$this->GetPK())
			{
				throw new PCException("不能修改主键 ".$this->GetPK()." 的值");
			}
		}
		$result = DBHelper::UpdateRecord($this->GetTable(), $fields, $this->GetPK(), $this->GetId());
		if($result)
		{
    		foreach ($fields as $key=>$value)
    		{
    		    $this->_data[$key]= $value;
    		}
		}
		return $result;
	}
	
	public function Delete()
	{
		if ($this->GetId() && DBHelper::DeleteRecord($this->GetTable(), $this->GetPK(), $this->GetId()))
		{
			$this->_data = null;
			return true;
		}
		$this->_data = null;
		return false;
	}
	
	public function __toString()
	{
		return serialize($this);
	}
}