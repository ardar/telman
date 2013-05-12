<?php

class Application implements IApplication
{
	protected $_version = "1.0";
	
	/**
	 * @var AppSetting
	 */
	protected $_settings = null;
	protected $_envs = array();
	protected $_exts = array();
	
	/**
	 * The IRequest instance
	 * @var IRequest
	 */
	protected $_request = null;
	/**
	 * the IView instance, view or template engine.
	 * @var IView
	 */
	protected $_view = null;
	
	/**
	 * the cache management object instance.
	 * @var ICache
	 */
	protected $_cache = null;
	/**
	 * the logger
	 * @var ILogger
	 */
	protected $_logger = null;
	/**
	 * the database instance.
	 * @var IDatabase
	 */
	protected $_db = null;
	
	/**
	 * the controller instance.
	 * @var ControllerBase
	 */
	protected $_controller = null;
	
	/**
	 * the website instance.
	 * @var WebSite
	 */
	protected $_website = null;
	
	public function __construct()
    {
    	if (DEBUG)
    	{
			$mtime = explode(' ', microtime());
			$this->_env['timestart'] = $mtime[1] + $mtime[0];
    	}
		
    	AppHost::SetApp($this);
    }
    
    public function __destruct()
    {
    	
    }
    
    public static function AutoLoader($classname)
    {
    	if(file_exists(FW_DIR."Core/$classname.php"))
    	{
    		require (FW_DIR."Core/$classname.php");
    	}
    	elseif(file_exists(FW_DIR."Controllers/$classname.php"))
    	{
    		require (FW_DIR."Controllers/$classname.php");
    	}
    	elseif(file_exists(FW_DIR."Models/$classname.php"))
    	{
    		require (FW_DIR."Models/$classname.php");
    	}
    	elseif(file_exists(FW_DIR."Exts/$classname.php"))
    	{
    		require (FW_DIR."Exts/$classname.php");
    	}
    }
    
	public function Init(AppSetting $setting)
	{
		global $_COOKIE,$_SESSION,$_SERVER,$_FILES,$_GET,$_POST;
		
    	$this->_settings = $setting;
    	
		date_default_timezone_set($setting->DefaultTimezone);
		
		$this->_envs['referer'] = $_SERVER['HTTP_REFERER'];
		$this->_envs['timestamp'] = time();
		$this->_envs['clientip'] = OpenDev::getRemoteIP();
		
		try 
		{
			$view_setting = array(
				'template_dir' => array(FW_DIR.'Views/locoroco/',FW_DIR.'Views/'),
				'compile_dir' => FW_DIR.'cache/compiles/',
				'cache_dir' => FW_DIR.'cache/',
				'debug_on' => 2,
			);
			// Init the view engine
			$this->_view = new SmartyView();
			$this->_view -> Init($view_setting);
			
			set_error_handler(array($this, 'ExceptionErrorHandler'));
			
			// Init the database
			$this->_db = new PDODatabase($setting->DbMS, 
				$setting->DbHost, $setting->DbName, $setting->DbUser, $setting->DbPass);
			$this->_db -> Init(array(PDO::ATTR_PERSISTENT => true));
			
			$this->_request = new HttpRequest();
			
			// Load WebSite config
			$this->_website = new WebSite($this->GetSetting()->WebSiteId);
			if(!$this->_website->GetData())
			{
				throw new PCException("站点设置不存在 " . $this->GetSetting()->WebSiteId);
			}
			
			// Load extensions
			$moduleLoadOptions = array();
			foreach (ModuleConfig::$LoadModules as $modulename)
			{
			    include_once (FW_DIR."Modules/".$modulename.".php");
			    $this->_exts[$modulename] = new $modulename();
			}
			
			// Init extensions
			foreach($this->_exts as $extname => $ext)
			{
				if (!($ext instanceof IExtension))
				{
					throw new PCException("The extension is not a IExtension");
				}
				$ext->Init($moduleLoadOptions);
			}
		} 
		catch (AuthException $e)
		{
			$this->OnMessage($e->GetMessage(), $e, $e->GetNextLocation());
		}
		catch (PCException $e) 
		{
			$this->OnMessage($e->getMessage(), $e);
		}
		catch(SmartyException $e)
		{
			$this->OnError('模板错误', $e);
		}
		catch(PDOException $e)
		{
			$this->OnError('数据库错误', $e);
		}
		catch(Exception $e)
		{
			$this->OnError(get_class($e).'错误', $e);
		}
	}
	
	public function Run()
	{
		try 
		{
			$ctrlname = $this->GetRequest()->GetInput(FIELD_CONTROLLER);
			if (strpos($ctrlname, '.')!==FALSE)
			{
				throw new PCException("The input param is not correct, ctrl:".$ctrlname);
			}
			if (!$ctrlname)
			{
				$ctrlname = "Index";
			}
			$ctrlname.= "Controller";
			
			$this->_controller = new $ctrlname();
			
			if (!$this->_controller || !($this->_controller instanceof ControllerBase))
			{
				throw new PCException("The ".$ctrlname." is not a Controller", LOCATION_HOME);
			}
			
			$this->_controller->Init($this, $this->_request, $this->_view);
			
			$this->_controller->Dispatch();
		} 
		catch (AuthException $e)
		{
			$this->OnMessage($e->GetMessage(), $e, $e->GetNextLocation());
		}
		catch (PCException $e) 
		{
			$this->OnMessage($e->getMessage(), $e);
		}
		catch(SmartyException $e)
		{
			$this->OnError('模板错误', $e);
		}
		catch(PDOException $e)
		{
			$this->OnError('数据库错误', $e);
		}
		catch(Exception $e)
		{
			$this->OnError(get_class($e).'错误', $e);
		}
	}
	
