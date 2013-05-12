<?php

class FWLogController extends ControllerBase
{
	const ModuleRight='log_view';
	const ModuleRightEdit='log_edit';
	const ObjName = "日志";
	
	public function Init($app, $request, $view)
	{
		$this->_perpage = 20;
		parent::Init($app, $request, $view);
		
		$this->CheckPermission(self::ModuleRight);
	}
	
	public function medialistAction()
	{
		$totalcount = 0;
		$filter_fields = array(
			'logop' => '=', 
			'logparam'=>'%like%',
			'source'=>'%like%',
			'target'=>'%like%',
			'targetid'=>'==',
			'sourceid'=>'==',
			);
		$filter_options = $this->GetFilterOption($filter_fields);
		$logop = $this->GetRequest()->GetInput('logop','string');
		$source = $this->GetRequest()->GetInput('source','string');
		$logusername = $this->GetRequest()->GetInput('logusername','string');
		if ($logusername)
		{
			$filter_options[] = DBHelper::FilterOption(Account::GetTable(), 'username', "%$logusername%", 'like');
		}
		$join_options[] = DBHelper::JoinOption(Account::GetTable(), '', 
			FWLog::GetLogTable(FWLog::LogMedia), 'loguserid', 'userid', 'username as logusername');
		$list = DBHelper::GetJoinRecordSet(FWLog::GetLogTable(FWLog::LogMedia), 
			$join_options, $filter_options, $this->GetSortOption('logid','desc'),
			$this->_offset, $this->_perpage, $totalcount);
	
		foreach($list as $key=>$rs)
		{
			$list[$key]['cp'] = HtmlHelper::Link('详细', 
				urlhelper('action',$this->GetControllerId(),'view',"&logtype=".$rs['logtype']."&logid=".$rs['logid']));
		}
		
		$table = new HtmlTable("媒体文件日志",'border');
		$table -> BindField('logid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('source', "媒体文件", '', '', 'asc');
		$table -> BindField('logop', "操作", '', '', 'asc');
		$table -> BindField('logparam', "参数", '', '', '');
		$table -> BindField('target', "关联对象", '', '', 'asc');
		$table -> BindField('logusername', "操作者", '', '', 'asc');
		$table -> BindField('logtime', "时间", 'datetime', '', 'asc');		
		$table -> BindField('cp', "操作", '');
		
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		$oparr = array(FWLog::ActionAdd,FWLog::ActionUpdate,FWLog::ActionDelete,
			FWLog::ActionAudit,FWLog::ActionDisAudit,FWLog::ActionMove,FWLog::ActionPlay);
		$ops = array_combine($oparr,$oparr);
		
		$table -> BindHeadbar(" 按媒体文件名".HtmlHelper::Input('source',$source,'text'));
		$table -> BindHeadbar(" 按操作者".HtmlHelper::Input('logusername',$logusername,'text'));
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('logop',$logop,$ops,'','','按操作'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function devicelistAction()
	{
		$totalcount = 0;
		$filter_fields = array(
			'logop' => '=', 
			'logparam'=>'%like%',
			'source'=>'%like%',
			'target'=>'%like%',
			'targetid'=>'==',
			'sourceid'=>'==',
			);
		$filter_options = $this->GetFilterOption($filter_fields);
		$logop = $this->GetRequest()->GetInput('logop','string');
		$source = $this->GetRequest()->GetInput('source','string');
		$logusername = $this->GetRequest()->GetInput('logusername','string');
		if ($logusername)
		{
			$filter_options[] = DBHelper::FilterOption(Account::GetTable(), 'username', "%$logusername%", 'like');
		}
		$join_options[] = DBHelper::JoinOption(Account::GetTable(), '', 
			FWLog::GetLogTable(FWLog::LogDevice), 'loguserid', 'userid', 'username as logusername');
		$list = DBHelper::GetJoinRecordSet(FWLog::GetLogTable(FWLog::LogDevice), 
			$join_options, $filter_options, $this->GetSortOption('logid','desc'),
			$this->_offset, $this->_perpage, $totalcount);
	
		foreach($list as $key=>$rs)
		{
			$list[$key]['cp'] = HtmlHelper::Link('详细', 
				urlhelper('action',$this->GetControllerId(),'view',"&logtype=".$rs['logtype']."&logid=".$rs['logid']));
		}
		
		$table = new HtmlTable("设备日志",'border');
		$table -> BindField('logid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('source', "设备", '', '', 'asc');
		$table -> BindField('logop', "操作", '', '', 'asc');
		$table -> BindField('logparam', "参数", '', '', '');
		$table -> BindField('target', "关联对象", '', '', 'asc');
		$table -> BindField('logusername', "操作者", '', '', 'asc');
		$table -> BindField('logtime', "时间", 'datetime', '', 'asc');		
		$table -> BindField('cp', "操作", '');
		
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		$oparr = array(FWLog::ActionAdd,FWLog::ActionUpdate,FWLog::ActionDelete,
			FWLog::ActionAudit,FWLog::ActionDisAudit,FWLog::ActionMove,FWLog::ActionPlay);
		$ops = array_combine($oparr,$oparr);
		
		$table -> BindHeadbar(" 按设备名".HtmlHelper::Input('source',$source,'text'));
		$table -> BindHeadbar(" 按操作者".HtmlHelper::Input('logusername',$logusername,'text'));
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('logop',$logop,$ops,'','','按操作'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function publishlistAction()
	{
		$totalcount = 0;
		$filter_fields = array(
			'logop' => '=', 
			'logparam'=>'%like%',
			'source'=>'%like%',
			'target'=>'%like%',
			'targetid'=>'==',
			'sourceid'=>'==',
			);
		$filter_options = $this->GetFilterOption($filter_fields);
		$logop = $this->GetRequest()->GetInput('logop','string');
		$source = $this->GetRequest()->GetInput('source','string');
		$target = $this->GetRequest()->GetInput('target','string');
		$logusername = $this->GetRequest()->GetInput('logusername','string');
		if ($logusername)
		{
			$filter_options[] = DBHelper::FilterOption(Account::GetTable(), 'username', "%$logusername%", 'like');
		}
		$join_options[] = DBHelper::JoinOption(Account::GetTable(), '', 
			FWLog::GetLogTable(FWLog::LogPublish), 'loguserid', 'userid', 'username as logusername');
		$list = DBHelper::GetJoinRecordSet(FWLog::GetLogTable(FWLog::LogPublish), 
			$join_options, $filter_options, $this->GetSortOption('logid','desc'),
			$this->_offset, $this->_perpage, $totalcount);
	
		foreach($list as $key=>$rs)
		{
			$list[$key]['cp'] = HtmlHelper::Link('详细', 
				urlhelper('action',$this->GetControllerId(),'view',"&logtype=".$rs['logtype']."&logid=".$rs['logid']));
		}
		
		$table = new HtmlTable("节目发布日志",'border');
		$table -> BindField('logid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('source', "逻辑组", '', '', 'asc');
		$table -> BindField('logop', "操作", '', '', 'asc');
		$table -> BindField('logparam', "参数", '', '', '');
		$table -> BindField('target', "关联对象", '', '', 'asc');
		$table -> BindField('logusername', "操作者", '', '', 'asc');
		$table -> BindField('logtime', "时间", 'datetime', '', 'asc');		
		$table -> BindField('cp', "操作", '');
		
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		$oparr = array(FWLog::ActionAdd,FWLog::ActionUpdate,FWLog::ActionDelete,
			FWLog::ActionAudit,FWLog::ActionDisAudit,FWLog::ActionMove,FWLog::ActionPlay);
		$ops = array_combine($oparr,$oparr);
		
		$table -> BindHeadbar(" 按节目包名".HtmlHelper::Input('source',$source,'text'));
		$table -> BindHeadbar(" 按逻辑组名".HtmlHelper::Input('target',$target,'text'));
		$table -> BindHeadbar(" 按操作者".HtmlHelper::Input('logusername',$logusername,'text'));
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('logop',$logop,$ops,'','','按操作'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function securitylistAction()
	{
		$totalcount = 0;
		$filter_fields = array(
			'logop' => '=', 
			'logparam'=>'%like%',
			'source'=>'%like%',
			'target'=>'%like%',
			'targetid'=>'==',
			'sourceid'=>'==',
			);
		$filter_options = $this->GetFilterOption($filter_fields);
		$logop = $this->GetRequest()->GetInput('logop','string');
		$source = $this->GetRequest()->GetInput('source','string');
		$logusername = $this->GetRequest()->GetInput('logusername','string');
		if ($logusername)
		{
			$filter_options[] = DBHelper::FilterOption(Account::GetTable(), 'username', "%$logusername%", 'like');
		}
		$join_options[] = DBHelper::JoinOption(Account::GetTable(), '', 
			FWLog::GetLogTable(FWLog::LogSecurity), 'loguserid', 'userid', 'username as logusername');
		$list = DBHelper::GetJoinRecordSet(FWLog::GetLogTable(FWLog::LogSecurity), 
			$join_options, $filter_options, $this->GetSortOption('logid','desc'),
			$this->_offset, $this->_perpage, $totalcount);
		
		foreach($list as $key=>$rs)
		{
			$list[$key]['cp'] = HtmlHelper::Link('详细', 
				urlhelper('action',$this->GetControllerId(),'view',"&logtype=".$rs['logtype']."&logid=".$rs['logid']));
		}
		
		$table = new HtmlTable("账户安全日志",'border');
		$table -> BindField('logid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('source', "用户名", '', '', 'asc');
		$table -> BindField('logop', "操作", '', '', 'asc');
		$table -> BindField('logparam', "参数", '', '', '');
		$table -> BindField('target', "关联对象", '', '', 'asc');
		$table -> BindField('logusername', "操作者", '', '', 'asc');
		$table -> BindField('logtime', "时间", 'datetime', '', 'asc');		
		$table -> BindField('cp', "操作", '');
		
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		$oparr = array(FWLog::ActionAdd,FWLog::ActionUpdate,FWLog::ActionDelete,
			FWLog::ActionAudit,FWLog::ActionDisAudit,FWLog::ActionMove,FWLog::ActionPlay);
		$ops = array_combine($oparr,$oparr);
		
		$table -> BindHeadbar(" 按用户名".HtmlHelper::Input('source',$source,'text'));
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('logop',$logop,$ops,'','','按操作'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	private function _bindFormFields(HtmlForm $form, &$rs)
	{
		
		
	}
	
	public function viewAction()
	{		
		$logtype = $this->GetRequest()->GetInput('logtype', 'string', '没有指定要查看的日志类型');
		$id = $this->GetRequest()->GetInput('logid', 'int', '没有指定要查看的'.self::ObjName);
		
		$log = new FWLog($logtype);
		$rs = $log->GetLog($id);
		if (!$rs)
		{
			$this->Message("没有找到要查看的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$form = new HtmlForm('查看'.self::ObjName);
		$form -> BindField('logid', 'ID', $rs['logid'],'');
		$form -> BindField('source', '日志对象', $rs['source'],'');
		$form -> BindField('sourceid', '日志对象ID', $rs['sourceid'],'');
		$form -> BindField('logop', '操作', $rs['logop'],'');
		$form -> BindField('logparam', '参数', $rs['logparam'],'');
		$form -> BindField('target', '关联对象', $rs['target'],'');
		$form -> BindField('targetid', '关联对象ID', $rs['targetid'],'');
		$form -> BindField('logtime', '记录时间', $rs['logtime'],'datetime');
		$logacc = new Account($rs['loguserid']);
		$logacc->GetData();
		$form -> BindField('logusername', '操作者', $logacc->username,'');
		$detail = unserialize($rs['logtext']);
		if($detail)
		{
			$str = var_export($detail, true);
		}
		else
		{
			echo $str = $rs['logtext'];
		}
		$form -> BindField('logtext', '详细记录', "<pre>".$str."</pre>",'');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'',$rs['logtype']."list",'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
		
		$form -> BindField('logtype','',$rs['logtype'],'hidden');
		$form -> BindField('logid','',$rs['logid'],'hidden');
		
		$form-> BindButton(HtmlHelper::Button('返回','button',"onclick=\"window.history.back();\""));
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function addAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$rs = array();
		$form = new HtmlForm('新建'.self::ObjName);
		$this->_bindFormFields($form, $rs);
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	private function _parseFormInput(&$configrs)
	{
		$input = array();
		$input['logicalgroupname'] = $this->GetRequest()->GetInput('logicalgroupname','string','角色名必须填写');
		$input['isactive'] = $this->GetRequest()->GetInput('isactive','int');
	
		$inherite = $this->GetRequest()->GetInput('config_inherite','int');
		if(!$inherite)
		{
			$configrs['playtimebegin'] = $this->GetRequest()->GetInput('playtimebegin','int');
			$configrs['playtimeend'] = $this->GetRequest()->GetInput('playtimeend','int');
			$configrs['downloadtime'] = $this->GetRequest()->GetInput('downloadtime','int');
			$configrs['sidebarpos'] = $this->GetRequest()->GetInput('sidebarpos','varchar');
			$configrs['reskeepdays'] = $this->GetRequest()->GetInput('reskeepdays','int');
		}
		return $input;
	}
	
	public function doaddAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$configrs = array();
		$input = $this->_parseFormInput($configrs);
		$rs = DBHelper::GetSingleRecord(LogicalGroup::GetTable(), 'logicalgroupname', $input['logicalgroupname']);
		if ($rs)
		{
			$this->Message("分组名已存在  ".$input['logicalgroupname'], LOCATION_BACK);
		}
		$referer = $this->GetRequest()->GetInput('referer');
		$object = new LogicalGroup();
		if($object->Insert($input))
		{
			if (count($configrs)>0)
			{
				$config = new PlayerConfig(PlayerConfig::TargetGroup, $object->GetId());
				$config -> playtimebegin = $configrs['playtimebegin'];
				$config -> playtimeend = $configrs['playtimeend'];
				$config -> downloadtime = $configrs['downloadtime'];
				$config -> reskeepdays = $configrs['reskeepdays'];
				$config -> sidebarpos = $configrs['sidebarpos'];
				$config->SaveData();
			}
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
		
		$id = $this->GetRequest()->GetInput('logicalgroupid', 'int', '没有指定要编辑的分组');
		$configrs = array();
		$input = $this->_parseFormInput($configrs);
		
		$object = new LogicalGroup($id);
		if (!$object->GetData())
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$count = DBHelper::GetRecordCount(LogicalGroup::GetTable(), 
			" logicalgroupname='".$input['logicalgroupname']."' 
			and logicalgroupid<>'".$id."'");
		if ($count)
		{
			$this->Message("已存在同名分组  ".$input['logicalgroupname'], LOCATION_BACK);
		}
		$referer = $this->GetRequest()->GetInput('referer');
		
		if($object->Update($input))
		{
			if (count($configrs)>0)
			{
				$config = new PlayerConfig(PlayerConfig::TargetGroup, $object->GetId());
				$config -> playtimebegin = $configrs['playtimebegin'];
				$config -> playtimeend = $configrs['playtimeend'];
				$config -> downloadtime = $configrs['downloadtime'];
				$config -> reskeepdays = $configrs['reskeepdays'];
				$config -> sidebarpos = $configrs['sidebarpos'];
				$config->SaveData();
				$config->SaveXml();
			}
			else
			{
				$config = new PlayerConfig(PlayerConfig::TargetGroup, $object->GetId());
				$config->Delete();
				$config = new PlayerConfig(PlayerConfig::TargetGroup, $object->GetId());
				$config -> SaveXml();
			}
			$this->Message("修改".self::ObjName."成功", $referer);
		}
		else
		{
			$this->Message("修改".self::ObjName."失败", LOCATION_BACK);
		}
	}
}