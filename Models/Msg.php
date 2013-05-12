<?php
class Msg extends DataObject
{	
    const MsgInBox = "inbox";
    const MsgOutBox = "outbox";
    
    public static $Boxies = array(
        self::MsgInBox => '收件箱',
        self::MsgOutBox => '发件箱',
    );
    
	public static function GetTable()
	{
		return 'msg';
	}
	
	public static function GetPK()
	{
		return 'msgid';
	}
	
	public static function GetObjName()
	{
		return '短消息';
	}
	
	public function Read($isReaded=true)
	{
	    if(!$this->isreaded)
	    {
    	    $this->isreaded = $isReaded;
    	    $this->readtime = time();
    	    return $this->SaveData();
	    }
	    return true;
	}
	
	public static function SendMsgByName($fromname, $tonames, $title, $content, 
	    $isimportant=false, $isemergency=false, $savetosent=false)
	{
	    $fromacc = Account::GetAccountByName($fromname);
	    if(!$fromacc)
	    {
	        throw new PCException("没有找到发件人 $fromname");
	    }
	    $namelist = array();
	    $idlist = array();
	    
	    if(is_array($tonames))
	    {
	        $namelist = $tonames;
	    }
	    else 
	    {
	        $namelist = explode(',', $tonames);
	    }
	    foreach($namelist as $name)
	    {
	        $name = trim($name);
	        $toacc = Account::GetAccountByName($name);
    	    if(!$toacc)
    	    {
        	    throw new PCException("没有找到收件人 $name");
    	    }
    	    $id = $toacc->GetId();
    	    $idlist[] = $id;
    	    $tostr = $tostr? (", ".$name) : $name;
	    }
	    foreach ($idlist as $id)
	    {
    	    $msg = new Msg();
	        $msg -> ownerid = $id;
    	    $msg -> from = $fromname;
	        $msg -> to = $tostr;
    	    $msg -> title = $title;
    	    $msg -> content = $content;
    	    $msg -> isimportant = $isimportant;
    	    $msg -> isemergency = $isemergency;
    	    $msg -> sendtime = time();
	        $msg -> box = Msg::MsgInBox;
	        $msg -> SaveData();
	    }
	    return true;
	}
	
	public static function SendMsg($fromid, $toids, $title, $content, 
	    $isimportant=false, $isemergency=false, $savetosent=false)
	{
	    $fromacc = new Account($fromid);
	    if(!$fromacc->GetData())
	    {
	        throw new PCException("没有找到发件人 $fromid");
	    }
	    $namelist = array();
	    $idlist = array();
	    if(is_array($toids))
	    {
	        $idlist = $toids;
	    }
	    else 
	    {
	        $idlist[] = $toids;
	    }
	    $tostr = '';
	    foreach($idlist as $id)
	    {
	        $toacc = new Account($id);
    	    if(!$toacc->GetData())
    	    {
    	        throw new PCException("没有找到收件人 $id");
    	    }
    	    $idlist[] = $id;
    	    $tostr = $tostr? (",".$toacc->username) : $toacc->username;
	    }
	    foreach ($idlist as $id)
	    {
    	    $msg = new Msg();
	        $msg -> ownerid = $id;
    	    $msg -> from = $fromacc->username;
	        $msg -> to = $tostr;
    	    $msg -> title = $title;
    	    $msg -> content = $content;
    	    $msg -> isimportant = $isimportant;
    	    $msg -> isemergency = $isemergency;
    	    $msg -> sendtime = time();
	        $msg -> box = Msg::MsgInBox;
	        $msg -> SaveData();
	    }
	    return true;
	}
}