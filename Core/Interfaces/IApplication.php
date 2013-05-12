<?php
interface IApplication
{
	public function Init(AppSetting $setting);
	
	public function Run();

	/**
	 * Gets the AppSetting instance.
	 * @return AppSetting
	 */
	public function GetSetting();
	/**
	 * Gets the IRequest instance.
	 * @return IRequest
	 */
	public function GetRequest();
	/**
	 * Gets the IView instance
	 * @return IView
	 */
	public function GetView();
	
	/**
	 * Gets the ICache instance.
	 * @return ICache
	 */
	public function GetCache();
	
	/**
	 * Gets the ILogger instance for sys logging.
	 * @return ILogger
	 */
	public function GetLogger();
	
	/**
	 * Gets the IDatabase instance for sys logging.
	 * @return IDatabase
	 */
	public function GetDB();
	
	/**
	 * Gets the IExtension instance of regestered enxtension.
	 * @param string $ext_name
	 * @return IExtension
	 */
	public function GetExt($ext_name);
	
	/**
	 * Gets the extensions' menu tree.
	 */
	public function GetExtMenus();
	
	/**
	 * Get enviroment value.
	 * @param string $field
	 * @return string
	 */
	public function GetEnv($field);
	/**
	 * Gets the WebSite instance.
	 * @return WebSite
	 */
	public function GetWebSite();
}