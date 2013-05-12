<?php
//using AccountGroup
class Account extends DataObject implements IAuthentication
{
	const GUEST_USERID = 1;
	const SYSTEM_USERID = 2;
	const SHELL_USERID = 3;
	
	public static $COOKIE_UID = "__PCFW_UID";
	public static $COOKIE_HASH = "__PCFW_HASH";
	
	/**
	 * AccountGroup instance
	 * @var AccountGroup
	 */
	private $_group = null;
	private $_isAuthenticated = false;
	
	public function __construct($userid=null)
	{
		parent::__construct($userid);
	}
	
	public static function GetTable()
	{
		return 'account';
	}
	
	public static function GetPK()
	{
		return 'userid';
	}
	
	public static function GetObjName()
	{
		return '用户';
	}
	
	public function GetData($refresh=false)
	{
		parent::GetData($refresh);
		if (!$this->_group || $refresh)
		{
			$this->_group = new AccountGroup($this->groupid);
		}
		$this->_group->GetData($refresh);
		//print_r($this->_data);
		return $this->_data;
	}
	
	public function __get($property)
	{
		$val = parent::__get($property);
		if (!$val && $this->_group)
		{
			$val = $this->_group->__get($property);
		}
		return $val;
	}
	
	// Implements IAuthentication
	public function GetName()
	{
	    $this->GetData();
	    return $this->username;
	}
	
	public function GetRoleName()
	{
	    $this->GetData();
	    return $this->_group->groupname;
	}
	
	 /**
     * Gets the previleges array of the authenticated account.
     * @return array
     */
	public function GetPrivileges()
    {
	    $this->GetData();
        return $this->_group->GetModuleRights();
    }
	
    /**
     * Indicates if the account is authenticated.
     * @return bool
     */
	public function IsAuthenticated()
	{
		return $this->_isAuthenticated;
	}
	
	public static function GetAccountByName($name)
	{
	    $rs = DBHelper::GetSingleRecord(Account::GetTable(), 'username', $name);
	    if($rs)
	    {
	        return new Account($rs);
	    }
		return null;
	}
	
	public static function EncryptPassword($inputpw, $cryptkey='')
	{
		return md5($inputpw);
	}
	
	public static function EncryptCookieVal($input, $cryptkey)
	{
		return md5($cryptkey."_PCFW_CONTENT_MANAGER_".$input);
	}
	
	public static function GetActiveAccounts($groupid)
	{
	    $join_options[] = DBHelper::JoinOption(AccountGroup::GetTable(), 
	    	'', '', 'groupid', 'groupid', 'groupname,issystem');
	    $filter_options[] = DBHelper::FilterOption(null, 'status', 1, '=');
	    $filter_options[] = DBHelper::FilterOption(AccountGroup::GetTable(), 'issystem', 0, '=');
	    if($groupid>0)
	    {
	       $filter_options[] = DBHelper::FilterOption(null, 'groupid', $groupid, '=');
	    }
	    $totalcount = 0;
		return DBHelper::GetJoinRecordSet(self::GetTable(), 
			$join_options, $filter_options, null,
			0, 99999, $totalcount);
	}
	
	private function SetAsGuest()
	{
		$this->_isAuthenticated = true;
		$this->ResetId(self::GUEST_USERID);
		$this->_data = null;
	}
	
	public function Authenticate()
	{
		$cookieuserid = AppHost::GetApp()->GetRequest()->GetInputFrom(INPUT_COOKIE, Account::$COOKIE_UID);
		$cookiehash = AppHost::GetApp()->GetRequest()->GetInputFrom(INPUT_COOKIE, Account::$COOKIE_HASH);
		if(!$cookieuserid || !$cookiehash)
		{
			//die("setas guest".$cookieuserid.",".$cookiehash);
			$this -> SetAsGuest();
			return $this;
		}
		$this->ResetId($cookieuserid);
		if (!$this->GetData(true)) 
		{
			$this->_clearCookie();
			$this -> SetAsGuest();
			return $this;
		}
		if (!$this->CheckPassword($cookiehash, 'cookie'))
		{
			$this->_clearCookie();
			$this -> SetAsGuest();
			return $this;
		}
		$this->_isAuthenticated = true;
		return $this;
	}
	
