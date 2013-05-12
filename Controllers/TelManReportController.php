<?php

class TelManReportController extends ControllerBase
{
	const ObjName = "上报名单";
	
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		
		$this->CheckPermission();
	}
	
	private function _exportReport($id)
	{
	    $report = new TelManReport($id);
	    if(!$report->GetData())
	    {
	        $this->Message('没有找到名单 '.$id);
	    }
	    $brand = new TelManBrand($report->brandid);
	    $brand->GetData();
				
		$sql = "select t.*,bb.maminame,bb.birthday,bb.babyisactive,bb.reportorder,
			b.brandname, ac.username as ownername,t.telpriority
			from (((".TelManTel::GetTable()." t 
			left join ".TelManBaby::GetTable()." bb 
				on t.telid=bb.telid and bb.brandid='".$report->brandid."')
			left join ".TelManBrand::GetTable()." b 
				on bb.brandid=b.brandid )
			left join ".Account::GetTable()." ac on t.ownerid=ac.userid)
			where t.telisactive=1 
			and (bb.babyid is null or bb.reportid=0 or bb.reportid='".$report->reportid."')
			order by t.telpriority desc, bb.reportorder asc, telid asc
			";		// and (bb.babyisactive is null or bb.babyisactive=0)
		$list = DBHelper::GetQueryRecords($sql);
		$list = TelManBaby::SortTelNumber($list);
		$content = "电话号码,姓名,出生日期\r\n";
			    
		foreach($list as $key=>$rs)
		{
		    if(!$rs['babyisactive'])
		    {
		        continue;
		    }
		    $index = $key+1;
		    $telnumber = $rs['telnumber'];
		    $brandname = $rs['brandname'];
		    $maminame = $rs['maminame'];
		    $birthday = HtmlHelper::FormatDate($rs['birthday']);
		    $content.= "$telnumber,$maminame,$birthday\r\n";
		}
		$datestr = date('Y年m月d日', $report->reporttime?$report->reporttime:time());
	    $filename = "上报号码列表-[".$report->reportname.']['.$brand->brandname."]($datestr).csv";
	    $filename = iconv('utf-8','gbk',$filename);
	    $content = iconv('utf-8','gbk',$content);
    	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
    	header("Cache-Control: no-cache, must-revalidate");
    	header("Pragma: no-cache");
    	header("Content-type: application/octet-stream");
    	header("Content-Length: ".strlen($content));
    	header('Content-Disposition: attachment; filename="'.$filename.'"');
		echo $content;
	}
	
	public function exportAction()
	{
	    $this->CheckPermission(array('telmantel_report','telmantel_admin'));
	    $canedit_admin = $this->CheckPermission('telmantel_admin', false);
	    
	    $id = $this->GetRequest()->GetInput('reportid','int','没有指定要导出的名单');
	    	    
	    $this->_exportReport($id);
	    
		$this->GetApp()->AppExit();
	}
	
	public function saveexportAction()
	{
	    $this->CheckPermission(array('telmantel_report','telmantel_admin'));
	    $canedit_admin = $this->CheckPermission('telmantel_admin', false);
	    
	    $id = $this->GetRequest()->GetInput('reportid','int','没有指定要导出的名单');
	    
	    $this->_saveReport($id);
	    
	    $this->_exportReport($id);
	    
		$this->GetApp()->AppExit();
	}
	
	public function reportAction()
	{
	    $this->CheckPermission(array('telmantel_report','telmantel_admin'));
	    $canedit_admin = $this->CheckPermission('telmantel_admin', false);
	    
	    $id = $this->GetRequest()->GetInput('reportid','int','没有指定要上报的名单');
	    $report = new TelManReport($id);
	    if(!$report->GetData())
	    {
	        $this->Message('没有找到名单 '.$id);
	    }
	    if($report->issuccess)
	    {
	        $this->Message('名单 '.$report->reportname.' 已返回结果, 无法修改上报号码 ');
	    }
	    $brand = new TelManBrand($report->brandid);
	    $brand->GetData();
		
		$yearmonth = $this->GetRequest()->GetInput('yearmonth','string');
		if($yearmonth==='')
		{
		    $yearmonth = date('Y-m');
		}
	    if($yearmonth)
	    {
	        $yearmonthfilter = " and FROM_UNIXTIME(t.begintime,'%Y-%m') ='$yearmonth' ";
	    }
				
		$sql = "select t.*,bb.maminame,bb.birthday,bb.babyisactive,bb.reportorder,
			b.brandname, ac.username as ownername,t.telpriority
			from (((".TelManTel::GetTable()." t 
			left join ".TelManBaby::GetTable()." bb 
				on t.telid=bb.telid and bb.brandid='".$report->brandid."')
			left join ".TelManBrand::GetTable()." b 
				on bb.brandid=b.brandid )
			left join ".Account::GetTable()." ac on t.ownerid=ac.userid)
			where t.telisactive=1 
		    $yearmonthfilter
			and (bb.babyid is null or bb.reportid=0 or bb.reportid='".$report->reportid."')
			order by t.telpriority desc, bb.reportorder asc, telid asc
			";		// and (bb.babyisactive is null or bb.babyisactive=0)
		$list = DBHelper::GetQueryRecords($sql);
		$list = TelManBaby::SortTelNumber($list);
				
		$table = new HtmlTable("为名单<".$report->reportname." - ".$brand->brandname."> 选择上报号码 ");
		$table -> BindField('telid', "", 'hidden');
        $table -> BindField('babyisactive', "上报", 'checkbool','',''); 
		$table -> BindField('telnumber', "电话号码", '', '', '', 'nowrap');
		if($canedit_admin)
		{
		$table -> BindField('telpriority', "优先级", '', '', '', 'nowrap');
		$table -> BindField('ownername', '所有者', '', '', '', 'nowrap');
		}
		$table -> BindField('reportorder', "顺序", '', '', '', 'nowrap');
	    $brandid = $report->brandid;
	    
        //$table -> BindField('brandname', "品牌", '','','');       
        $table -> BindField('maminame', "名字", 'text', '', '');        
        $table -> BindField('birthday', '生日', 'date_picker', '', '');
        
		$table -> BindData($list);
		
		$table -> BindHeadbar(" 按启用月份显示号码:".HtmlHelper::ListBox('yearmonth',$yearmonth,
		    TelManTel::GetMonthComboArray()));
		$table -> BindHeadbar(HtmlHelper::Input('reportid', $id,'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('过滤','submit'));
		
		$table -> BindFootbar(" ".HtmlHelper::Button('上报选中号码', 'button',
			"onclick=\"if(!confirm('是否确定上报选中号码'))return false;form.action.value='doreport';form.submit();\""));
		$table -> BindFootbar(" ".HtmlHelper::Button('保存上报号码并导出Excel表格', 'button',
			"onclick=\"form.action.value='saveexport';form.submit();\""));
		$table -> BindFootbar(" ".HtmlHelper::Button('返回上页', 'button',
			"onclick=\"window.history.back()\""));
		$table -> BindFootbar(HtmlHelper::Input(FIELD_ACTION, 'none','hidden'));
		$table -> BindFootbar(HtmlHelper::Input('referer', $this->GetApp()->GetEnv('referer'),'hidden'));
		$table -> BindFootbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	private function _saveReport($id)
	{
	    $report = new TelManReport($id);
	    if(!$report->GetData())
	    {
	        $this->Message('没有找到名单 '.$id);
	    }
	    if($report->issuccess)
	    {
	        $this->Message('名单 '.$report->reportname.' 已返回结果, 无法修改上报号码 ');
	    }
	    		
	    $telids = $this->GetRequest()->GetInput('telid',DT_ARRAY);	    
        $maminames = $this->GetRequest()->GetInput('maminame', DT_ARRAY);
        $birthdays = $this->GetRequest()->GetInput('birthday', DT_ARRAY);
        $babyisactives = $this->GetRequest()->GetInput('babyisactive', DT_ARRAY);

	    $submitcount = 0;
	    $reportorder = 0;
	    foreach($telids as $idkey=>$id)
	    {
	        $tel = new TelManTel($id);
	        if(!$tel->GetData())
	        {
	            continue;
	        }
    		if(true)
    		{
    		    $brandid = $report->brandid;
                $maminame = trim($maminames[$idkey]);
                $birthday = OpenDev::StrToTime($birthdays[$idkey]);
                $birthday = intval($birthday);
                $babyisactive = intval($babyisactives[$idkey]);
        	    if($babyisactive && (!$maminame || !$birthday))
        	    {
        	        $this->Message('上报记录名字和生日必须填写');
        	    }
                
    		    $sql = "select * from ".TelManBaby::GetTable()." where 
    		    	telid='".$tel->GetId()."' and brandid='".$report->brandid."'
    		    	and (reportid=0 or reportid='".$report->reportid."')";
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
    		        $baby->maminame = $maminame;
    		        $baby->birthday = $birthday;
    		        $baby->SaveData();
    		    }
    		    else 
    		    {
    		        $baby = new TelManBaby($rs);
    		        if($maminame!=$baby->maminame || $birthday!=$baby->birthday)
    		        {
    		            $baby->issubmitchanged = 1;
    		        }
    		        $baby->maminame = $maminame;
    		        $baby->birthday = $birthday;
    		        $baby->SaveData();
    		    }
    		    if($babyisactive)
    		    {
    		        $reportorder++;
    		    }
    		    $baby->Submit($report->reportid, $babyisactive, $reportorder);
    		    
    		    $submitcount ++;
    		}
	    }
	    if(!$report->isreported || !$report->reporttime)
	    {
	        $report->isreported = 1;
	        $report->reporttime = time();
	        $report->SaveData();
	    }
	    return array('submitcount'=>$submitcount, 'reportorder'=>$reportorder);
	}
	
	public function doreportAction()
	{
	    $this->CheckPermission(array('telmantel_report','telmantel_admin'));
	    $canedit_admin = $this->CheckPermission('telmantel_admin', false);
	    
	    $id = $this->GetRequest()->GetInput('reportid','int','没有指定要上报的名单');
	    
	    $count = $this->_saveReport($id);
	    $referer = $this->GetRequest()->GetInput('referer');
	    $this->Message("已修改记录 ".$count['submitcount']." 条, 上报记录  ".$count['reportorder']." 条", $referer);
	}
	
	public function resultAction()
	{
	    $this->CheckPermission(array('telmantel_report','telmantel_admin'));
	    $canedit_admin = $this->CheckPermission('telmantel_admin', false);
	    
	    $id = $this->GetRequest()->GetInput('reportid','int','没有指定要操作的名单');
	    $report = new TelManReport($id);
	    if(!$report->GetData())
	    {
	        $this->Message('没有找到名单 '.$id);
	    }
	    if(!$report->isreported)
	    {
	        $this->Message('名单 '.$report->reportname.' 未上报, 无法录入返回结果 ');
	    }
	    $brand = new TelManBrand($report->brandid);
	    $brand->GetData();
				
		$sql = "select t.*,
			bb.babyid,bb.maminame,bb.birthday,bb.babyisactive,bb.reportorder,bb.issuccess,bb.resultdesc,
			b.brandname, ac.username as ownername,t.telpriority
			from (((".TelManTel::GetTable()." t 
			left join ".TelManBaby::GetTable()." bb 
				on t.telid=bb.telid and bb.brandid='".$report->brandid."')
			left join ".TelManBrand::GetTable()." b 
				on bb.brandid=b.brandid )
			left join ".Account::GetTable()." ac on t.ownerid=ac.userid)
			where t.telisactive=1 
			and (bb.reportid='".$report->reportid."')
			order by t.telpriority desc, bb.reportorder asc, telid asc
			";		// and (bb.babyisactive is null or bb.babyisactive=0)
		$list = DBHelper::GetQueryRecords($sql);
		$list = TelManBaby::SortTelNumber($list);
				
		$table = new HtmlTable("为名单<".$report->reportname." - ".$brand->brandname."> 录入返回结果 ");
		$table -> BindField('babyid', "", 'hidden');
        $table -> BindField('babyisactive', "已上报", 'bool','',''); 
		$table -> BindField('telnumber', "电话号码", '', '', '', 'nowrap');
		if($canedit_admin)
		{
		$table -> BindField('telpriority', "优先级", '', '', '', 'nowrap');
		$table -> BindField('ownername', '所有者', '', '', '', 'nowrap');
		}
		$table -> BindField('reportorder', "顺序", '', '', '', 'nowrap');
	    $brandid = $report->brandid;
	    
        //$table -> BindField('brandname', "品牌", '','','');       
        $table -> BindField('maminame', "名字", '', '', '');        
        $table -> BindField('birthday', '生日', 'date', '', '');
        $table -> BindField('issuccess', '成功', 'checkbool', '', '');
        $table -> BindField('resultdesc', '结果备注', 'text', '', '');
        
		$table -> BindData($list);
		
		$table -> BindFootbar(" ".HtmlHelper::Button('保存返回结果', 'button',
			"onclick=\"if(!confirm('是否确定保存返回结果'))return false;form.action.value='doresult';form.submit();\""));
		$table -> BindFootbar(" ".HtmlHelper::Button('返回上页', 'button',
			"onclick=\"window.history.back()\""));
		$table -> BindFootbar(HtmlHelper::Input(FIELD_ACTION, 'none','hidden'));
		$table -> BindFootbar(HtmlHelper::Input('referer', $this->GetApp()->GetEnv('referer'),'hidden'));
		$table -> BindFootbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function doresultAction()
	{
	    $this->CheckPermission(array('telmantel_report','telmantel_admin'));
	    $canedit_admin = $this->CheckPermission('telmantel_admin', false);
	    
	    $id = $this->GetRequest()->GetInput('reportid','int','没有指定要操作的名单');
	    $report = new TelManReport($id);
	    if(!$report->GetData())
	    {
	        $this->Message('没有找到名单 '.$id);
	    }
	    if(!$report->isreported)
	    {
	        $this->Message('名单 '.$report->reportname.' 未上报, 无法录入返回结果 ');
	    }
	    
	    $babyids = $this->GetRequest()->GetInput('babyid',DT_ARRAY);	    
        $issuccessarray = $this->GetRequest()->GetInput('issuccess', DT_ARRAY);
        $resultdescarray = $this->GetRequest()->GetInput('resultdesc', DT_ARRAY);

	    $submitcount = 0;
	    foreach($babyids as $idkey=>$id)
	    {
	        $baby = new TelManBaby($id);
	        if(!$baby->GetData())
	        {
	            continue;
	        }
            $resultdesc = trim($resultdescarray[$idkey]);
            $issuccess = intval($issuccessarray[$idkey]);
		    $baby->Result($report->reportid, $issuccess, $resultdesc);
    		
		    $submitcount ++;
	    }
	    if(!$report->isresulted || !$report->resulttime)
	    {
	        $report->isresulted = 1;
	        $report->resulttime = time();
	        $report->SaveData();
	    }
	    $referer = $this->GetRequest()->GetInput('referer');
	    $this->Message("已保存返回结果 $submitcount 条", $referer);
	}
	
	public function listAction()
	{
	    $this->CheckPermission(array('telmantel_report','telmantel_admin'));
	    
		$totalcount = 0;
		$brandid = $this->GetRequest()->GetInput('brandid','int');
		$filter_fields = array(
			'reportname' => '%like%', 
			'brandid' => '==', 
			'reportuserid' => '==', 
			'resultuserid' => '==', 
			);
		
		$join_options[] = array('table'=>Account::GetTable(), 'left_field'=>'reportuserid', 
			'right_field'=>'userid','select'=>'username as reportusername');
		$join_options[] = array('table'=>TelManBrand::GetTable(), 'left_field'=>'brandid', 
			'right_field'=>'brandid','select'=>'brandname as brandname');
		
		$list = DBHelper::GetJoinRecordSet(TelManReport::GetTable(), 
			$join_options, $this->GetFilterOption($filter_fields), 
			$this->GetSortOption(TelManReport::GetTable().'.reportid','desc'),
			$this->_offset, $this->_perpage, $totalcount);
		
		$table = new HtmlTable(self::ObjName."列表",'border');
		$table -> BindField('reportid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('reportname', self::ObjName."名称", '', '', 'asc');
		$table -> BindField('brandname', "品牌", '', '', 'asc');
		$table -> BindField('isreported', "已上报", 'bool', '', 'asc');
		$table -> BindField('reporttime', "上报日期", 'date', '', 'asc');
//		$table -> BindField('isresulted', "已返回", 'bool', '', 'asc');
//		$table -> BindField('resulttime', "返回结果日期", 'date', '', 'asc');
		
		$table -> BindField('cp', "操作", 'cp', '','', 
			array(
			'选择上报号码'=>array('', 'report','reportid'),
			'导出Excel'=>array('', 'export','reportid'),
			'修改'=>array('', 'edit','reportid'),
			'删除'=>array('', 'delete','reportid')));		
		$table -> BindHeadbar(HtmlHelper::Button('创建'.self::ObjName,'link',
			urlhelper('action',$this->GetControllerId(),'add', "&brandid=$brandid"),''));
		
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		$brands = TelManBrand::GetActiveBrands();
		$table -> BindHeadbar(" | 按名字".HtmlHelper::Input('reportname','','text'));
		$table -> BindHeadbar(" 按品牌".HtmlHelper::ListBox('brandid',
		    $brandid, $brands, 'brandid','brandname','全部'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function deleteAction()
	{
	    $this->CheckPermission(array('telmantel_report','telmantel_admin'));
	    $canadmin = $this->CheckPermission('telmantel_admin', false);
	    
	    $reportids = $this->GetRequest()->GetInput('reportid',DT_ARRAY);
	    if(!$reportids)
	    {
	        $reportids[] = $this->GetRequest()->GetInput('reportid',DT_INT,'没有指定要删除的'.self::ObjName);
	    }
	    $count = 0;
	    foreach ($reportids as $id)
	    {
	        $tel = new TelManReport($id);
	        $tel->GetData();
	        if($canadmin)
	        {
	        	echo "AdminDelete";
	            $tel->AdminDelete();
	        }
	        else 
	        {
	        	echo "Delete";
	            $tel->Delete();
	        }
	        $count ++;
	    }
	    $this->Message('已删除'.self::ObjName." ".$count."个 $canadmin",LOCATION_REFERER);
	}
	
	private function _bindFormFields(HtmlForm $form, &$rs)
	{
		$form -> BindField('reportname', self::ObjName."名称", $rs['reportname'],'text');
		$form -> BindField('isreported', '已上报', $rs['isreported'],'hidden');
		$form -> BindField('reporttime', '上报日期', $rs['reporttime'],'hidden');
		$form -> BindField('isresulted', '已返回结果', $rs['isresulted'],'hidden');
		$form -> BindField('resulttime', '返回结果日期', $rs['resulttime'],'hidden');
		$form -> BindField('desc', '备注', $rs['desc'],'textarea');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
	}
	
	public function editAction()
	{
	    $this->CheckPermission(array('telmantel_report','telmantel_admin'));
		
		$id = $this->GetRequest()->GetInput('reportid', 'int', '没有指定要修改的'.self::ObjName);
		$obj = new TelManReport($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$brand = new TelManBrand($rs['brandid']);
		$brand->GetData();
		$form = new HtmlForm('修改'.self::ObjName);
		$form -> BindField('reportid', 'ID', $rs['reportid'],'');
		
		$form -> BindField('brandname', '所属品牌', $brand->brandname,'');
		$this->_bindFormFields($form, $rs);
		
		$form -> BindField('brandid','',$rs['brandid'],'hidden');
		$form -> BindField('reportid','',$rs['reportid'],'hidden');
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function addAction()
	{
	    $brandid = $this->GetRequest()->GetInput('brandid','int');
	    $this->CheckPermission(array('telmantel_report','telmantel_admin'));
		
		$rs = array();
		$rs['brandid'] = $brandid;
		$form = new HtmlForm('新建'.self::ObjName);
	    $brandlist = TelManBrand::GetActiveBrands();
		$form -> BindField('brandid', '所属品牌', $rs['brandid'],'radiolist',
		    $brandlist,'brandid','brandname');
		$this->_bindFormFields($form, $rs);
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	private function _parseFormInput()
	{
		$input = array();
		$input['reportname'] = $this->GetRequest()->GetInput('reportname','string',self::ObjName.'必须填写');
		$input['brandid'] = $this->GetRequest()->GetInput('brandid','int',"品牌必须填写");
//		$input['isreported'] = $this->GetRequest()->GetInput('brandid','int');
//		$input['isresulted'] = $this->GetRequest()->GetInput('brandid','int');
//		$date = $this->GetRequest()->GetInput('reporttime','string');
//		$input['reporttime'] = intval(strtotime($date));
//		$date = $this->GetRequest()->GetInput('reporttime','string');
//		$input['reporttime'] = intval(strtotime($date));
		$input['desc'] = $this->GetRequest()->GetInput('desc','string');
		return $input;
	}
	
	public function doaddAction()
	{
	    $this->CheckPermission(array('telmantel_report','telmantel_admin'));
		
		$configrs = array();
		$input = $this->_parseFormInput($configrs);
		
		$referer = $this->GetRequest()->GetInput('referer');
		$object = new TelManReport();
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
	    $this->CheckPermission(array('telmantel_report','telmantel_admin'));
		
		$id = $this->GetRequest()->GetInput('reportid', 'int', '没有指定要编辑的'.self::ObjName);
		$configrs = array();
		$input = $this->_parseFormInput();
		
		$object = new TelManReport($id);
		if (!$object->GetData())
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
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
	
	public function indexAction()
	{
	    $this->CheckPermission(array('telmantel_report','telmantel_admin'));
		$left_url = urlhelper('action','TelManBrand', 'tree',
			"&urlcontroller=".$this->GetControllerId()."&urlaction=list");
		$right_url = urlhelper('action',$this->GetControllerId(), 'list');
		$this->GetView()->Assign('left_url', $left_url);
		$this->GetView()->Assign('right_url', $right_url);
		$this->GetView()->Display('_tree_frame.htm');
		$this->GetApp()->AppExit();
	}
}