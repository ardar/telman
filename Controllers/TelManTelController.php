<?php

class TelManTelController extends ControllerBase
{
	const ModuleRight='telmantel_view';
	const ModuleRightEdit='telmantel_edit';
	const ObjName = "手机号码";
	
	public function Init($app, $request, $view)
	{
	    $this->_perpage = 1000;
		parent::Init($app, $request, $view);
		
	}
	
	public function presubmitAction()
	{
	    $this->CheckPermission(array('telmantel_presubmit','telmantel_admin'));
	    $canedit_presubmit = $this->CheckPermission('telmantel_presubmit', false);
	    $canedit_admin = $this->CheckPermission('telmantel_admin', false);
	    
		$totalcount = 0;
		$transfertoid = $this->GetRequest()->GetInput('transfertoid','int');
		$ownerid = $this->GetRequest()->GetInput('ownerid','int');
		$filter_fields = array(
			'telnumber' => 'like%', 
			'transfertoid' => '==', 
		    'ownerid'=>'==',
			);
		//$join_options[] = array('table'=>TelManTel::GetTable(), 'alia'=>'ttel', 'left_field'=>'transfertoid', 
		//	'right_field'=>'telid','select'=>'telnumber as transfertonumber');
		$join_options[] = array('table'=>Account::GetTable(), 'left_field'=>'ownerid', 
			'right_field'=>'userid','select'=>'username as ownername');
		
		$brands = TelManBrand::GetActiveBrands();
		foreach ($brands as $key=>$brand)
		{
		    $brandid = $brand['brandid'];
		    $alia = 'baby_'.$brandid;
		    $join_options[] = array('joinsql'=>" left join ".TelManBaby::GetTable()." as $alia
		     on (".TelManTel::GetTable().".telid=$alia.telid and $alia.brandid='$brandid')", 
    			'select'=>
    				$alia.'.maminame as maminame_'.$brandid.","
		            .$alia.".birthday as birthday_".$brandid.","
		            .$alia.".babyisactive as babyisactive_".$brandid);
		}
		
		$filter_options = $this->GetFilterOption($filter_fields);
		$filter_options[] = DBHelper::FilterOption(null, 'telisactive', 1, '=');
		if(!$canedit_admin)
		{
		$filter_options[] = DBHelper::FilterOption(null, 'ownerid', $this->GetAccount()->GetId(), '=');
		}
		
		$yearmonth = $this->GetRequest()->GetInput('yearmonth','string');
		if($yearmonth==='')
		{
		    $yearmonth = date('Y-m');
		}
	    if($yearmonth)
	    {
	        $filter_options[] = array('sql'=>" FROM_UNIXTIME(".TelManTel::GetTable().".begintime,'%Y-%m') ='$yearmonth' ");
	    }
		
		$list = DBHelper::GetJoinRecordSet(TelManTel::GetTable(), 
			$join_options, $filter_options, $this->GetSortOption(),
			$this->_offset, $this->_perpage, $totalcount);
	    foreach($list as $key=>$rs)
	    {
    	    foreach ($brands as $brandkey=>$brand)
    		{
    		    $brandid = $brand['brandid'];
    		    if(!$rs['babyisactive_'.$brandid])
    		    {
    		    $list[$key]['maminame_'.$brandid] = HtmlHelper::Input(
    		    	'maminame_'.$brandid."[]",$rs['maminame_'.$brandid],'text','style="width:80px"'); 
    		    $list[$key]['birthday_'.$brandid] = HtmlHelper::DatePicker(
    		    	'birthday_'.$brandid."_".$key,'birthday_'.$brandid."[]",$rs['birthday_'.$brandid],
    		        '','style="width:70px"'); 
    		    }
    		    else 
    		    {
    		    $list[$key]['maminame_'.$brandid] = $rs['maminame_'.$brandid].HtmlHelper::Input(
    		    	'maminame_'.$brandid."[]",$rs['maminame_'.$brandid],'hidden',''); 
    		    $list[$key]['birthday_'.$brandid] = HtmlHelper::FormatDate($rs['birthday_'.$brandid])
    		        .HtmlHelper::Input(
    		    		'birthday_'.$brandid."[]",$rs['birthday_'.$brandid],'hidden'); 
    		    }
    		}
	    }
			
	    $brandcount = count($brands);
	    $tablewidth = $canedit_admin ? ($brandcount*220+250) : ($brandcount*220+180);
		
		$table = new HtmlTable("编写名字/出生日期",'border');
		$table -> BindParam('width', $brandcount*220+220);
		$table -> BindField('telid', "", 'hidden');
		$table -> BindField('telid', "ID", '', 'list_pk', 'desc', 'nowrap');
		$table -> BindField('telnumber', self::ObjName, '', '', 'asc', 'nowrap');
		if($canedit_admin)
		{
		$table -> BindField('ownername', '所有者', '', '', 'asc',"style='width:50px;'");
		}
		$randcolor = 78;
		foreach ($brands as $key=>$brand)
		{
		    $brandid = $brand['brandid'];
		    
		    $randcolor = ($randcolor+79)%256;
		    $color = OpenDev::WebRGB(200 , 213, $randcolor);
		    $colorstyle = "background-color:$color;";
		    
	        $table -> BindField('babyisactive_'.$brandid, "上报", 
	        	'bool','','asc',"style='width:30px;$colorstyle'");
	        
            $table -> BindField('maminame_'.$brandid, $brand['brandname']."/名字", 
        		'', '', 'asc',"style='width:80px;$colorstyle';text-align:left;");
            
            $table -> BindField('birthday_'.$brandid, $brand['brandname']."/生日", 
        		'', '', 'asc',"style='width:80px;$colorstyle';text-align:left;");
		}
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
	    $numberlist = TelManTel::GetNumberList($this->GetAccount()->GetId());
	    $acclist = Account::GetActiveAccounts(0);
		$table -> BindHeadbar(" | 按号码".HtmlHelper::Input('telnumber','','text'));
		$table -> BindHeadbar(" | 按所有者".HtmlHelper::ListBox('ownerid',
		    $ownerid, $acclist, 'userid','username','全部'));
		$table -> BindHeadbar(" | 启用月份".HtmlHelper::ListBox('yearmonth',$yearmonth,
		    TelManTel::GetMonthComboArray()));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		
		$table -> BindFootbar(" | ".HtmlHelper::Button('提交修改', 'button',
			"onclick=\"if(!confirm('是否确定修改选中的条目'))return false;form.action.value='dopresubmit';form.submit();\""));
		$table -> BindFootbar(HtmlHelper::Input(FIELD_ACTION, 'dopresubmit','hidden'));
		$table -> BindFootbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function resultAction()
	{
	    $this->CheckPermission(array('telmantel_result','telmantel_admin'));
	    $canedit_admin = $this->CheckPermission('telmantel_admin', false);
	    
		$totalcount = 0;
		$transfertoid = $this->GetRequest()->GetInput('transfertoid','int');
		$ownerid = $this->GetRequest()->GetInput('ownerid','int');
		$filter_fields = array(
			'telnumber' => 'like%', 
			'transfertoid' => '==',  
		    'ownerid'=>'==',
			);
		//$join_options[] = array('table'=>TelManTel::GetTable(), 'alia'=>'ttel', 'left_field'=>'transfertoid', 
		//	'right_field'=>'telid','select'=>'telnumber as transfertonumber');
		$join_options[] = array('table'=>Account::GetTable(), 'left_field'=>'ownerid', 
			'right_field'=>'userid','select'=>'username as ownername');
		
		$brands = TelManBrand::GetActiveBrands();
		foreach ($brands as $key=>$brand)
		{
		    $brandid = $brand['brandid'];
		    $alia = 'baby_'.$brandid;
		    $join_options[] = array('joinsql'=>" left join ".TelManBaby::GetTable()." as $alia
		     on (".TelManTel::GetTable().".telid=$alia.telid and $alia.brandid='$brandid')", 
    			'select'=>
    				$alia.'.maminame as maminame_'.$brandid.","
		            .$alia.".birthday as birthday_".$brandid.","
		            .$alia.".babyisactive as babyisactive_".$brandid.","
		            .$alia.".issuccess as issuccess_".$brandid);
		            
		    $aliareport = "report_".$brandid;
		    $join_options[] = array('joinsql'=>" left join ".TelManReport::GetTable()." as $aliareport
		     on (".$alia.".reportid=$aliareport.reportid)", 
    			'select'=>
    				$aliareport.'.reportname as reportname_'.$brandid);
		}
	
		$filter_options = $this->GetFilterOption($filter_fields);
		$filter_options[] = DBHelper::FilterOption(null, 'telisactive', 1, '=');
		
		$yearmonth = $this->GetRequest()->GetInput('yearmonth','string');
		if($yearmonth==='')
		{
		    $yearmonth = date('Y-m');
		}
	    if($yearmonth)
	    {
	        $filter_options[] = array('sql'=>" FROM_UNIXTIME(".TelManTel::GetTable().".begintime,'%Y-%m') ='$yearmonth' ");
	    }
		
		$list = DBHelper::GetJoinRecordSet(TelManTel::GetTable(), 
			$join_options, $filter_options, $this->GetSortOption(),
			$this->_offset, $this->_perpage, $totalcount);
	    $success_option = array('0'=>'-','-1'=>'失败','1'=>'成功');
		foreach ($list as $key=>$rs)
		{
    		foreach ($brands as $brand)
    		{
    		    $brandid = $brand['brandid'];
    		    
    		    $randcolor = ($randcolor+79)%256;
    		    $color = OpenDev::WebRGB(200 , 215, $randcolor);
    		    $colorstyle = "background-color:$color;";
    		    if($rs['babyisactive_'.$brandid])
    		    {
    		        $list[$key]['issuccess_'.$brandid] = HtmlHelper::ListBox('issuccess_'.$brandid.'[]', 
    		            $rs['issuccess_'.$brandid], $success_option);
    		    }
    		    else 
    		    {
    		        $list[$key]['issuccess_'.$brandid] = '-';
    		    }
                
                //$table -> BindField('resultdesc_'.$brandid, '失败原因', 
            	//	'text', '', 'asc',"style='width:60px;$colorstyle'");
    		}
		}
			
	    $brandcount = count($brands);
		$widthplus = 90;
		$table = new HtmlTable("录入返回结果",'border');
		$table -> BindParam('width', $brandcount*(220+$widthplus)+180);
		$table -> BindField('telid', "", 'hidden');
		$table -> BindField('telid', "ID", '', 'list_pk', 'desc', 'nowrap');
		$table -> BindField('telnumber', self::ObjName, '', '', 'asc', 'nowrap');
		if($canedit_admin)
		{
		$table -> BindField('ownername', '所有者', '', '', 'asc', 'nowrap');
		}
		$randcolor = 78;
		foreach ($brands as $key=>$brand)
		{
		    $brandid = $brand['brandid'];
		    
		    $randcolor = ($randcolor+79)%256;
		    $color = OpenDev::WebRGB(200 , 215, $randcolor);
		    $colorstyle = "background-color:$color;";
		    
	        //$table -> BindField('babyisactive_'.$brandid, "上报", 
	        //	'bool','','',"style='width:30px;$colorstyle'");
            $table -> BindField('reportname_'.$brandid, "上报表单", 
            	'','','',"style='width:80px;$colorstyle'");
	        $table -> BindField('issuccess_'.$brandid, "结果", 
	        	'','','',"style='width:40px;$colorstyle'");
            
            //$table -> BindField('resultdesc_'.$brandid, '失败原因', 
        	//	'text', '', 'asc',"style='width:60px;$colorstyle'");
	        
            $table -> BindField('maminame_'.$brandid, $brand['brandname']."/名字", 
        		'', '', 'asc',"style='width:70px;$colorstyle'");
            
            $table -> BindField('birthday_'.$brandid, '生日', 
        		'date', '', 'asc',"style='width:60px;$colorstyle'");
		}
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
	    $acclist = Account::GetActiveAccounts(0);
		if($canedit_admin)
		{
		$table -> BindHeadbar(" | 按号码".HtmlHelper::Input('telnumber','','text'));
		$table -> BindHeadbar(" | 按所有者".HtmlHelper::ListBox('ownerid',
		    $ownerid, $acclist, 'userid','username','全部'));
		}
		$table -> BindHeadbar(" | 启用月份".HtmlHelper::ListBox('yearmonth',$yearmonth,
		    TelManTel::GetMonthComboArray()));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		
		$table -> BindFootbar(" | ".HtmlHelper::Button('保存返回结果', 'button',
			"onclick=\"if(!confirm('是否确定保存结果'))return false;form.action.value='doresult';form.submit();\""));
		$table -> BindFootbar(HtmlHelper::Input(FIELD_ACTION, 'none','hidden'));
		$table -> BindFootbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function dopresubmitAction()
	{
	    $this->CheckPermission(array('telmantel_presubmit','telmantel_admin'));
	    $canedit_presubmit = $this->CheckPermission('telmantel_presubmit', false);
	    $canedit_admin = $this->CheckPermission('telmantel_admin', false);

	    $telids = $this->GetRequest()->GetInput('telid',DT_ARRAY);
	    
	    $submitcount = 0;
    	$brands = TelManBrand::GetActiveBrands();
	    foreach($telids as $idkey=>$id)
	    {
	        $tel = new TelManTel($id);
	        if(!$tel->GetData())
	        {
	            continue;
	        }
	        if(!$canedit_admin && $tel->ownerid!=$this->GetAccount()->GetId())
	        {
	            $this->Message('没有权限操作此号码 '.$tel->telnumber);
	        }
    		foreach ($brands as $brand)
    		{
    		    $brandid = $brand['brandid'];
                $maminames = $this->GetRequest()->GetInput('maminame_'.$brandid, DT_ARRAY);
                $birthdays = $this->GetRequest()->GetInput('birthday_'.$brandid, DT_ARRAY);
                $babyisactives = $this->GetRequest()->GetInput('babyisactive_'.$brandid, DT_ARRAY);
                $maminame = trim($maminames[$idkey]);
                $birthday = OpenDev::StrToTime($birthdays[$idkey]);
                $birthday = intval($birthday);
                
    		    $sql = "select * from ".TelManBaby::GetTable()." where 
    		    	telid='".$tel->GetId()."' and brandid='$brandid'";
    		    $rs = DBHelper::GetQueryRecord($sql);
    		    if (!$rs)
    		    {
                    if(!$maminame)
                    {
                        continue;
                    }
    		        $baby = new TelManBaby();
    		        $baby->brandid = $brandid;
    		        $baby->telid = $tel->GetId();
    		        $baby->SaveData();
    		    }
    		    else 
    		    {
    		        $baby = new TelManBaby($rs);
    		    }
    		    if($baby->babyisactive)
    		    {
    		        continue;
    		    }
    		    $baby->PreSubmit($maminame, $birthday);
    		    $submitcount++;
    		}
	    }
	    $this->Message("已修改 $submitcount 条记录", LOCATION_REFERER);
	}
	
	public function dosubmitAction()
	{
	    $this->CheckPermission(array('telmantel_submit','telmantel_admin'));
	    $canedit_submit = $this->CheckPermission('telmantel_submit', false);
	    $canedit_admin = $this->CheckPermission('telmantel_admin', false);
	    $telids = $this->GetRequest()->GetInput('telid',DT_ARRAY);

	    $submitcount = 0;
    	$brands = TelManBrand::GetActiveBrands();
	    foreach($telids as $idkey=>$id)
	    {
	        $tel = new TelManTel($id);
	        if(!$tel->GetData())
	        {
	            continue;
	        }
    		foreach ($brands as $brand)
    		{
    		    $brandid = $brand['brandid'];
                $maminames = $this->GetRequest()->GetInput('maminame_'.$brandid, DT_ARRAY);
                $birthdays = $this->GetRequest()->GetInput('birthday_'.$brandid, DT_ARRAY);
                $babyisactives = $this->GetRequest()->GetInput('babyisactive_'.$brandid, DT_ARRAY);
                $maminame = trim($maminames[$idkey]);
                $birthday = intval(strtotime($birthdays[$idkey]));
                $babyisactive = intval($babyisactives[$idkey]);
    		    //TRACE("$maminame $birthday $babyisactive");
    		    $sql = "select * from ".TelManBaby::GetTable()." where 
    		    	telid='".$tel->GetId()."' and brandid='$brandid'";
    		    $rs = DBHelper::GetQueryRecord($sql);
    		    if (!$rs)
    		    {
                    if(!$maminame || !$birthday)
                    {
                        continue;
                    }
    		        $baby = new TelManBaby();
    		        $baby->brandid = $brandid;
    		        $baby->telid = $tel->GetId();
    		        $baby->ownerid = $tel->ownerid;
    		        $baby->SaveData();
    		    }
    		    else 
    		    {
                    if($babyisactive && (!$maminame || !$birthday))
                    {
                        continue;
                    }
    		        $baby = new TelManBaby($rs); 
    		    }
    		    //TRACE("  dd >$maminame< $birthday $babyisactive");
    		    $baby->Submit($babyisactive, $maminame, $birthday);
    		    
    		    $submitcount ++;
    		}
	    }
	    $this->Message("已修改上报记录 $submitcount 条", LOCATION_REFERER);
	}
	
	public function doresultAction()
	{
	    $this->CheckPermission(array('telmantel_result','telmantel_admin'));
	    $canedit_submit = $this->CheckPermission('telmantel_result', false);
	    $canedit_admin = $this->CheckPermission('telmantel_admin', false);
	    $telids = $this->GetRequest()->GetInput('telid',DT_ARRAY);

	    $submitcount = 0;
    	$brands = TelManBrand::GetActiveBrands();
	    foreach($telids as $idkey=>$id)
	    {
	        $tel = new TelManTel($id);
	        if(!$tel->GetData())
	        {
	            continue;
	        }
    		foreach ($brands as $brand)
    		{
    		    $brandid = $brand['brandid'];
                $issuccessarray = $this->GetRequest()->GetInput('issuccess_'.$brandid, DT_ARRAY);
                $resultdescarray = $this->GetRequest()->GetInput('resultdesc_'.$brandid, DT_ARRAY);
                
                $issuccess = intval($issuccessarray[$idkey]);
                $resultdesc = intval($resultdescarray[$idkey]);
                if($issuccess==0)
                {
                    continue;
                }
    		    //TRACE("$maminame $birthday $babyisactive");
    		    $sql = "select * from ".TelManBaby::GetTable()." where 
    		    	telid='".$tel->GetId()."' and brandid='$brandid'";
    		    $rs = DBHelper::GetQueryRecord($sql);
    		    if (!$rs || !$rs['babyisactive'] || $rs['reportid']==0)
    		    {
                   continue;
    		    }
    		    $baby = new TelManBaby($rs);
    		    //TRACE("  dd >$maminame< $birthday $babyisactive");
    		    $baby->Result($rs['reportid'], $issuccess, $resultdesc);
    		    
    		    $submitcount ++;
    		}
	    }
	    $this->Message("已修改上报记录 $submitcount 条", LOCATION_REFERER);
	}
	
	public function sheetAction()
	{
	    $this->CheckPermission(array('telmantel_report','telmantel_sheet','telmantel_admin'));
	    $canedit_report = $this->CheckPermission('telmantel_report', false);
	    $canedit_sheet = $this->CheckPermission('telmantel_sheet', false);
	    $canedit_admin = $this->CheckPermission('telmantel_admin', false);
	    $exportcsv = $this->GetRequest()->GetInput('exportcsv','int');
	    
	    $canedit = 1;
		$totalcount = 0;
		$transfertoid = $this->GetRequest()->GetInput('transfertoid','int');
		$ownerid = $this->GetRequest()->GetInput('ownerid','int');
		$filter_fields = array(
			'telnumber' => 'like%', 
			'transfertoid' => '==', 
		    'ownerid'=>'==',
			);
		//$join_options[] = array('table'=>TelManTel::GetTable(), 'alia'=>'ttel', 'left_field'=>'transfertoid', 
		//	'right_field'=>'telid','select'=>'telnumber as transfertonumber');
		$join_options[] = array('table'=>Account::GetTable(), 'left_field'=>'ownerid', 
			'right_field'=>'userid','select'=>'username as ownername');
		
		$brands = TelManBrand::GetActiveBrands();
		foreach ($brands as $key=>$brand)
		{
		    $alia = 'baby_'.$key;
		    $join_options[] = array('joinsql'=>" left join ".TelManBaby::GetTable()." as $alia
		     on (".TelManTel::GetTable().".telid=$alia.telid and $alia.brandid='".$brand['brandid']."')", 
    			'select'=>
    				$alia.'.babyid as babyid_'.$key.","
    				.$alia.'.maminame as maminame_'.$key.","
		            .$alia.".birthday as birthday_".$key.","
		            .$alia.".babyisactive as babyisactive_".$key.","
		            .$alia.".pickuptime as pickuptime_".$key.","
		            .$alia.".pickupdesc as pickupdesc_".$key.","
		            .$alia.".issuccess as issuccess_".$key);
		    $aliareport = "report_".$key;
		    $join_options[] = array('joinsql'=>" left join ".TelManReport::GetTable()." as $aliareport
		     on (".$alia.".reportid=$aliareport.reportid)", 
    			'select'=>
    				$aliareport.'.reportname as reportname_'.$key);
		}
		$filter_options = $this->GetFilterOption($filter_fields);
		if(!$canedit_report && !$canedit_admin)
		{
		$filter_options[] = DBHelper::FilterOption(null, 'ownerid', $this->GetAccount()->GetId(), '=');
		}
		$filter_options[] = DBHelper::FilterOption(null, 'telisactive', 1, '=');
		
		$yearmonth = $this->GetRequest()->GetInput('yearmonth','string');
		if($yearmonth==='')
		{
		    $yearmonth = date('Y-m');
		}
	    if($yearmonth)
	    {
	        $filter_options[] = array('sql'=>" FROM_UNIXTIME(".TelManTel::GetTable().".begintime,'%Y-%m') ='$yearmonth' ");
	    }
		$list = DBHelper::GetJoinRecordSet(TelManTel::GetTable(), 
			$join_options, $filter_options, $this->GetSortOption(),
			$this->_offset, $this->_perpage, $totalcount);
	    //global $sql; TRACE($sql);
		foreach($list as $rowkey=>$rs)
		{
		    if(!$canedit_admin && $canedit_report)
		    {
		        if($rs['ownername']!='02' && $rs['ownername']!='03' && $rs['ownername']!='04')
		        {
		            $list[$rowkey]['ownername'] = '27s';
		        }
		    } 
    		foreach ($brands as $key=>$brand)
    		{
    		    //$randcolor = ($randcolor+79)%256;
    		    // $color = OpenDev::WebRGB(200 , 215, $randcolor);
    		    $color = $color=='#c8d79e' ? '#c8d7ed' : '#c8d79e';
    		    $colorstyle = "background-color:$color;";
    		    
    		    if(!$rs['babyisactive_'.$key])
    		    {
    		        $list[$rowkey]['maminame_'.$key] = '';
    		        $list[$rowkey]['birthday_'.$key] = '';
    		    }
    		    if($rs['babyisactive_'.$key])
    		    {
    		    	if($rs['pickuptime_'.$key])
    		    	{
    		    		$list[$rowkey]['btn_'.$key] = date('m-d',$rs['pickuptime_'.$key]).$list[$rowkey]['pickupdesc_'.$key];
    		    	}
    		    	else
    		    	{
    		    		$url = urlhelper('action',$this->GetControllerId(), 'pickup', "&babyid=".$rs['babyid_'.$key]);
    		    		$list[$rowkey]['btn_'.$key] = HtmlHelper::Link('设为已接', $url,'','button');
    		    	}
    		    }
    		}
		}
		
	    $brandcount = count($brands);
	    $widthplus = 90;
		
		$table = new HtmlTable(self::ObjName."上报情况汇总",'border');
		$table -> BindParam('width', $brandcount*(250+ $widthplus)+280 );
		
		$table -> BindField('telid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('telnumber', self::ObjName, '', '', 'asc');
		if($canedit_admin || $canedit_report)
		{
		$table -> BindField('ownername', '所有者', '', '', 'asc',"style='width:50px;'");
		}
		$color = '';
		foreach ($brands as $key=>$brand)
		{
		    //$randcolor = ($randcolor+79)%256;
		    // $color = OpenDev::WebRGB(200 , 215, $randcolor);
		    $color = $color=='#c8d79e' ? '#c8d7ed' : '#c8d79e';
		    $colorstyle = "background-color:$color;";
		    
		    //if($canedit_admin)
		    //{
		    $table -> BindField('reportname_'.$key, "上报表单", 
		        '', '', 'asc',"nowrap style='width:80px;$colorstyle'");
//		    }
//		    else 
//		    {
//		    $table -> BindField('babyisactive_'.$key, "上报", 
//		        'bool', '', 'asc',"nowrap style='width:30px;$colorstyle'");
//		    }
		    //$table -> BindField('issuccess_'.$key, "结果", 
		   //     'bool', '', 'asc',"nowrap style='width:30px;$colorstyle'");
		    $table -> BindField('maminame_'.$key, $brand['brandname']."/名字", 
		    	'', '', 'asc',"nowrap style='width:70px;$colorstyle'");
		    $table -> BindField('birthday_'.$key, '生日', 
		    	'date', '', 'asc',"nowrap style='width:90px;$colorstyle'");
		    $table -> BindField('btn_'.$key, "状态"."<a name='brand_".$brand['brandid']."'></a>", 
		    	'', '', 'asc',"nowrap style='width:80px;$colorstyle'");
		}
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
	    $acclist = Account::GetActiveAccounts(0);
		$table -> BindHeadbar(" | 按号码".HtmlHelper::Input('telnumber','','text'));
	    if($canedit_admin)
		{
		$table -> BindHeadbar(" | 按所有者".HtmlHelper::ListBox('ownerid',
		    $ownerid, $acclist, 'userid','username','全部'));
		}
		$table -> BindHeadbar(" | 启用月份".HtmlHelper::ListBox('yearmonth',$yearmonth,
		    TelManTel::GetMonthComboArray()));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		
		$brandbtns = '';
		foreach($brands as $brand)
		{
			$brandbtns .= HtmlHelper::Link($brand['brandname'], '#brand_'.$brand['brandid'],'','button');
		}
		$table->BindHeadbar("<div style='padding:5px;padding-top:8px;'>快速导航: $brandbtns (点击快速跳转到品牌列)</div>");
		
		$table -> BindFootbar("".HtmlHelper::Button('导出Excel文件','button',"onclick=\"this.form.submit();\""));
		$table -> BindFootbar(HtmlHelper::Input('exportcsv', '1','hidden'));
		$table -> BindFootbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindFootbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		
		if($exportcsv)
		{
	        $filename = "上报号码汇总-[".date('Y-m-d').'].csv';
	        $filename = iconv('utf-8','gbk',$filename);
        	$tableval = $table->ParseTplValue();
		    $this->GetView()->Assign('fields', $table->GetFields());
		    $this->GetView()->Assign('rows', $tableval['_rows']);
		    $content = $this->GetView()->GetOutput('_table.csv');
			$content = iconv('utf-8','gbk',$content);
        	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
        	header("Cache-Control: no-cache, must-revalidate");
        	header("Pragma: no-cache");
        	header("Content-type: application/csv");	
        	header("Content-Length: ".strlen($content));
        	header('Content-Disposition: attachment; filename="'.$filename.'"');
			echo $content;
		    $this->GetApp()->AppExit();
		}
		else 
		{
		    $this->GetView()->Assign('table', $table);
		    $this->GetView()->Display('_data_table.htm');
		}
	}
	
	public function pickupAction()
	{
	    $this->CheckPermission(array('telmantel_report','telmantel_sheet','telmantel_admin'));
		$babyid = $this->GetRequest()->GetInput('babyid','int','没有指定已接电话的项目');
		$baby = new TelManBaby($babyid);
		if(!$baby->GetData())
		{
			$this->Message("没有找到要设置的项目");
		}	
		$baby->PickUp('已接');
		$this->Message("设置已接状态成功", LOCATION_REFERER);
	}
	
	public function resultsheetAction()
	{
	    $this->CheckPermission(array('telmantel_presubmit','telmantel_report','telmantel_admin'));
	    $canedit_report = $this->CheckPermission('telmantel_report', false);
	    $canedit_presubmit = $this->CheckPermission('telmantel_presubmit', false);
	    $canedit_admin = $this->CheckPermission('telmantel_admin', false);
	    
	    $canedit = 1;
		$totalcount = 0;
		$transfertoid = $this->GetRequest()->GetInput('transfertoid','int');
		$ownerid = $this->GetRequest()->GetInput('ownerid',DT_ARRAY);
		$owneridstr = '';
		if($ownerid)
		{
    		foreach($ownerid as $oid)
    		{
    		    $owneridstr .= $owneridstr ? ",".$oid : $oid;
    		}
		}
		
		$exportcsv = $this->GetRequest()->GetInput('exportcsv','int');
		$filter_fields = array(
			'telnumber' => 'like%', 
			'transfertoid' => '==', 
			);
		$filter_options = $this->GetFilterOption($filter_fields);
		if(!$canedit_report && !$canedit_admin)
		{
		    $filter_options[] = DBHelper::FilterOption(null, 'ownerid', $this->GetAccount()->GetId(), '=');
		}
		if($owneridstr)
		{
		    $filter_options[] = DBHelper::FilterOption(null, 'ownerid', $owneridstr, 'in');
		}
		$filter_options[] = DBHelper::FilterOption(null, 'telisactive', 1, '=');
		
		//$join_options[] = array('table'=>TelManTel::GetTable(), 'alia'=>'ttel', 'left_field'=>'transfertoid', 
		//	'right_field'=>'telid','select'=>'telnumber as transfertonumber');
		$join_options[] = array('table'=>Account::GetTable(), 'left_field'=>'ownerid', 
			'right_field'=>'userid','select'=>'username as ownername');
		
		$brands = TelManBrand::GetActiveBrands();
		foreach ($brands as $key=>$brand)
		{
		    $alia = 'baby_'.$key;
		    $join_options[] = array('joinsql'=>" left join ".TelManBaby::GetTable()." as $alia
		     on (".TelManTel::GetTable().".telid=$alia.telid and $alia.brandid='".$brand['brandid']."')", 
    			'select'=>
    				$alia.'.maminame as maminame_'.$key.","
		            .$alia.".birthday as birthday_".$key.","
		            .$alia.".resulttime as resulttime_".$key.","
		            .$alia.".babyisactive as babyisactive_".$key.","
		            .$alia.".issuccess as issuccess_".$key);
		    $aliareport = "report_".$key;
		    $join_options[] = array('joinsql'=>" left join ".TelManReport::GetTable()." as $aliareport
		     on (".$alia.".reportid=$aliareport.reportid)", 
    			'select'=>
    				$aliareport.'.reportname as reportname_'.$key);
		}
		$yearmonth = $this->GetRequest()->GetInput('yearmonth','string');
		if($yearmonth==='')
		{
		    $yearmonth = date('Y-m');
		}
	    if($yearmonth)
	    {
	        $filter_options[] = array('sql'=>" FROM_UNIXTIME(".TelManTel::GetTable().".begintime,'%Y-%m') ='$yearmonth' ");
	    }
		$list = DBHelper::GetJoinRecordSet(TelManTel::GetTable(), 
			$join_options, $filter_options, $this->GetSortOption(),
			$this->_offset, $this->_perpage, $totalcount);
	    foreach ($list as $key=>$rs)
		{
		    if($rs['ownername']!='02' && $rs['ownername']!='03' && $rs['ownername']!='04')
		    {
		        $list[$key]['ownername'] = '27s';
		    }
    		foreach ($brands as $brandkey=>$brand)
    		{
    		    $brandid = $brand['brandid'];
    		    
    		    $randcolor = ($randcolor+79)%256;
    		    $color = OpenDev::WebRGB(200 , 215, $randcolor);
    		    $colorstyle = "background-color:$color;";
    		    
    		    if(!$rs['babyisactive_'.$brandkey])
    		    {
    		        $list[$key]['maminame_'.$brandkey] = '';
    		        $list[$key]['birthday_'.$brandkey] = '';
    		        $list[$key]['issuccess_'.$brandkey] = '-';
    		        continue;
    		    }
    		    $color = '';
    		    switch($rs['issuccess_'.$brandkey])
    		    {
    		        case 1:
    		            $color= 'blue';
    		            $list[$key]['issuccess_'.$brandkey] = "<font color=blue><b>成功</b></font>";
    		            break;
    		        case -1:
    		            $color= 'red';
    		            $list[$key]['issuccess_'.$brandkey] = "<font color=red><b>失败</b></font>";
    		            break;
    		        default:
    		            $list[$key]['issuccess_'.$brandkey] = "暂无";
    		            break;
    		    }
    		    $rs['resulttime_'.$brandkey] = $rs['resulttime_'.$brandkey] 
    		        ? date('m月d日', $rs['resulttime_'.$brandkey]) : ''; 
    		    $list[$key]['resulttime_'.$brandkey] = 
    		    	"<font color=$color>".$rs['resulttime_'.$brandkey]."</font>";
                //$table -> BindField('resultdesc_'.$brandid, '失败原因', 
            	//	'text', '', 'asc',"style='width:60px;$colorstyle'");
    		}
		}
		
	    $brandcount = count($brands);
	    
		$widthplus = 90;
		
		$table = new HtmlTable(self::ObjName."返回结果汇总",'border');
		$table -> BindParam('width', $brandcount*(280+ $widthplus)+180);
		
		$table -> BindField('telid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('telnumber', self::ObjName, '', '', 'asc');
		if($canedit_admin || $canedit_report)
		{
		$table -> BindField('ownername', '所有者', '', '', 'asc',"style='width:50px;'");
		}
		$randcolor = 79;
		foreach ($brands as $key=>$brand)
		{
		    $randcolor = ($randcolor+79)%256;
		    $color = OpenDev::WebRGB(200 , 215, $randcolor);
		    $colorstyle = "background-color:$color;";
		    
		    $table -> BindField('issuccess_'.$key, "结果", 
		        '', '', 'asc',"nowrap style='width:30px;$colorstyle'");
		    
		    $table -> BindField('reportname_'.$key, "所属表单", 
		        '', '', 'asc',"nowrap style='width:80px;$colorstyle'");
		    
		    $table -> BindField('resulttime_'.$key, "返回时间", 
		        '', '', 'asc',"nowrap style='width:60px;$colorstyle'");
		    $table -> BindField('maminame_'.$key, $brand['brandname']."/名字", 
		    	'', '', 'asc',"nowrap style='width:70px;$colorstyle'");
		    $table -> BindField('birthday_'.$key, '生日', 
		    	'date', '', 'asc',"nowrap style='width:80px;$colorstyle'");
//		    $table -> BindField('resultdesc_'.$key, "原因", 
//		        '', '', 'asc',"nowrap style='width:60px;$colorstyle'");
		}
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
	    $acclist = Account::GetActiveAccounts(0);
		$table -> BindHeadbar(" | 按号码".HtmlHelper::Input('telnumber','','text'));
		if($canedit_admin)
		{
		$table -> BindHeadbar(" | 按所有者".HtmlHelper::CheckList('ownerid',
		    $ownerid, $acclist, 'userid','username',''));
		}
		$table -> BindHeadbar(" | 按启用日期:".HtmlHelper::ListBox('yearmonth',$yearmonth,
		    TelManTel::GetMonthComboArray()));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		
		$table -> BindFootbar("".HtmlHelper::Button('导出Excel文件','button',"onclick=\"this.form.submit();\""));
		$table -> BindFootbar(HtmlHelper::Input('exportcsv', '1','hidden'));
		$table -> BindFootbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindFootbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		
	    if($exportcsv)
		{
	        $filename = "返回结果汇总-[".date('Y-m-d').'].csv';
	        $filename = iconv('utf-8','gbk',$filename);
        	$tableval = $table->ParseTplValue();
		    $this->GetView()->Assign('fields', $table->GetFields());
		    $this->GetView()->Assign('rows', $tableval['_rows']);
		    //TRACE($tableval['_rows']);exit();
		    $content = $this->GetView()->GetOutput('_table.csv');
			$content = iconv('utf-8','gbk',$content);
        	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
        	header("Cache-Control: no-cache, must-revalidate");
        	header("Pragma: no-cache");
        	header("Content-type: application/csv");	
        	header("Content-Length: ".strlen($content));
        	header('Content-Disposition: attachment; filename="'.$filename.'"');
			echo $content;
		    $this->GetApp()->AppExit();
		}
		else 
		{
		    $this->GetView()->Assign('table', $table);
		    $this->GetView()->Display('_data_table.htm');
		}
	}
	
	public function listAction()
	{
	    $this->CheckPermission('');
	    $canedit_admin = $this->CheckPermission('telmantel_admin', false);
	    
		$totalcount = 0;
		$transfertoid = $this->GetRequest()->GetInput('transfertoid','int');
		$ownerid = $this->GetRequest()->GetInput('ownerid','int');
		$telisactive = $this->GetRequest()->GetInput('telisactive','string');
		$filter_fields = array(
			'telnumber' => 'like%', 
			'ownerid' => '==', 
			'transfertoid' => '==', 
			'telisactive' => '=', 
			);
		$join_options[] = array('table'=>TelManTel::GetTable(), 'alia'=>'ttel', 'left_field'=>'transfertoid', 
			'right_field'=>'telid','select'=>'telnumber as transfertonumber');
	    $join_options[] = array('table'=>SysCode::GetTable(), 'left_field'=>'telcompanyid', 
			'right_field'=>'syscodeid','select'=>'syscodename as telcompanyname, syscode as telcompanycode');
		
		$join_options[] = array('table'=>Account::GetTable(), 'left_field'=>'ownerid', 
			'right_field'=>'userid','select'=>'username as ownername');
		
		$filter_options = $this->GetFilterOption($filter_fields);
		if(!$canedit_admin)
		{
		    $filter_options[] = DBHelper::FilterOption(null, 'ownerid', $this->GetAccount()->GetId(), '=');
		}
	
		$yearmonth = $this->GetRequest()->GetInput('yearmonth','string');
		if($yearmonth==='')
		{
		    $yearmonth = date('Y-m');
		}
	    if($yearmonth)
	    {
	        $filter_options[] = array('sql'=>" FROM_UNIXTIME(".TelManTel::GetTable().".begintime,'%Y-%m') ='$yearmonth' ");
	    }
		$list = DBHelper::GetJoinRecordSet(TelManTel::GetTable(), 
			$join_options, $filter_options, $this->GetSortOption('telid','desc'),
			$this->_offset, $this->_perpage, $totalcount);
		foreach ($list as $key=>$rs)
		{
			$list[$key]['cp'] .= HtmlHelper::Link('设置', urlhelper('action',$this->GetControllerId(),'edit','&telid='.$rs['telid']));
			$list[$key]['cp'] .= " ".HtmlHelper::Link('删除', 
				urlhelper('action',$this->GetControllerId(),'delete','&telid='.$rs['telid']),
				'','',"onclick=\"if(!confirm('是否确定删除该纪录'))return false;\"");
		}
		
		$table = new HtmlTable(self::ObjName."列表",'border');
		$table -> BindField('telid', "telid", 'hidden');
		$table -> BindField('telid', "ID", '', 'list_pk', 'desc');
		if($canedit_admin)
		{
		$table -> BindField('telpriority', "优先级", 'text', '', 'asc','size=3');
		}
		$table -> BindField('ownername', "所有者", '', '', 'asc');
		$table -> BindField('telnumber', self::ObjName."", '', '', 'asc');
		//$table -> BindField('telcompanyname', "运营商", '', '', 'asc');
		//$table -> BindField('transfertonumber', "转接到", '', '', 'asc');
		$table -> BindField('begintime', "启用日期", 'date_picker', '', 'asc');
		$table -> BindField('balance', "话费余额", 'text', '', 'asc');
		$table -> BindField('telisactive', "启用", 'checkbool', '', 'asc');
		if(	$this->CheckPermission(self::ModuleRightEdit,false))
		{
		$table -> BindField('cp', "操作", '');
		
		$table -> BindHeadbar(HtmlHelper::Button('创建'.self::ObjName,'link',
			urlhelper('action',$this->GetControllerId(),'add'),''));
		}
		else
		{
		$table -> BindField('cp', "操作", 'cp', '','', 
			array('查看'=>array('', 'edit','telid')));
		}
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
	    $numberlist = TelManTel::GetNumberList($this->GetAccount()->GetId());
	    $acclist = Account::GetActiveAccounts(0);
	    $statusoption = array('0'=>'未启用','1'=>'启用');
		$table -> BindHeadbar(" | 按号码".HtmlHelper::Input('telnumber','','text'));
		//$table -> BindHeadbar(" 按转接到号码".HtmlHelper::ListBox('transfertoid',
		//    $transfertoid, $numberlist, 'telid','title','全部'));
		if($canedit_admin)
		{
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('ownerid',
		    $ownerid, $acclist, 'userid','username','全部所有者'));
		}
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('yearmonth',$yearmonth,
		    TelManTel::GetMonthComboArray()));
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('telisactive',
		    $telisactive, $statusoption, '','','全部状态'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		
		$table -> BindFootbar(HtmlHelper::Button('保存修改','submit'));
		$table -> BindFootbar(HtmlHelper::Input(FIELD_ACTION, 'dobatchedit','hidden'));
		$table -> BindFootbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function dobatcheditAction()
	{
	    $this->CheckPermission('');
	    $canedit_admin = $this->CheckPermission('telmantel_admin', false);
	    
	    $telids = $this->GetRequest()->GetInput('telid',DT_ARRAY);
	    $telprioritys = $this->GetRequest()->GetInput('telpriority',DT_ARRAY);
	    $telisactives = $this->GetRequest()->GetInput('telisactive',DT_ARRAY);
	    $begintimes = $this->GetRequest()->GetInput('begintime',DT_ARRAY);
	    $balances = $this->GetRequest()->GetInput('balance',DT_ARRAY);
	    foreach ($telids as $idkey=>$id)
	    {
	        $tel = new TelManTel($id);
	        if($tel->GetData())
	        {
	            if($canedit_admin)
		        {
	                $tel->telpriority = $telprioritys[$idkey];
		        }
	            $tel->telisactive = $telisactives[$idkey];
	            $begintime = OpenDev::StrToTime($begintimes[$idkey]);
	            $tel->begintime = intval($begintime);
	            $tel->balance = $balances[$idkey];
	            $tel->SaveData();
	        }
	    }
	    $this->Message('号码资料已更改',LOCATION_REFERER);
	}
	
	public function deleteAction()
	{
	    $this->CheckPermission('');
	    $telids = $this->GetRequest()->GetInput('telid',DT_ARRAY);
	    if(!$telids)
	    {
	        $telids[] = $this->GetRequest()->GetInput('telid',DT_INT,'没有指定要删除的'.self::ObjName);
	    }
	    $count = 0;
	    foreach ($telids as $id)
	    {
	        $tel = new TelManTel($id);
	        $tel->Delete();
	        $count ++;
	    }
	    $this->Message('已删除'.self::ObjName." ".$count."个",LOCATION_REFERER);
	}
	
	private function _bindFormFields(HtmlForm $form, &$rs)
	{
	    $canedit_admin = $this->CheckPermission('telmantel_admin', false);
	    
	    $numberlist = TelManTel::GetNumberList($this->GetAccount()->GetId(), $rs['telid']);
	    $acclist = Account::GetActiveAccounts(0);
		$form -> BindField('telnumber', self::ObjName, $rs['telnumber'],'text');
		//$form -> BindField('transfertoid', '转接到号码', $rs['transfertoid'],'radiolist',
		//    $numberlist,'telid','telnumber','无');
		//$form -> BindField('telcompanyid', '运营商', $rs['telcompanyid'],'radiolist',
		//    SysCode::GetCodeList(SysCode::TelManTelCom),'syscodeid','syscodename');
		
		if($canedit_admin)
		{
		$form -> BindField('ownerid', '所有者', $rs['ownerid'],'select',
		    $acclist,'userid','username');
		$form -> BindField('telpriority', '排号优先级', $rs['telpriority'],'text');
		}
		$form -> BindField('begintime', '启用日期', $rs['begintime'],'date_picker');
		$form -> BindField('balance', '话费余额', $rs['balance'],'text');
		$form -> BindField('desc', '备注', $rs['desc'],'textarea');
		$form -> BindField('telisactive', '是否启用', $rs['telisactive'],'check');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
	}
	
	public function editAction()
	{
	    $this->CheckPermission('');
		$canedit = $this->CheckPermission(self::ModuleRightEdit, false);
		
		$id = $this->GetRequest()->GetInput('telid', 'int', '没有指定要修改的'.self::ObjName);
		$obj = new TelManTel($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$form = new HtmlForm('修改'.self::ObjName);
		$form->SetReadOnly(!$canedit);
		$form -> BindField('telid', 'ID', $rs['telid'],'');
		
		$this->_bindFormFields($form, $rs);
		
		$form -> BindField('telid','',$rs['telid'],'hidden');
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function addAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$rs = array();
		$rs['telisactive'] = 1;
		$rs['telpriority'] = 1;
		$rs['begintime'] = $this->GetApp()->GetEnv('timestamp');
		$rs['ownerid'] = $this->GetAccount()->GetId();
		$form = new HtmlForm('新建'.self::ObjName);
		$this->_bindFormFields($form, $rs);
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	private function _parseFormInput()
	{
	    $canedit_admin = $this->CheckPermission('telmantel_admin', false);
		$input = array();
		$input['ownerid'] = $this->GetAccount()->GetId();
		$input['telnumber'] = $this->GetRequest()->GetInput('telnumber','string',self::ObjName.'必须填写');
		//$input['telcompanyid'] = $this->GetRequest()->GetInput('telcompanyid','int');
		//$input['transfertoid'] = $this->GetRequest()->GetInput('transfertoid','int');
		$input['balance'] = $this->GetRequest()->GetInput('balance','number');
		$begintime = $this->GetRequest()->GetInput('begintime','string');
		$input['begintime'] = intval(strtotime($begintime));
		$input['desc'] = $this->GetRequest()->GetInput('desc','string');
	    $input['telisactive'] = $this->GetRequest()->GetInput('telisactive','int');
	
		if($canedit_admin)
		{
		    $input['ownerid'] = $this->GetRequest()->GetInput('ownerid','int');
		    $input['telpriority'] = $this->GetRequest()->GetInput('telpriority','int');
	    }
	    else
	    {
	        $input['ownerid'] = $this->GetAccount()->GetId();
	    }
		return $input;
	}
	
	public function doaddAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$input = $this->_parseFormInput();
		$rs = DBHelper::GetSingleRecord(TelManTel::GetTable(), 'telnumber', $input['telnumber']);
		if ($rs)
		{
			$this->Message(self::ObjName."名称已存在  ".$input['telnumber'], LOCATION_BACK);
		}
		$referer = $this->GetRequest()->GetInput('referer');
		$object = new TelManTel();
		if($object->Insert($input))
		{
			$this->Message("新建".self::ObjName."成功", $referer);
		}
		else
		{
			$this->Message("新建".self::ObjName."失败", LOCATION_BACK);
		}
	}
	
	public function doeditAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$id = $this->GetRequest()->GetInput('telid', 'int', '没有指定要编辑的'.self::ObjName);
		$configrs = array();
		$input = $this->_parseFormInput($configrs);
		
		$object = new TelManTel($id);
		if (!$object->GetData())
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$count = DBHelper::GetRecordCount(TelManTel::GetTable(), 
			" telnumber='".$input['telnumber']."' 
			and telid<>'".$id."'");
		if ($count)
		{
			$this->Message("已存在同名记录  ".$input['telnumber'], LOCATION_BACK);
		}
		$referer = $this->GetRequest()->GetInput('referer');
		
		if($object->Update($input))
		{
			$this->Message("修改".self::ObjName."成功", $referer);
		}
		else
		{
			$this->Message("修改".self::ObjName."失败", LOCATION_BACK);
		}
	}
}