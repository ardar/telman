<?php
class PlaylistItem extends DataObject
{	
	public static function GetTable()
	{
		return 'playlistitem';
	}
	
	public static function GetPK()
	{
		return 'playlistitemid';
	}
	
	const ObjName = '节目项';
}