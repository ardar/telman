<?php
class DeviceMonitorController extends ControllerBase
{
	const ModuleRight='device_monitor';
	const ModuleRightOP='device_operate';
	const ObjName = '设备状态';
	
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		
		$this->CheckPermission(self::ModuleRight);
	}
	
	public function faillistAction()
	{
		$list = Device::GetFailDevices();
		foreach ($list as $key => $rs)
		{
		    if(!Device::CheckDeviceRight($rs, $this->GetAccount()->GetAuthorizedDeviceIds()))
		    {
		        unset($list[$key]);
    	        continue;
		    }
			$list[$key]['devicename'] = HtmlHelper::Link($rs['devicename'],
				urlhelper('action',$this->GetControllerId(), 
					'view',"&parentid=".$rs['deviceid']));
			$list[$key]['devicetype'] = HtmlHelper::Image('images/'.$rs['devicetype'].'.png');
			$list[$key]['st_message'] = "<font color=red>".$rs['st_message']."</font>";
			$list[$key]['cp']= HtmlHelper::Link('日志',
				urlhelper('action','FWLog','devicelist',"&sourceid=".$rs['deviceid']),'','');
		}
		
		$table = new HtmlTable("异常设备列表",'border');
		$table -> BindField('deviceid', "ID", '', 'list_pk', '');
		$table -> BindField('st_message', "状态描述", '', '', '');
		$table -> BindField('devicetype', "类型", '', '', '');
		$table -> BindField('devicename', "设备名称", '', '', '');
		$table -> BindField('devicemac', "Mac地址", '', '', '');
		$table -> BindField('deviceisactive', "启用", 'bool', '', '');
		$table -> BindField('st_status', "开机", 'bool', '', '');
		$table -> BindField('st_screenstate', "开启屏幕", 'bool', '', '');
		$table -> BindField('st_lastbeattime', "最后连接时间", 'datetime', '', '');
		$table -> BindField('st_reconntimes', "重连次数", '', '', 'asc');
		$table -> BindField('st_reboottimes', "重启次数", '', '', 'asc');
		$table -> BindField('cp', "操作", '');
		$table -> BindData($list);
				
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function viewAction()
	{
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
			'devicetype' => '==',
			'logicalgroupid' => '==',
			);
		$parentid = $this->GetRequest()->GetInput('parentid','int');
		$devicetype = $this->GetRequest()->GetInput('devicetype','string');
		$logicalgroupid = $this->GetRequest()->GetInput('logicalgroupid','int');
		
		if($parentid>0)
		{
			$parent = new Device($parentid);
			$prs = $parent->GetData();
			if($prs)
			{
        	    if(!Device::CheckDeviceRight($prs, $this->GetAccount()->GetAuthorizedDeviceIds()))
        	    {
        	        $this->Message('没有权限监控该设备');
        	    }
				if ($parent->logicalgroupid>0)
				{
					$group = new LogicalGroup($parent->logicalgroupid);
					if ($group->GetData())
					{
						$prs['logicalgroupname'] = $group->logicalgroupname;
					}
				}
				$prs['st_message'] = Device::GetDeviceMessage($prs);
				$prs['st_message'] = $prs['st_message'] ? $prs['st_message'] : "正常";
				$prs['parentpath'] = $parent->GetParentPath('DeviceMonitor','view','parentid');
				$prs['devicetypename'] = Device::$DeviceTypes[$parent->devicetype];
				
				if(file_exists(Device::ScreenImageUrl($prs['devicemac'],TRUE)))
				{
				$prs['screen_image'] = urlhelper('action','DeviceMonitorJS','screenimage',
					"&deviceid=".$prs['deviceid']."&isthumbnail=1");
				}
				$st = $parent->GetStateFieldsDesc();
				$this->GetView()->Assign('st', $st);		
				$this->GetView()->Assign('rs', $prs);				
			}
		}
		if(!$parent || $parent->devicetype!=Device::DeviceTerminal)
		{
		    $filter_options = $this->GetFilterOption($filter_fields);
		    if(!in_array('ALL', $this->GetAccount()->GetAuthorizedDeviceIds()))
		    {
		        $ids = $this->GetAccount()->GetAuthorizedDeviceIds();
		        $str = "-1";
		        foreach ($ids as $id)
		        {
		            $str .= ",".intval($id);
		        }
		        $filter_options[] = array('sql'=>" (device.parentid in ($str) or device.deviceid in ($str))");
		    }
			$list = DBHelper::GetJoinRecordSet(Device::GetTable(), 
				$join_options, $filter_options, 
				$this->GetSortOption('devicetype,devicename','asc'),
				$this->_offset, $this->_perpage, $totalcount);
			foreach ($list as $key => $rs)
			{
				$list[$key]['devicename'] = HtmlHelper::Link($rs['devicename'],
					urlhelper('action',$this->GetControllerId(), 
						$this->GetActionId(),"&parentid=".$rs['deviceid']));
				$list[$key]['devicetype'] = HtmlHelper::Image('images/'.$rs['devicetype'].'.png');
				$rs['st_message'] = Device::GetDeviceMessage($rs);
				$list[$key]['st_message'] = $rs['st_message'] 
					? ("<font color=red>".$rs['st_message']."</font>") : "正常";
				$list[$key]['cp'] = HtmlHelper::Link('日志',
					urlhelper('action','FWLog','devicelist',"&sourceid=".$rs['deviceid']),'_blank','');
			}
				
			$logicalgroups = DBHelper::GetRecords(LogicalGroup::GetTable());
			$repeaters = Device::BuildRepeaterComboList();
			
			$table = new HtmlTable(self::ObjName."列表",'border');
			$table -> BindField('deviceid', "ID", '', 'list_pk', 'desc');
			$table -> BindField('devicetype', "类型", '', '', 'asc');
			$table -> BindField('devicename', "设备名称", '', '', 'asc');
			$table -> BindField('devicemac', "Mac地址", '', '', 'asc');
			$table -> BindField('deviceisactive', "启用", 'bool', '', 'asc');
			$table -> BindField('st_message', "状态", '', '', 'asc');
			$table -> BindField('st_status', "开机", 'bool', '', 'asc');
			$table -> BindField('st_screenstate', "开屏幕", 'bool', '', 'asc');
			$table -> BindField('st_lastbeattime', "最后连接时间", 'datetime', '', 'asc');
			$table -> BindField('st_reconntimes', "重连次数", '', '', 'asc');
			$table -> BindField('st_reboottimes', "重启次数", '', '', 'asc');
			$table -> BindField('cp', "操作", '');
			$table -> BindData($list);
			$table -> BindPager($totalcount, $this->_perpage, $this->_page);
			if(!$parentid)
			{
			$table -> BindHeadbar(" 终端名".HtmlHelper::Input('terminalname','','text'));
			$table -> BindHeadbar(" Mac".HtmlHelper::Input('terminalmac','','text'));
			$table -> BindHeadbar(" ".HtmlHelper::ListBox('logicalgroupid',$logicalgroupid,$logicalgroups,
				'logicalgroupid','logicalgroupname',array(''=>'按逻辑组','0'=>'未分配')));
			$table -> BindHeadbar(" ".HtmlHelper::ListBox('parentid',$parentid,$repeaters,
				'parentid','title',array(''=>'按所属中继','0'=>'未分配')));
			$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
			$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
			$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
			}
			
			$this->GetView()->Assign('table', $table);
		}
		$canop = $this->CheckPermission(self::ModuleRightOP,false);
		$this->GetView()->Assign('canop', $canop);
		$this->GetView()->Display('device_state.htm');
	}
	
	public function controlAction()
	{
		$this->CheckPermission(self::ModuleRightOP);
		
		$deviceid = $this->GetRequest()->GetInput('deviceid','int');
		$op = $this->GetRequest()->GetInput('op','string');
		$device = new Device($deviceid);
		if (!$device->GetData() && $op!='update')
		{
			$this->Message('没有找到设备');
		}
	    if(!Device::CheckDeviceRight($device->GetData(), $this->GetAccount()->GetAuthorizedDeviceIds()))
	    {
	        $this->Message('没有权限管理该设备');
	    }
		
		$client = new MonitorClient($device->devicemac);
		$client->Connect($this->GetApp()->GetSetting()->MonitorServerIP, 
			$this->GetApp()->GetSetting()->MonitorServerPort);
		$client->Init();
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		switch ($op)
		{
			case 'screen': 
				$ret = $client->RequestScreen(); 
				if ($ret===true)
				{
					$this->Message("获取屏幕截图成功");
				}
				else
				{
					$this->Message("获取屏幕截图失败");
				}
			case 'state': 
				$ret = $client->RequestState();
				if ($ret===true)
				{
					$this->Message("获取状态成功");
				}
				else
				{
					$this->Message("获取状态失败");
				}
				
				break;
			case 'close': 
				$ret=$client->RequestClose();		
				if ($ret)
				{
					$this->Message("操作成功, 当前屏幕已开启");
				}
				else
				{
					$this->Message("操作成功, 当前屏幕已关闭");
				}
				break;
			case 'update': 
				$isemergency = $this->GetRequest()->GetInput('isemergency','int');
				$client->RequestUpdate($isemergency); 
				$this->Message('更新通知已发送 ',LOCATION_REFERER);
				break;
			default: 
				$this->Message("未定义控制操作" .$op);
				break;
		}
		$this->GetApp()->AppExit();
	}
	
	public function indexAction()
	{
		$left_url = urlhelper('action',$this->GetControllerId(), 'left');
		$right_url = urlhelper('action',$this->GetControllerId(), 'view',"&parentid=0");
		$this->GetView()->Assign('left_url', $left_url);
		$this->GetView()->Assign('right_url', $right_url);
		$this->GetView()->Display('_tree_frame.htm');
		$this->GetApp()->AppExit();
	}
	
	public function leftAction()
	{
		$this->GetView()->Assign('tree1_url', 
			urlhelper('action', $this->GetControllerId(), 'devicetree', "&parentid=0"));
		$this->GetView()->Assign('tree2_url', 
			urlhelper('action', $this->GetControllerId(), 'grouptree', "&parentid=0"));
		$this->GetView()->Display('terminal_left.htm');
		$this->GetApp()->AppExit();
	}
	
	public function devicetreeAction()
	{
		$parentid = $this->GetRequest()->GetInput('parentid','int');
		$this->GetView()->Assign('treeview_url', 
			urlhelper('action', $this->GetControllerId(), 'devicexml', "&parentid=$parentid"));
		$this->GetView()->Display('_tree_view.htm');
		$this->GetApp()->AppExit();
	}
	
	public function devicexmlAction()
	{
		$parentid = $this->GetRequest()->GetInput('parentid','int');
		$repeaters = DBHelper::GetRecords(Device::GetTable(), 'parentid', $parentid, 'devicetype,devicename', 'asc');
		foreach($repeaters as $key=>$rs)
		{
    	    if(!Device::CheckDeviceRight($rs, $this->GetAccount()->GetAuthorizedDeviceIds()))
    	    {
    	        unset($repeaters[$key]);
    	        continue;
    	    }
			$repeaters[$key]['id'] = $rs['deviceid'];
			$repeaters[$key]['text'] = $rs['devicename'];
			$repeaters[$key]['title'] = $rs['devicemac'];
			$repeaters[$key]['target'] = 'main_right';
			$repeaters[$key]['href'] = htmlentities(urlhelper('action',$this->GetControllerId(),
					'view',"&parentid=".$rs['deviceid']));
			if($rs['devicetype']==Device::DeviceRepeater)
			{
				$repeaters[$key]['xml'] = htmlentities(urlhelper('action',$this->GetControllerId(),
					$this->GetActionId(),"&parentid=".$rs['deviceid']));
				$repeaters[$key]['image'] = './images/repeater.png';
			}
			else
			{
				$repeaters[$key]['xml'] = null;
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
				'view',"&parentid=0"));
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
		$this->GetView()->Assign('treeview_url', 
			urlhelper('action', $this->GetControllerId(), 'groupxml'));
		$this->GetView()->Display('_tree_view.htm');
		$this->GetApp()->AppExit();
	}
	
	public function groupxmlAction()
	{
		$groupid = $this->GetRequest()->GetInput('logicalgroupid','int');
		if($groupid)
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
        	    if(!Device::CheckDeviceRight($rs, $this->GetAccount()->GetAuthorizedDeviceIds()))
        	    {
        	        continue;
        	    }
				$rs['id'] = $rs['deviceid'];
				$rs['text'] = $rs['devicename'];
				$rs['title'] = $rs['devicename'];
				$rs['xml'] = null;
				$rs['href'] = htmlentities(urlhelper('action',$this->GetControllerId(),
					'view',"&parentid=".$rs['deviceid']));
				$rs['target'] = 'main_right';
				$rs['image'] = './images/terminal.png';
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
				$list[$key]['href'] = 'javascript:return false;';
				$list[$key]['target'] = 'main_right';
				$list[$key]['image'] = './images/group.png';
			}
			
			$key++;
			$list[$key]['id'] = -1;
			$list[$key]['text'] = '未分组';
			$list[$key]['title'] = '未分组';
			$list[$key]['xml'] = htmlentities(urlhelper('action',$this->GetControllerId(),
				$this->GetActionId(),"&logicalgroupid=-1"));
			$list[$key]['href'] = 'javascript:return false;';
			$list[$key]['target'] = 'main_right';
			$list[$key]['image'] = './images/nogroup.png';
			
			$root['id'] = 0;
			$root['text'] = '逻辑分组列表';
			$root['title'] = '逻辑分组列表';
			$root['xml'] = null;
			$root['href'] = 'javascript:return false;';
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