	/**
	 * Gets the AppSetting instance.
	 * @return AppSetting
	 */
	public function GetSetting()
	{
		return $this->_settings;
	}
	
	/**
	 * Gets the IRequest instance.
	 * @return IRequest
	 */
	public function GetRequest()
	{
		return $this->_request;
	}
	
	/**
	 * Gets the ICache instance.
	 * @return ICache
	 */
	public function GetCache()
	{
		return $this->_cache;
	}
	
	/**
	 * Gets the IView instance
	 * @return IView
	 */
	public function GetView()
	{
		return $this->_view;
	}
	
	/**
	 * Gets the ILogger instance
	 * @return ILogger
	 */
	public function GetLogger()
	{
		return $this->_logger;
	}
	
	/**
	 * Gets the IDatabase instance for sys logging.
	 * @return IDatabase
	 */
	public function GetDB()
	{
		return $this->_db;
	}
	
	public function GetEnv($field)
	{
		return $this->_envs[$field];
	}

	/**
	 * Gets the WebSite instance.
	 * @return WebSite
	 */
	public function GetWebSite()
	{
		return $this->_website;
	}

	/**
	 * Gets the IExtension instance of regestered enxtension.
	 * @param string $ext_name
	 * @return IExtension
	 */
	public function GetExt($ext_name)
	{
		if (key_exists($ext_name, $this->_exts))
		{
			return $this->_exts[$ext_name];
		}
		return null;
	}
	
	/**
	 * Gets the extensions' menu tree.
	 */
	public function GetExtMenus()
	{
	    $menus = array();
	    foreach ($this->_exts as $extname => $ext)
	    {
	        $menu = $ext->GetMenu();
	        $submenus = $ext->GetSubMenus();
	        $menus[] = array('menu'=>$menu, 'sub'=>$submenus);
	    }
	    return $menus;
	}
    
    public function ExceptionErrorHandler($errno, $errstr, $errfile, $errline ) 
    {
    	if (!(error_reporting() & $errno)) 
    	{
            // This error code is not included in error_reporting
            return;
    	}
    	
    	//TRACE( "ExceptionErrorHandler : $errno");
    	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	}
	
	/**
	 * handle the error message and exception.
	 * @param string $error
	 * @param Exception $exception
	 */
	public function OnMessage($error, Exception $exception=null, $nextlocation=null)
	{
		if ($exception)
		{
			// TODO: Log
		}
	    if($this->_controller)
	    {
    		if ($nextlocation)
    		{
    			$this->_controller->Message($error, $nextlocation);
    		}
    		else
    		{
    			$this->_controller->Message($error);
    		}
	    }
	    else 
	    {
	        echo "<div style='width:300px;height:200px;left:50%;height:50%;
	        margin-left:150px;margin-top:100px;border:solid 2px'>\n";
	        echo "<h3>$error</h3>\n";
	        echo "</div>\n";
	    }
		$this->AppExit();
	}
	
	/**
	 * handle the error message and exception.
	 * @param string $error
	 * @param Exception $exception
	 */
	public function OnError($error, Exception $exception=null, $nextlocation=null)
	{
        echo "<div style='position:absolute;padding:30px;text-align:
        center;vertical-align:middle;width:500px;height:200px;left:50%;top:50%;
        margin-left:-250px;margin-top:-100px; border:solid 2px'>\n";
       	echo "<h3>$error</h3>\n";
        if(DEBUG)
        {
    		echo '<BR>------------------------<BR>\n<PRE>\n';
    		echo $exception->GetMessage();
    		echo "\n------------------------\n";
    		echo $exception->getTraceAsString();
    		echo '</PRE>\n<BR>------------------------<BR>\n';
        }
        else
        {
            echo "<div>".$exception->GetMessage()."</div>\n";
        }
        echo "</div>\n";
		$this->AppExit();
	}
	
	public function AppExit()
	{
		exit;
	}
	
	public function DebugInfo()
	{
		if (DEBUG /*&& WEBSERVICE!=1*/)
		{
			$mtime = explode(' ', microtime());
			$totaltime = number_format(($mtime[1] + $mtime[0] - $this->_env['timestart']) , 6);
			$info1 .= '[ 执行时间 '.$totaltime.' second(s) ] [ 数据库查询 '.$this->GetDB()->GetQueryTimes().' 次 ]';
			if(WEBSERVICE==1)
			{
				echo "\n<!--".$info1.'-->';
			}
			else 
			{
				echo '<table class=border width=100%><tr><td>'.$info1.'</td></tr></table>';
			}
		}
	}
}