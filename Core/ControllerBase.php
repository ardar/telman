<?php

abstract class ControllerBase
{
	/**
	 * The global IApplication instance of the website.
	 * @var IApplication
	 */
	private $_app;
	
	/**
	 * The current IRequest object.
	 * @var IRequest
	 */
	private $_request;
	
	/**
	 * The current IView object.
	 * @var IView
	 */
	private $_view;
	
	/**
	 * The current request controller.
	 * @var string
	 */
	protected $_controllerId;
	
	/**
	 * The current request action.
	 * @var string
	 */
	protected $_actionId;
	
	/**
	 * The current logged in account instance.
	 * @var IAuthentication
	 */
	protected $_account;
	
	protected $_page;
	protected $_offset;
	protected $_perpage;
	
	public function __construct()
	{
		$this->_perpage = 10;
	}
	
	public function Init(IApplication $app, IRequest& $request, IView& $view)
	{
		$this->_app = $app;
		$this->_request = $request;
		$this->_view = $view;
		$this->_actionId = $request->GetInput("action");
		if (!$this->_actionId)
		{
			$this->_actionId = "index";
		}
		$classname = get_class($this);
		$this->_controllerId = substr($classname, 0, strrpos($classname, "Controller"));
		
		$this->_account = new Account(null);
		$this->_account -> Authenticate();
		
		// Pager
		$this->_page = $this->GetRequest()->GetInput('page','int');
		if ($this->_page<=0) 
		{
			$this->_page = 1;
		}
		$this->_offset = $this->_perpage * ($this->_page - 1);
	}
	
	/**
	 * Get the IApplication instance.
	 * @return IApplication
	 */
	public final function GetApp()
	{
		return $this->_app;
	}
	
	/**
	 * Gets the IRequest object;
	 * @return IRequest
	 */
	public final function GetRequest()
	{
		return $this->_request;
	}
	
	/**
	 * Gets the IView object;
	 * @return IView
	 */
	public final function GetView()
	{
		return $this->_view;
	}
	
	/**
	 * Gets the current login Account instance;
	 * @return IAuthentication
	 */
	public final function GetAccount()
	{
		return $this->_account;
	}
	
	public final function GetControllerId()
	{
		return $this->_controllerId;
	}
	
	public final function GetActionId()
	{
		return $this->_actionId;
	}
	
	/**
	 * Get the WebSite instance.
	 * @return WebSite
	 */
	public final function GetWebSite()
	{
		return $this->GetApp()->GetWebSite();
	}
	
	public final function GetPage()
	{
		$this->_page = $this->GetRequest()->GetInput('page','int');
	    if ($this->_page<=0) 
		{
			$this->_page = 1;
		}
		return $this->_page;
	}
	
	public final function GetOffset()
	{
	    $this->GetPage();
	    return $this->_offset = $this->_perpage * ($this->_page - 1);
	}
	
	public function Dispatch()
	{
		$actionname = $this->_actionId."Action";
		if (method_exists($this, $actionname)) 
		{
            call_user_func(array($this, $actionname));
        }
        else
        {
        	throw new PCException("action invalid ".$this->_controllerId."::".$this->_actionId, LOCATION_BACK);
        }
	}
	
	public function CleanUp()
	{
		//Nothing todo.
	}
	
	/**
	 * Display view template with assign params
	 * @param string $viewScript
	 * @param array $params
	 */
	public function Display($viewScript, $params)
	{
		foreach ($params as $field => $value)
		{
			$this->GetView()-> Assign($field, $value);
		}
		$this->GetView()-> Display($viewScript);
	}
	
	public function Redirect($url)
	{
		ob_clean();
		header("Location:$url");
		$this->GetApp()->AppExit();
	}
	
