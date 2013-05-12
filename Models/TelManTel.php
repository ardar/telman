<?php
class TelManTel extends DataObject
{
	public static function GetTable()
	{
		return 'telman_tel';
	}
	
	public static function GetPK()
	{
		return 'telid';
	}
	
	public static function GetObjName()
	{
		return '电话号码';
	}
	
	public static function GetNumberList($ownerid, $excludenumber=null)
	{	
	    if($ownerid>0)
	    {
	        $filter_options[] = DBHelper::FilterOption(null, 'ownerid', $ownerid, '=');
	    }
	    $filter_options[] = DBHelper::FilterOption(null, 'telisactive', 1, '=');
	    $join_options[] = array('table'=>SysCode::GetTable(), 'left_field'=>'telcompanyid', 
			'right_field'=>'syscodeid','select'=>'syscodename as telcompanyname, syscode as telcompanycode');
		$totalcount = 0;
		$numberlist = DBHelper::GetJoinRecordSet(TelManTel::GetTable(), 
			$join_options, $filter_options, null,
			0, 99999, $totalcount);
	    foreach($numberlist as $key => $number)
	    {
	        if($number['telid']==$excludenumber)
	        {
	            unset($numberlist[$key]);
	            continue;
	        }
	        $numberlist[$key]['title'] = $number['telnumber']."(".$number['telcompanyname'].")";
	    }
	    return $numberlist;
	}
	
	public function Delete()
	{
		$sql = "select count(*) as count from ".TelManBaby::GetTable()." 
			where telid='".$this->GetId()."' 
			and babyisactive=1";
		$rs = DBHelper::GetQueryRecord($sql);
		if($rs['count']>0)
		{
			throw new PCException('该号码已上报，不能删除');
			return false;
		}
		return parent::Delete();
	}
	
	public static function GetTimeRangeByMonth($year, $month)
	{
	    if($year<=0 || $month<=0)
	    {
	        return null;
	    }
	    $begintime = mktime(0, 0, 0, $month, 1, $year);
	    $endmonth = $month;
	    $endyear = $year;
	    if($month>=12)
	    {
	        $endyear = $year+1;
	        $endmonth = 1;
	    }
	    $endtime = mktime(0, 0, 0, $endmonth, 1, $endyear)-1;
	    return array('begin'=>$begintime, 'end'=>$endtime);
	}
	
	public static function GetMonthComboArray()
	{
	    $result = array();
	    $beginmonth = 8;
	    $beginyear = 2011;
	    while(true)
	    {
	        $time = mktime(0,0,0, $beginmonth, 1, $beginyear);
	        if($time > time() + 31*86400)
	        {
	            break;
	        }
	        
	        $date = date('Y-m',$time);
	        $datename = date('Y年m月',$time);
	        $result[$date] = $datename;
	        
	        $beginmonth++;
	        if($beginmonth>12)
	        {
	            $beginmonth=1;
	            $beginyear ++;
	        }
	    }
	    $result['0'] = '全部月份';
	    return array_reverse($result) ;
	}
}