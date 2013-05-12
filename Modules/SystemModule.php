<?php
class SystemModule implements IExtension
{
    public function GetName()
    {
        return "系统管理";
    }
    
    public function Init($options)
    {
        
    }
    
    public function GetMenu()
    {
        return array('system.png','系统管理','','system');
    }
    
    public function GetSubMenus()
    {
        return array(
            array('system.png', '系统设置', '?controller=WebSite&action=list', 'system'),
            array('usergroup.png', '角色管理', '?controller=AccountGroup&action=list', 'accountgroup'),
            array('user.png', '用户管理', '?controller=Account&action=list', 'account'),
            array('syscode.png', '常量设置', '?controller=SysCode', 'system'),
            array('log.png', '账户日志', '?controller=FWLog&action=securitylist', 'account'),
        );
    }
    
    public function GetPriveleges()
    {
        return array(
        	'system' => '系统设置',
    		'accountgroup' => '用户组管理',
    		'account' => '用户管理',
        );
    }
}