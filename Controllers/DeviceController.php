<?php
class DeviceController extends ControllerBase
{
	const ModuleRightView='device_view';
	const ModuleRightEdit='device_edit';
	const ObjName = "设备";
	
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
	}
	
	public function listAction()
	{
		$this->CheckPermission(array(self::ModuleRightView,self::ModuleRightEdit));
		$canedit = $this->CheckPermission(self::ModuleRightEdit,false);
		
		$totalcount = 0;
		$join_options[] = array(
			'table'=>Device::GetTable(), 'alia'=>'p', 
			'left_field'=>'parentid','right_field'=>'deviceid','select'=>'devicename as parentname');
		$join_options[] = array(
			'table'=>LogicalGroup::GetTable(),
			 'on_field'=>'logicalgroupid','select'=>'logicalgroupname');
		$filter_fields = array(
			'devicename' => '%like%', 
			'devicemac' => '%like%',
			'parentid' => '==',
			'devicetype' => '=',
			'logicalgroupid' => '==',
			);
		$parentid = $this->GetRequest()->GetInput('parentid','int');
		$devicetype = $this->GetRequest()->GetInput('devicetype','string');
		$logicalgroupid = $this->GetRequest()->GetInput('logicalgroupid','int');
		$list = DBHelper::GetJoinRecordSet(Device::GetTable(), 
			$join_options, $this->GetFilterOption($filter_fields), 
			$this->GetSortOption('devicetype,devicename','asc'),
			$this->_offset, $this->_perpage, $totalcount);
		foreach ($list as $key => $rs)
		{
			$url = urlhelper('action',$this->GetControllerId(),'edit',"&deviceid=".$rs['deviceid']);
			$list[$key]['devicename'] = HtmlHelper::Link($rs['devicename'], $url);
			$list[$key]['devicetype'] = HtmlHelper::Image('images/'.$rs['devicetype'].'.png');
		}
			
		$logicalgroups = DBHelper::GetRecords(LogicalGroup::GetTable());
		$repeaters = Device::BuildRepeaterComboList();
		
		if($parentid>0)
		{	
			$parent = new Device($parentid);
			$prs = $parent->GetData();
			if($prs)
			{				
				$parentpath = $parent->GetParentPath('Device','list','parentid');
				$actionname = $canedit ? "修改" :"查看";
				$form = new HtmlForm($actionname.' 中继服务器'.self::ObjName);
				$form -> SetReadOnly(!$canedit);
				$form -> BindField('deviceid', 'ID', $prs['deviceid'],'');
				$form -> BindField('devicetype', self::ObjName.'类型', 
					Device::$DeviceTypes[$prs['devicetype']],'');
				
				$this->_bindFormFields($form, $prs);
				$form -> BindField(FIELD_ACTION,'','doedit','hidden');
				$this->GetView()->Assign('form', $form);
				$this->GetView()->Display('_data_form.htm');
			}
		}
		
		$table = new HtmlTable(self::ObjName."列表",'border');
		$table -> BindField('deviceid', "ID", 'checkbox', '', 'desc');
		$table -> BindField('deviceid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('devicetype', "类型", '', '', 'asc');
		$table -> BindField('devicename', self::ObjName."名称", '', '', 'asc');
		$table -> BindField('devicemac', "Mac地址", '', '', 'asc');
		$table -> BindField('logicalgroupname', "逻辑组", '', '', 'asc');
		$table -> BindField('parentname', "父中继", '', '', 'asc');
		$table -> BindField('deviceisactive', "启用", 'bool', '', 'asc');
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		if($canedit)
		{
			$table -> BindField('cp', "操作", 'cp', '','', 
				array(
				'设置'=>array('','edit','deviceid'),
				'删除'=>array('','delete','deviceid','',"onclick=\"if(!confirm('是否确定删除该设备?'))return false;\"")));
			$table -> BindHeadbar(" ".HtmlHelper::Button('创建'.self::ObjName,'link',
				urlhelper('action',$this->GetControllerId(),'add',"&parentid=$parentid&logicalgroupid=$logicalgroupid")));
		}
		
		$table -> BindToolbar($parentpath);
		
		$table -> BindHeadbar(" | 设备名".HtmlHelper::Input('devicename','','text'));
		$table -> BindHeadbar(" Mac".HtmlHelper::Input('devicemac','','text'));
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('devicetype',$devicetype,Device::$DeviceTypes,
			'','',array(''=>'按设备类型','0'=>'全部')));
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('logicalgroupid',$logicalgroupid,$logicalgroups,
			'logicalgroupid','logicalgroupname',array(''=>'按逻辑组','0'=>'未分配')));
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('parentid',$parentid,$repeaters,
			'deviceid','title',array(''=>'按所属中继','0'=>'未分配')));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		
		if($canedit)
		{
		$table -> BindFootbar("将选中项:");
		$table -> BindFootbar(HtmlHelper::Input(FIELD_ACTION, 'none','hidden'));
		$table -> BindFootbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindFootbar(HtmlHelper::Button('分配到逻辑组', 'button',
			"onclick=\"if(!confirm('是否确认分配选中的条目到逻辑组'))return false;form.action.value='assigngroup';form.submit();\""));
		$table -> BindFootbar(" ".HtmlHelper::ListBox('logicalgroupid',
			$logicalgroupid,$logicalgroups, 'logicalgroupid','logicalgroupname','无'));
		$table -> BindFootbar(" | 或将选中项:".HtmlHelper::Button('分配到中继', 'button',
			"onclick=\"if(!confirm('是否确认分配选中的条目到中继'))return false;form.action.value='assignrepeater';form.submit();\""));
		$table -> BindFootbar(" ".HtmlHelper::ListBox('parentid',
			$parentid,$repeaters,'deviceid','title','无'));
		}
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function deleteAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$id = $this->GetRequest()->GetInput('deviceid','int','没有指定要删除的'.self::ObjName);
		
		$obj = new Device($id);
		if (!$obj->GetData())
		{
			$this->Message("没有找到要删除的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$rs = $obj->GetData();
		if($obj -> Delete())
		{
			FWLog::DeviceLog(FWLog::ActionDelete, true, $rs, $this->GetAccount()->GetId());
			$this->Message("删除".self::ObjName."成功", LOCATION_REFERER);
		}
		else
		{
			$this->Message("删除 ".self::ObjName."失败", LOCATION_BACK);
		}
	}
	
	public function assigngroupAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$logicalgroupid = $this->GetRequest()->GetInput('logicalgroupid', 'int');
	    $group = new LogicalGroup($logicalgroupid);
		if($logicalgroupid>0 && ! $group->GetData())
		{
			$this->Message("没有找到逻辑组 ".$logicalgroupid, LOCATION_BACK);
		}
		
		$ids = $this->GetRequest()->GetInput('deviceid', DT_ARRAY);
		if (!$ids)
		{
			$ids[] = $this->GetRequest()->GetInput('deviceid','int','没有指定要分配到逻辑组的'.self::ObjName);
		}
		$icount= 0;
		foreach ($ids as $id)
		{
			$obj = new Device($id);
			if (!$obj->GetData())
			{
				$this->Message("没有找到要分配的".self::ObjName." ".$id, LOCATION_REFERER);
			}
			if($obj->devicetype==Device::DeviceRepeater)
			{
			    continue;
			}
			if(!$obj -> AssignToGroup($logicalgroupid))
			{
				$this->Message('分配'.self::ObjName."到逻辑组失败", LOCATION_REFERER);
			}
			FWLog::DeviceLog(FWLog::ActionAssign, true, $obj->GetData(), $this->GetAccount()->GetId(),
			   $group->logicalgroupname, $group->GetId());
			$icount++;
		}
		$this->Message('分配 '.$icount." 个 ".self::ObjName."到逻辑组成功", LOCATION_REFERER);
	}
	
	public function assignrepeaterAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$repeaterid = $this->GetRequest()->GetInput('parentid', 'int');
		$repeater = new Device($repeaterid);
		if($repeaterid>0 && !$repeater->GetData())
		{
			$this->Message("没有找到中继服务器 ".$repeaterid, LOCATION_BACK);
		}
		$ids = $this->GetRequest()->GetInput('deviceid', DT_ARRAY);
		if (!$ids)
		{
			$ids[] = $this->GetRequest()->GetInput('deviceid','int','没有指定要分配到中继服务器的'.self::ObjName);
		}
		$icount = 0;
		foreach ($ids as $id)
		{
			$obj = new Device($id);
			if (!$obj->GetData())
			{
				$this->Message("没有找到要分配的".self::ObjName." ".$id, LOCATION_REFERER);
			}
			if(!$obj -> AssignToRepeater($repeaterid))
			{
				$this->Message('分配'.self::ObjName."到中继服务器失败", LOCATION_REFERER);
			}
			FWLog::DeviceLog(FWLog::ActionAssign, true, $obj->GetData(), $this->GetAccount()->GetId(),
			   $repeater->devicename, $repeater->GetId());
			$icount++;
		}
		$this->Message('分配 '.$icount." 个 ".self::ObjName."到中继服务器成功", LOCATION_REFERER);
	}
	
	private function _bindFormFields(HtmlForm $form, &$rs)
	{
		$logicalgroups = DBHelper::GetRecords(LogicalGroup::GetTable());
		$repeaters = Device::BuildRepeaterComboList();
		$form -> BindField('devicename', self::ObjName.'名(*)', $rs['devicename'],'text');
		$form -> BindField('devicemac', 'MAC地址(*)', $rs['devicemac'],'text');
		if($rs['devicetype']!=Device::DeviceRepeater)
		{
			$form -> BindField('logicalgroupid', '所属逻辑组', $rs['logicalgroupid'],
				'select', $logicalgroups, 'logicalgroupid','logicalgroupname','无');
		}
		$form -> BindField('parentid', '所属中继服务器', $rs['parentid'],
			'select', $repeaters, 'deviceid','title','无');
		$form -> BindField('deviceip', 'IP地址', $rs['deviceip'],'text');
		$form -> BindField('deviceddr', '部署地址', $rs['deviceaddr'],'text');
		$form -> BindField('deviceisactive', '是否启用', $rs['deviceisactive'],'check');	
		$form -> BindField('deviceid','',$rs['deviceid'],'hidden');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');	
		
		if($rs['devicetype']==Device::DeviceTerminal)
		{
		$keepdays = array(1,2,3,4,5,6,7,15,30,45,60,90,180);
		$keepdays = array_combine($keepdays,$keepdays);
		$posset = array('top'=>'顶部','bottom'=>'底部 ','left'=>'左侧','right'=>'右侧');
		$config = PlayerConfig::GetConfig($rs['logicalgroupid'], $rs['deviceid']);
		$crs = $config->GetData();
		$beginctrl = HtmlHelper::HourPicker('playtimebegin', $crs['playtimebegin']).'点 ';
		$endctrl = HtmlHelper::HourPicker('playtimeend', $crs['playtimeend']).'点 ';
		$downctrl = HtmlHelper::HourPicker('downloadtime', $crs['downloadtime']).'点 ';
		
		$checked = $crs['target']==PlayerConfig::TargetDevice ? '' : "checked";
		$checkbox = HtmlHelper::Input('config_inherite',
			1,	'checkbox',
			" $checked onclick=\"if(this.checked)configblock.disabled='true';else configblock.disabled=false;\" ");
		$form -> BindField('', '播放设置', '<label>'.$checkbox.'继承上级设置</label>','');
		$disabledblock = $crs['target']!=PlayerConfig::TargetDevice ? 'disabled=true' : '';
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
		$this->CheckPermission(array(self::ModuleRightEdit,self::ModuleRightView));
		
		$id = $this->GetRequest()->GetInput('deviceid', 'int', '没有指定要修改的'.self::ObjName);
		$obj = new Device($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		
		$logicalgroups = DBHelper::GetRecords(LogicalGroup::GetTable());
		$repeaters = Device::BuildRepeaterComboList();
		
		if($obj->parentid>0)
		{
			$parent = new Device($obj->parentid);
			$parentpath = $parent->GetParentPath('Device','list','parentid');
		}
		$canedit = $this->CheckPermission(self::ModuleRightEdit,false);
		$actionname = $canedit ? "修改": "查看";
		$form = new HtmlForm($actionname.self::ObjName);
		$form -> SetReadOnly(!$canedit);
		$form -> BindField('deviceid', 'ID', $rs['deviceid'],'');
		$form -> BindField('devicetype', self::ObjName.'类型', Device::$DeviceTypes[$rs['devicetype']],'');
		
		$form -> BindField('', '中继路径', $parentpath,'');
		
		$this->_bindFormFields($form, $rs);
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function addAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$rs = array();
		$rs['parentid'] = $this->GetRequest()->GetInput('parentid','int');
		$rs['logicalgroupid'] = $this->GetRequest()->GetInput('logicalgroupid','int');
		
		
		$form = new HtmlForm('新建'.self::ObjName);
		
		$form -> BindField('devicetype', self::ObjName.'类型(*)', $rs['devicetype'],
			'radiolist', Device::$DeviceTypes);
		
		$this->_bindFormFields($form, $rs);
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	private function _parseFormInput(&$configrs)
	{
		$input = array();
		
		$input['devicename'] = $this->GetRequest()->GetInput('devicename','string', self::ObjName.'名必须填写');
		$input['deviceaddr'] = $this->GetRequest()->GetInput('deviceaddr','string');
		$input['devicemac'] = $this->GetRequest()->GetInput('devicemac','string','Mac地址必须填写');
		$input['devicemac'] = strtoupper($input['devicemac']);
		$input['deviceip'] = $this->GetRequest()->GetInput('deviceip','string');
		$input['parentid'] = $this->GetRequest()->GetInput('parentid','int');
		$input['logicalgroupid'] = $this->GetRequest()->GetInput('logicalgroupid','int');
		$input['deviceisactive'] = $this->GetRequest()->GetInput('deviceisactive','int');

		if (strlen($input['devicemac'])!=17 || count(explode('-',$input['devicemac']))!=6)
		{
			$this->Message("Mac地址格式不正确,请写成 AA-BB-CC-DD-EE-FF 形式");
		}
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
		$input['devicetype'] = $this->GetRequest()->GetInput('devicetype','string', self::ObjName.'类型必须填写');
		if (!array_key_exists($input['devicetype'], Device::$DeviceTypes))
		{
			throw new PCException('所选设备类型不正确'.$input['devicetype']);
		}
		if($input['devicetype']==Device::DeviceRepeater)
		{
			$input['logicalgroupid'] = null;
		}
		$rs = DBHelper::GetSingleRecord(Device::GetTable(), 'devicename', $input['devicename']);
		if ($rs)
		{
			$this->Message("设备名已存在  ".$input['devicename'], LOCATION_BACK);
		}
		$rs = DBHelper::GetSingleRecord(Device::GetTable(), 'devicemac', $input['devicemac']);
		if ($rs)
		{
			$this->Message("设备Mac地址已存在  ".$input['devicemac'], LOCATION_BACK);
		}
		$referer = $this->GetRequest()->GetInput('referer');
		$object = new Device();
		if($object->Insert($input))
		{
			FWLog::DeviceLog(FWLog::ActionAdd, true, $object->GetData(), $this->GetAccount()->GetId());
			$backurl = urlhelper('action',$this->GetControllerId(),'list',"&parentid=".$input['parentid']);
			$this->Message("新建".self::ObjName."成功", $backurl);
		}
		else
		{
			$this->Message("新建".self::ObjName."失败", LOCATION_BACK);
		}
	}
	
	public function doeditAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$id = $this->GetRequest()->GetInput('deviceid', 'int', '没有指定要修改的'.self::ObjName);
		$configrs = array();
		$input = $this->_parseFormInput($configrs);	
		if($input['devicetype']==Device::DeviceRepeater)
		{
			$input['logicalgroupid'] = null;
		}
		
		$object = new Device($id);
		if (!$object->GetData())
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$referer = $this->GetRequest()->GetInput('referer');
		if($object->Update($input))
		{
			if ($object->devicetype==Device::DeviceTerminal)
			{
				if (count($configrs)>0)
				{
					$config = new PlayerConfig(PlayerConfig::TargetDevice, $object->GetId());
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
					$config = new PlayerConfig(PlayerConfig::TargetDevice, $object->GetId());
					$config->Delete();
					$config = new PlayerConfig(PlayerConfig::TargetDevice, $object->GetId());
					$config -> SaveXml();
				}
			}
			FWLog::DeviceLog(FWLog::ActionUpdate, true, $object->GetData(), $this->GetAccount()->GetId());
			$backurl = urlhelper('action',$this->GetControllerId(),'list',"&parentid=".$input['parentid']);
			$this->Message("修改".self::ObjName."成功", $backurl);
		}
		else
		{
			$this->Message("修改".self::ObjName."失败", LOCATION_BACK);
		}
	}
	
	public function indexAction()
	{
		$this->CheckPermission(self::ModuleRightView);
		
		$left_url = urlhelper('action',$this->GetControllerId(), 'left');
		$right_url = urlhelper('action',$this->GetControllerId(), 'list',"&parentid=0");
		$this->GetView()->Assign('left_url', $left_url);
		$this->GetView()->Assign('right_url', $right_url);
		$this->GetView()->Display('_tree_frame.htm');
		$this->GetApp()->AppExit();
	}
	
	public function leftAction()
	{
		$this->CheckPermission(self::ModuleRightView);
		
		$this->GetView()->Assign('tree1_url', 
			urlhelper('action', $this->GetControllerId(), 'devicetree', "&parentid=0"));
		$this->GetView()->Assign('tree2_url', 
			urlhelper('action', $this->GetControllerId(), 'grouptree', "&parentid=0"));
		$this->GetView()->Display('terminal_left.htm');
		$this->GetApp()->AppExit();
	}
	
	public function devicetreeAction()
	{
		$this->CheckPermission(self::ModuleRightView);
		
		$parentid = $this->GetRequest()->GetInput('parentid','int');
		$this->GetView()->Assign('treeview_url', 
			urlhelper('action', $this->GetControllerId(), 'devicexml', "&parentid=$parentid"));
		$this->GetView()->Display('_tree_view.htm');
		$this->GetApp()->AppExit();
	}
	
	public function devicexmlAction()
	{
		$this->CheckPermission(self::ModuleRightView);
		
		$parentid = $this->GetRequest()->GetInput('parentid','int');
		$repeaters = DBHelper::GetRecords(Device::GetTable(), 'parentid', $parentid, 'devicetype,devicename', 'asc');
		foreach($repeaters as $key=>$rs)
		{
			$repeaters[$key]['id'] = $rs['deviceid'];
			$repeaters[$key]['text'] = $rs['devicename'];
			$repeaters[$key]['title'] = $rs['devicemac'];
			$repeaters[$key]['target'] = 'main_right';
			if($rs['devicetype']==Device::DeviceRepeater)
			{
				$repeaters[$key]['xml'] = htmlentities(urlhelper('action',$this->GetControllerId(),
					$this->GetActionId(),"&parentid=".$rs['deviceid']));
				$repeaters[$key]['href'] = htmlentities(urlhelper('action',$this->GetControllerId(),
					'list',"&parentid=".$rs['deviceid']));
				$repeaters[$key]['image'] = './images/repeater.png';
			}
			else
			{
				$repeaters[$key]['xml'] = null;
				$repeaters[$key]['href'] = htmlentities(urlhelper('action',$this->GetControllerId(),
					'edit',"&deviceid=".$rs['deviceid']));
				$repeaters[$key]['image'] = './images/terminal.png';
			}
		}
		$index = count($repeaters);
		if ($parentid==0)
		{
			$root['id'] = 0;
			$root['text'] = 'Root';
			$root['title'] = 'Root';
			$root['xml'] = null;
			$root['href'] = htmlentities(urlhelper('action',$this->GetControllerId(),
				'list',"&parentid=0"));
			$root['target'] = 'main_right';
			$root['image'] = './images/home.png';
		}
		ob_clean();
		header("Content-Type: text/xml");
		$this->GetView()->Assign('root', $root);
		$this->GetView()->Assign('list', $repeaters);
		$this->GetView()->Display('_tree_view.xml');
		$this->GetApp()->AppExit();
	}

	public function grouptreeAction()
	{
		$this->CheckPermission(self::ModuleRightView);
		
		$this->GetView()->Assign('treeview_url', 
			urlhelper('action', $this->GetControllerId(), 'groupxml'));
		$this->GetView()->Display('_tree_view.htm');
		$this->GetApp()->AppExit();
	}
	
	public function groupxmlAction()
	{
		$this->CheckPermission(self::ModuleRightView);
		
		$groupid = $this->GetRequest()->GetInput('logicalgroupid','int');
		if($groupid!=0)
		{
			if($groupid==-1)
			{
				$filter_options[] = DBHelper::FilterOption(null, 'logicalgroupid', 0, '=');
				$filter_options[] = DBHelper::FilterOption(null, 'devicetype', Device::DeviceTerminal, '=');
				$sort_options[] = Device::GetPK()." asc";
				$icount = 0;
				$terminals = DBHelper::GetJoinRecordSet(Device::GetTable(), null, 
					$filter_options, $sort_options, 0, 999999, $icount);
				//$terminals = DBHelper::GetRecords(Device::GetTable(), 'logicalgroupid', 0, Device::GetPK(), 'asc');
			}
			else 
			{
				$terminals = DBHelper::GetRecords(Device::GetTable(), 'logicalgroupid', $groupid, Device::GetPK(), 'asc');
			}
			foreach($terminals as $key=>$rs)
			{
				$rs['id'] = $rs['deviceid'];
				$rs['text'] = $rs['devicename'];
				$rs['title'] = $rs['devicemac'];
				$rs['xml'] = null;
				$rs['href'] = htmlentities(urlhelper('action',$this->GetControllerId(),
					'edit',"&deviceid=".$rs['deviceid']));
				$rs['target'] = 'main_right';
				$rs['image'] = 'images/terminal.png';
				$list[] = $rs;
			}
		}
		else
		{
			$list = DBHelper::GetRecords(LogicalGroup::GetTable(),'','','logicalgroupid', 'asc');
			foreach($list as $key=>$rs)
			{
				$list[$key]['id'] = $rs['logicalgroupid'];
				$list[$key]['text'] = $rs['logicalgroupname'];
				$list[$key]['title'] = $rs['logicalgroupname'];
				$list[$key]['xml'] = htmlentities(urlhelper('action',$this->GetControllerId(),
				$this->GetActionId(),"&logicalgroupid=".$rs['logicalgroupid']));
				$list[$key]['href'] = htmlentities(urlhelper('action',$this->GetControllerId(),'list', 
					"&logicalgroupid=".$rs['logicalgroupid']));
				$list[$key]['target'] = 'main_right';
				$list[$key]['image'] = 'images/group.png';
			}
			$key++;
			$list[$key]['id'] = 0;
			$list[$key]['text'] = '未分组';
			$list[$key]['title'] = '未分组';
			$list[$key]['xml'] = htmlentities(urlhelper('action',$this->GetControllerId(),
				$this->GetActionId(),"&logicalgroupid=-1"));
			$list[$key]['href'] = htmlentities(urlhelper('action',$this->GetControllerId(),
				'list',"&logicalgroupid=0&devicetype=terminal"));
			$list[$key]['target'] = 'main_right';
			$list[$key]['image'] = './images/nogroup.png';
			
			$root['id'] = -1;
			$root['text'] = '逻辑分组列表';
			$root['title'] = '逻辑分组列表';
			$root['xml'] = null;
			$root['href'] = htmlentities(urlhelper('action',$this->GetControllerId(),
				'list',"&logicalgroupid=0"));
			$root['target'] = 'main_right';
			$root['image'] = './images/home.png';
			
		}
		ob_clean();
		header("Content-Type: text/xml");
		$this->GetView()->Assign('root', $root);
		$this->GetView()->Assign('list', $list);
		$this->GetView()->Display('_tree_view.xml');
		$this->GetApp()->AppExit();
	}
}