<?php
class TFEntity extends DataObject
{
	private $_exFields = array();
	
	public static function GetTable()
	{
		return 'tf_entity';
	}
	
	public static function GetPK()
	{
		return 'entityid';
	}
	
	public static function GetObjName()
	{
		return "查询信息";
	}
	
	public function AddHitCount()
	{
		$sql = "update ".self::GetTable()." set hitcount = hitcount+1 
			where ".self::GetPK()."='".$this->GetId()."' limit 1";
		return DBHelper::ExecQuery($sql);
	}
	
	public function Delete()
	{
		$sql = "delete from ".TFFieldData::GetTable()." where entityid='".$this->GetId()."' ";
		if(DBHelper::ExecQuery($sql))
		{
			return parent::Delete();
		}
		else
		{
			return false;
		}
	}

	public function GetFieldList()
	{
		$sql = "select f.*,d.fielddataid,d.fieldvalue,c.categoryid from (".TFField::GetTable()." f 
			left join ".TFFieldData::GetTable()." d on d.fieldid=f.fieldid and entityid='".$this->GetId()."')
			left join ".TFCategory::GetTable()." c on f.formatid=c.formatid and c.categoryid='".$this->categoryid."'
			where c.categoryid='".$this->categoryid."'
			order by fieldorder";
		$rss = DBHelper::GetQueryRecords($sql);
		foreach ($rss as $rs)
		{
			$this->_exFields[$rs['fieldid']] = $rs;
		}
		unset($rss);
		return $this->_exFields;
	}
	
	public function SetExField($fieldid, $fieldvalue)
	{
		$this->_exFields[$fieldid]['fieldvalue'] = $fieldvalue;
	}
	
	public function SaveFieldList()
	{
		$result = true;
		foreach ($this->_exFields as $key=>$field)
		{
			if ($field['fielddataid']>0)
			{
				$data = new TFFieldData($field['fielddataid']);
			}
			else 
			{
				$data = new TFFieldData();
			}
			//echo "<BR>savefield: ";print_r($field)."<br>\n";
			$data->fieldid=$field['fieldid'];
			$data->entityid=$this->GetId();
			$data->fieldvalue = $field['fieldvalue'];
			$data->SaveData();
		}
		return $result;
	}
	
	public function SaveData()
	{
		if(parent::SaveData())
		{
			return $this->SaveFieldList();
		}
		return false;
	}
}
