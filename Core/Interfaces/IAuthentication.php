<?php
interface IAuthentication
{
    public function GetId();
    
    public function GetName();
    
    public function GetRoleName();
    
    /**
     * Gets the previleges array of the authenticated account.
     * @return array
     */
    public function GetPrivileges();
    
    /**
     * Indicates if the account is authenticated.
     * @return bool
     */
    public function IsAuthenticated();
    
    public function Authenticate();
    
	public function Login($username, $password, $authType='web');
	
	public function Logout();
	
	public function CheckPermission($module, $isForcedown=true);
}