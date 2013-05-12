<?php
define('APP_URL','/');

class AppSetting
{
	public $AppName = "Management Framework";
	public $Version = "1.0";
	public $PowerBy = "Perconsoft";
	public $UsingUrlRewrite = false;
	public $ExtensionList = "";
	public $DefaultTimezone = "Asia/Chongqing";
	
	public $CookiePrefix = "__FWCOMMON_";
	
	public $UploadDir = "uploadfiles/";
	public $ThumbnailDir = "uploadfiles/thumbnails/";
	public $CacheDir = "caches/";
	public $LogDir = "logs/";
	
	public $CacheEngine = "MemcacheEngine";
	
	public $ViewEngine = "SmartyEngine";
	
	public $DbEngine = "PDODatabase";
	public $DbMS = "mysql";
	public $DbHost = "localhost";
	public $DbUser = "bikeface";
	public $DbPass = "ardar";
	public $DbName = "fw_telman";
	public $DbTablePrefix = "";
	
	public $WebSiteId = 1;
	
	public $MonitorServerIP = "192.168.1.200";
	public $MonitorServerPort = 999;
	public $TouchWebServerAddr = "http://192.168.1.200/touch/";
	
}