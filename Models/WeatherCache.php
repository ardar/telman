<?php
class WeatherCache extends DataObject
{	
	public static function GetTable()
	{
		return 'weathercache';
	}
	
	public static function GetPK()
	{
		return 'cityid';
	}
	
	public static function GetObjName()
	{
		return '天气缓存';
	}
    
    public function __set($property, $value) 
    {
    	$this->_dirtyFields[$property] = 1;
        $this->_data[$property] = $value;
    }
    
    public function SaveData()
    {
    	$rs = DBHelper::GetSingleRecord($this->GetTable(), $this->GetPK(), $this->GetId());
    	if (!$rs)
    	{
    		$this->_id = null;
    	}
    	if(!$this->_data['cityid'])
    	{
    		return false;
    	}
    	return parent::SaveData();
    }
}