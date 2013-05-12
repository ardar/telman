<?php
class MsgModule implements IExtension
{
    public function GetName()
    {
        return "短消息";
    }
    
    public function Init($options)
    {
        
    }
    
    public function GetMenu()
    {
        return array('msg.png','短消息','','msg');
    }
    
    public function GetSubMenus()
    {
        return array(
            array('inbox.png', '收件箱', '?controller=Msg&action=list&box=inbox', 'msg'),
            array('outbox.png', '发件箱', '?controller=Msg&action=list&box=outbox', 'msg'),
            array('sendmsg.png', '发短消息', '?controller=Msg&action=send', 'msg'),
        );
    }
    
    public function GetPriveleges()
    {
        return array(
		'msg' => '短消息',
        );
    }
}