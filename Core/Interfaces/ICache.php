<?php
interface ICache
{
	public function Init($cachedir, $expire_timestamp=null);
	
	public function IsCache($cachekey);
	
	public function Query($cachekey, $expire_timestamp);
	
	public function Save($cachekey, $content, $expire_timestamp);
	
	public function Delete($cachekey);
	
	public function DeleteAll();
	
	public function DeleteExpired($expire_timestamp);
}