	/**
	 * Display message box
	 * @param string $message
	 * @param string $nextlocation LOCATION_BACK
	 * @param bool $isForceDown
	 */
	public function Alert($message, $nextlocation, $isForceDown)
	{
		if(WEBSERVICE==1)
		{
			echo $message;
			if($isForceDown)
			{
				$this->GetApp()->AppExit();
			}
			return;
		}
		if($nextlocation==LOCATION_REFERER)
			$nextlocation = $this->GetApp()->GetEnv('referer');
		$message = OpenDev::ehtmlspecialchars($message);
		echo "
			<script language=\"JavaScript\">";
		if($message)
			echo "	alert(\"$message\");";
		if($nextlocation==LOCATION_THIS)
		{
			echo "\n</script>";
		}
		elseif (!$nextlocation || $nextlocation==LOCATION_BACK )
		{
			echo "this.history.go(-1);
				</script>";
		}
		else 
		{
			$redirect_top = false;
			switch ($nextlocation)
			{
			case LOCATION_HOME:	$nextlocation = urlhelper("home"); $redirect_top=true; break;
			case LOCATION_UCP:	$nextlocation = urlhelper("ucp");$redirect_top=true;  break;
			case LOCATION_LOGIN: $nextlocation = urlhelper("login"); $redirect_top=true; break;
			default: break;
			}
			if ($redirect_top)
			{
				echo "window.top.location.href=\"$nextlocation\";
				</script>";
			}
			else 
			{
			echo "this.location.href=\"$nextlocation\";
				</script>";
			}
		}
		if ($isForceDown) $this->GetApp()->AppExit();
	}
	
	/**
	 * Display message box
	 * @param string $message
	 * @param string $nextlocation LOCATION_BACK
	 * @param bool $isForceDown
	 */
	public function Message($message, $nextlocation=LOCATION_BACK, $isForceDown=ForceDown)
	{
		return $this->Alert($message, $nextlocation, $isForceDown);
		$btn_onclick = "";
		$url_location = "";
		if($nextlocation==LOCATION_THIS)
		{
		}
		elseif (!$nextlocation || $nextlocation==LOCATION_BACK )
		{
			$btn_onclick = "window.history.go(-1);";
		}
		else 
		{
			switch ($nextlocation)
			{
			case LOCATION_HOME:	$url_location = urlhelper("home"); break;
			case LOCATION_UCP:	$url_location = urlhelper("ucp"); break;
			case LOCATION_LOGIN: $url_location = urlhelper("login"); break;
			default: break;
			}
			$btn_onclick = "window.top.location.href=\"$url_location\";";
			$url_location = "";
		}
		if ($url_location)
		{
			$redirect = ' http-equiv="refresh" content="3;url='.$url_location.'"';
		}
		$this -> GetView() -> Assign('url_redirect', $redirect);
		$this -> GetView() -> Assign('btn_onclick', $btn_onclick);
		$this -> GetView() -> Assign('message', $message);
		$this -> GetView() -> Display("msg.htm");
		exit();
	}
	
	public function CheckPermission($module="", $isForceDown=ForceDown)
	{
		if (!$this->GetAccount() || $this->GetAccount()->GetId()==ACCOUNT::GUEST_USERID) 
		{
			if ($isForceDown)
			{
				throw new AuthException('请先登录 ', LOCATION_LOGIN);
			}
			else
			{
				return false;
			}
		}
		return $this->GetAccount()->CheckPermission($module, $isForceDown);
	}
	
	public function GetSortOption($defsortfield='', $defsorttype='asc')
	{
		$sortfield = $this->GetRequest()->GetInput('sortfield','string');
		$sorttype = $this->GetRequest()->GetInput('sorttype','string');
		$sortfield = $sortfield ? $sortfield : $defsortfield;
		$sort_option = null;
		if ($sortfield)
		{
			$sorttype = $sorttype ? $sorttype : $defsorttype;
			$sorttype = $sorttype ? $sorttype : 'asc';
			$sort_option[] = " $sortfield $sorttype";
		}
		return $sort_option;
	}
	
	public function GetFilterOption($option_set)
	{
		foreach ($option_set as $field => $field_op)
		{
			$table = null;
			$value = null;
			$op = null;
			if (strpos($field, '.')>0)
			{
				$arr = explode('.', $field);
				$table = $arr[0];
				$field = $arr[1];
			}
			$sval = $this->GetRequest()->GetInput($field);
			if(strlen($sval)>0)
			{
				switch ($field_op)
				{
					case '==': 
						$value =  $this->GetRequest()->GetInput($field, DT_NUMBER); 
						$op = '='; 
						break;
					case '=': 
					case 'like': $value = $sval; $op = $field_op; break;
					case '%like': $value = "%$sval"; $op = 'like'; break;
					case 'like%': $value = "$sval%"; $op = 'like'; break;
					case '%like%': $value = "%$sval%"; $op = 'like'; break;
					default: throw new PCException("解析参数错误: 不合法的Filter参数");
						break;
				}
				$filter_options[] = array('table'=>$table, 'field'=>$field, 'value'=>$value, 'op'=>$op);
			}
		}
		return $filter_options;
	}
}