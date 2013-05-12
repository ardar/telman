<?php
class TelManBaby extends DataObject
{
	public static function GetTable()
	{
		return 'telman_baby';
	}
	
	public static function GetPK()
	{
		return 'babyid';
	}
	
	public static function GetObjName()
	{
		return '宝宝';
	}
	
	public static function SortTelNumber($numberlist)
	{
	    $usercount = 0;
	    $usercountlist = array();
	    $leftcountlist = array();
	    $resultcountlist = array();
	    $resultlist = array();
	    
	    $tempprioritylist = array();
	    $tempowneridlist = array();
	    foreach ($numberlist as $key => $rs) 
	    {
            $tempprioritylist[$key]  = $rs['telpriority'];
            $tempowneridlist[$key] = $rs['ownerid'];
	        $usercountlist[$rs['ownerid']] ++;
	        $leftcountlist[$rs['ownerid']] ++;
        }
        arsort($usercountlist, SORT_NUMERIC);
        $usertotalcount = count($usercountlist);
//        TRACE($usercountlist);
//        TRACE($tempowneridlist);
//        TRACE($numberlist);
//        // Sort the data with volume descending, edition ascending
//        // Add $data as the last parameter, to sort by the common key
        array_multisort($tempprioritylist, SORT_DESC, $tempowneridlist, SORT_ASC, $numberlist);
//        TRACE($numberlist);
//        TRACE($tempowneridlist);
        
        $numbercount = count($numberlist);
        $resultidlist = array();
        $loopcount = 1;
        while(count($resultidlist)<=$numbercount && $loopcount<=$numbercount*2)
        {
            $waitrs = array();
    	    // fetch tel from numberlist.
    	    foreach ($numberlist as $key=>$rs)
    	    {
    	        if(in_array($rs['telid'], $resultidlist))
    	        {
    	            continue;
    	        }
    	        $percent = $usercountlist[$rs['ownerid']]/$numbercount;
    	        $savedpercent = $resultcountlist[$rs['ownerid']]/$numbercount;
    	        $nextpercent = ($resultcountlist[$rs['ownerid']]+1)/$numbercount;
    	        $curpercent = count($resultidlist)/$numbercount;
    	        $shouldpercent = $percent * $curpercent;
    	        if($savedpercent<=$shouldpercent)
    	        {
    	            //TRACE($rs['ownername']." $percent $savedpercent $curpercent $shouldpercent $nextpercent");
    	            if(!$waitrs || $leftcountlist[$rs['ownerid']]>$leftcountlist[$waitrs['ownerid']])
    	            {
    	                $waitrs = $rs;
    	            }
        	    }
    	    }
    	    
    	    if($waitrs)
    	    {
    //	        $percent = $usercountlist[$waitrs['ownerid']]/$numbercount;
    //	        $savedpercent = $resultcountlist[$waitrs['ownerid']]/$numbercount;
    //	        $nextpercent = ($resultcountlist[$waitrs['ownerid']]+1)/$numbercount;
    //	        $curpercent = count($resultidlist)/$numbercount;
    //	        $shouldpercent = $percent * $curpercent;
        	    //TRACE($waitrs['ownername']." $percent $savedpercent $curpercent $shouldpercent $nextpercent");
        	    $waitrs['loopcount'] = $loopcount;
    	        $resultidlist[] = $waitrs['telid'];
                $resultlist[] = $waitrs;
                $resultcountlist[$waitrs['ownerid']]++;
                $leftcountlist[$waitrs['ownerid']]--;    	
    	    }    
            
    	    $loopcount++;
        }
        if(count($resultlist)<$numbercount)
        {
            TRACE("没对 ".count($resultlist)." < $numbercount");
            TRACE($resultlist);
            exit("没有对");
        }
	    return $resultlist;
	}
	
	public function PreSubmit($maminame, $birthday)
	{
	    $this->GetData();
	    if($this->babyisactive)
	    {
	        throw new PCException('该记录 ('.$this->GetId().') '.$this->maminame.' 已上报, 无法修改名字和生日');
	    }
	    $this->maminame = $maminame;
	    $this->birthday = $birthday;
	    return $this->SaveData();
	}
	
	public function Submit($reportid, $issubmit, $reportorder)
	{
	    $this->GetData();
	    if($this->reportid!=0 && $this->reportid!=$reportid)
	    {
	        throw new PCException('该记录已上报到其他上报名单, 无法再次上报');
	    }
	    if($this->issuccess)
	    {
	        throw new PCException('该记录已返回结果, 无法再次修改上报状态');
	    }
	    if($issubmit)
	    {
	        $this->reportid = $reportid;
    	    if(!$this->submittime)
    	    {
    	        $this->submittime = time();
    	    }
    	    $this->reportorder = $reportorder;
    	    $this->babyisactive = 1;
	    }
	    else
	    {
	        $this->reportid = 0;
    	    $this->submittime = 0;  
    	    $this->reportorder = 0;
    	    $this->babyisactive = 0;	
	    }
	    return $this->SaveData();
	}
	
	public function PickUp($pickupdesc='')
	{
	    $this->GetData();
	    if(!$this->babyisactive)
	    {
	        throw new PCException('该记录未上报, 无法录入返回结果');
	    }
	    if($this->resulttime>0)
	    {
	        throw new PCException('该记录已返回结果, 无法修改接电话状态');
	    }
	    $this->pickuptime = time();
	    $this->pickupdesc = $pickupdesc;
	    return $this->SaveData();
	}
	
	public function Result($reportid, $issuccess, $resultdesc='')
	{
	    $this->GetData();
	    if($this->reportid!=$reportid)
	    {
	        throw new PCException('该记录 '.$this->GetId().' 已分配到其他报表 '.$this->reportid.', 无法修改结果');
	    }
	    if(!$this->babyisactive)
	    {
	        throw new PCException('该记录未上报, 无法录入返回结果');
	    }
	    if(!$this->resulttime)
	    {
	        $this->resulttime = time();
	    }
	    $this->resultdesc = $resultdesc;
	    $this->issuccess = $issuccess;
	    return $this->SaveData();
	}
}