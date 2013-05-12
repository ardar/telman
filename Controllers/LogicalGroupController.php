<?php

class LogicalGroupController extends ControllerBase
{
	const ModuleRight='logicalgroup_view';
	const ModuleRightEdit='logicalgroup_edit';
	const ObjName = "逻辑分组";
	
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		
		$this->CheckPermission(self::ModuleRight);
	}
	
	public function listAction()
	{
		$totalcount = 0;
		$filter_fields = array(
			'logicalgroupname' => '%like%', 
			);
		$list = DBHelper::GetJoinRecordSet(LogicalGroup::GetTable(), 
			null, $this->GetFilterOption($filter_fields), $this->GetSortOption(),
			$this->_offset, $this->_perpage, $totalcount);
		
		$table = new HtmlTable(self::ObjName."列表",'border');
		$table -> BindField('logicalgroupid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('logicalgroupname', "逻辑分组名", '', '', 'asc');
		$table -> BindField('isactive', "是否启用");
		if(	$this->CheckPermission(self::ModuleRightEdit,false))
		{
		$table -> BindField('cp', "操作", 'cp', '','', 
			array(
			'设置'=>array('', 'edit','logicalgroupid'),
			'删除'=>array('', 'delete','logicalgroupid')));
		
		$table -> BindHeadbar(HtmlHelper::Button('创建'.self::ObjName,'link',
			urlhelper('action',$this->GetControllerId(),'add'),''));
		}
		else
		{
		$table -> BindField('cp', "操作", 'cp', '','', 
			array('查看'=>array('', 'edit','logicalgroupid')));
		}
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		$table -> BindHeadbar(" 按分组名".HtmlHelper::Input('logicalgroupname','','text'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	private function _bindFormFields(HtmlForm $form, &$rs)
	{
		$form -> BindField('logicalgroupname', '逻辑组名', $rs['logicalgroupname'],'text');
		$form -> BindField('isactive', '是否启用', $rs['isactive'],'check');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
		
		if(true)
		{
		$keepdays = array(1,2,3,4,5,6,7,15,30,45,60,90,180);
		$keepdays = array_combine($keepdays,$keepdays);
		$posset = array('top'=>'顶部','bottom'=>'底部 ','left'=>'左侧','right'=>'右侧');
		$config = PlayerConfig::GetConfig($rs['logicalgroupid'], 0);
		$crs = $config->GetData();
		$beginctrl = HtmlHelper::HourPicker('playtimebegin', $crs['playtimebegin']).'点 ';
		$endctrl = HtmlHelper::HourPicker('playtimeend', $crs['playtimeend']).'点 ';
		$downctrl = HtmlHelper::HourPicker('downloadtime', $crs['downloadtime']).'点 ';
		
		$checked = $crs['target']==PlayerConfig::TargetGroup ? '' : "checked";
		$checkbox = HtmlHelper::Input('config_inherite',
			1,	'checkbox',
			" $checked onclick=\"if(this.checked)configblock.disabled='true';else configblock.disabled=false;\" ");
		$form -> BindField('', '播放设置', '<label>'.$checkbox.'继承全局设置</label>','');
		$disabledblock = $crs['target']==PlayerConfig::TargetGroup ? '' : 'disabled=true';
		$form -> BindField('tbody_begin', '', "<tbody id=configblock $disabledblock>",'html');
		$form -> BindField('playtimebegin', '开始播放时间', $beginctrl,'');
		$form -> BindField('playtimeend', '结束播放时间', $endctrl,'');
		$form -> BindField('dowloadtime', '节目下载时间', $downctrl,'');
		$form -> BindField('reskeepdays', '媒体保存天数', $crs['reskeepdays'],'select',$keepdays);
		$form -> BindField('sidebarpos', '浮动菜单位置', $crs['sidebarpos'],'select',$posset,'','','');
		$form -> BindField('tbody_end', '', '</tbody>','html');
		}
	}
	
	public function editAction()
	{
		$canedit = $this->CheckPermission(self::ModuleRightEdit, false);
		
		$id = $this->GetRequest()->GetInput('logicalgroupid', 'int', '没有指定要修改的'.self::ObjName);
		$obj = new LogicalGroup($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$form = new HtmlForm('修改'.self::ObjName);
		$form->SetReadOnly(!$canedit);
		$form -> BindField('logicalgroupid', 'ID', $rs['logicalgroupid'],'');
		
		$this->_bindFormFields($form, $rs);
		
		$form -> BindField('logicalgroupid','',$rs['logicalgroupid'],'hidden');
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