<?php
class AppHost
{
	private static $_appInstance = null;
	
	/**
	 * Get global App instance.
	 * @param int $appId
	 * @return IApplication
	 * @throws PCException
	 */
	public static function GetApp()
	{
		if(self::$_appInstance==null)
		{
			//AppHost::$_appInstance = new Application();
			throw new PCException("App Not initialized.");
		}
		return self::$_appInstance;
	}
	
	public static function SetApp(IApplication $app)
	{
		self::$_appInstance = $app;
	}
	
	public static function GetAppSetting()
	{
		return self::GetApp()->GetSetting();
	}
	
	private function __construct()
	{		
	}
}