<?php

class MediaZone extends DataObject
{	
	const ZoneTypeVideo = "video";
	const ZoneTypeText = "text";
	const ZoneTypeHtml = "html";
	
	public static $ZoneAllowMediaType = array(
		ZoneTypeVideo => "vedio|audio",
		ZoneTypeText => "text|image",
		ZoneTypeHtml => "",
	);
	
	public static function GetTable()
	{
		return 'mediazone';
	}
	
	public static function GetPK()
	{
		return 'zoneid';
	}
	
	const ObjName = '节目区域';
	
	public function __cdonstruct($id_or_array)
	{
		parent::__construct($id_or_array);
	}
	
	public function DeleteItems()
	{
		return DBHelper::DeleteRecord(PlaylistItem::GetTable(), 'zoneid', $this->GetId());
	}
	
	public function Delete()
	{
		$this->DeleteItems();
		return parent::Delete();
	}
	
	public function GetItems()
	{
		return DBHelper::GetRecords(PlaylistItem::GetTable(), 'zoneid', $this->GetId(), 'playorder', 'asc');
	}
}