	public function CheckPassword($inputPass, $authType='web')
	{
		if ( !$inputPass || !$this->GetData())
		{
			echo 'empty'.$this->GetId().$inputPass;
			return false;
		}
		switch ($authType)
		{
			case 'web':
				if ($this->password!=self::EncryptPassword($inputPass, $this->GetId()))
				{
					echo 'web wrong';
					return false;
				}
				break;
			case 'cookie':
				if (self::EncryptCookieVal($this->password, $this->GetId())!=$inputPass)
				{
					return false;
				}
				break;
			case 'webservice':
				break;
			default;
				throw new PCException("invalid Authentication type.");
				return false;
		}
		$this->_isAuthenticated = true;
		return true;
	}
	
	public function Login($inputUser, $inputPass, $authType='web',$saveloginstate=false)
	{
		$existuser = DBHelper::GetSingleRecord(self::GetTable(), 'username', $inputUser);
		if (!$existuser || !$existuser['userid'])
		{
			throw new AuthException("Username or password invalid", LOCATION_LOGIN);
		}
		$this->_id = $existuser['userid'];
		if (!$this->GetData())
		{
			throw new AuthException("Username or password invalid", LOCATION_LOGIN);
		}
		if(!$this->CheckPassword($inputPass, $authType))
		{
			throw new AuthException("Username or password invalid", LOCATION_LOGIN);
		}
		
		$cookietime = saveloginstate ? (time()+86400*365) : 0;
		$cookiepw = Account::EncryptCookieVal($this->password, $this->GetId());
		$cookiepath = APP_URL;
		$cookiedomain = "";
		//echo Account::$COOKIE_HASH;
		setcookie('useless', $cookiepw, $cookietime, $cookiepath, $cookiedomain);
		setcookie(Account::$COOKIE_HASH, $cookiepw, $cookietime, $cookiepath, $cookiedomain);
		setcookie(Account::$COOKIE_UID, $this->GetId(), $cookietime, $cookiepath, $cookiedomain);

		return true;
	}
	
	private function _clearCookie()
	{
		$cookietime = 0;
		$cookiepath = APP_URL;
		$cookiedomain = "";
		
		setcookie(Account::$COOKIE_HASH, "", -100000, $cookiepath, $cookiedomain);
		setcookie(Account::$COOKIE_UID, "", -100000, $cookiepath, $cookiedomain);
	}
	
	public function Logout()
	{
		$this->_clearCookie();
		
		$this->SetAsGuest();
		
		return true;
	}
	
	private function _checkOnePermission($modules, $isForcedown=UnForceDown)
	{
		$this->GetData();
		if ($this->_group)
		{
			$rights = $this->_group->GetModuleRights();
			if(!$modules || in_array('ALL', $rights))
			{
				return true;
			}
			foreach ($modules as $module)
			{
				if( in_array($module, $rights))
				{
					return true;
				}
			}
		}
		if($isForcedown==ForceDown)
		{
		    $modulename = $module;
		    foreach (ModuleConfig::$LoadModules as $modulename)
    	    {
    	        $mobject = AppHost::GetApp()->GetExt($modulename);
    	        if($mobject)
    	        {
    	            $list = $mobject->GetPriveleges();
    	            foreach($list as $rs)
    	            {
    	                if($module==$rs[0])
    	                {
    	                    $modulename = $rs[1];
    	                }
    	            }
    	        }
    	    }
			throw new AuthException("没有  [$module] 权限");
		}
		return false;
	}
	
	public function CheckPermission($module, $isForcedown=UnForceDown)
	{
		if(is_array($module))
		{
			return $this->_checkOnePermission($module, $isForcedown);
		}
		$this->GetData();
		if ($this->_group)
		{
			$rights = $this->_group->GetModuleRights();
			if($module=="" || in_array('ALL', $rights) || in_array($module, $rights))
			{
				return true;
			}
		}
		if($isForcedown==ForceDown)
		{
		$modulename = $module;
		    foreach (ModuleConfig::$LoadModules as $modulename)
    	    {
    	        $mobject = AppHost::GetApp()->GetExt($modulename);
    	        if($mobject)
    	        {
    	            $list = $mobject->GetPriveleges();
    	            foreach($list as $rs)
    	            {
    	                if($module==$rs[0])
    	                {
    	                    $modulename = $rs[1];
    	                }
    	            }
    	        }
    	    }
			throw new ErrorException("没有  [$module] 权限");
		}
		return false;
	}
	
	public function Delete()
	{
		if ($this->_group->issystem)
		{
			throw new PCException("该用户属于系统用户组,无法删除");
		}
		return parent::Delete();
	}
	
	public function GetAuthorizedDeviceIds()
	{
	    return explode('|', $this->deviceright);
	}
}