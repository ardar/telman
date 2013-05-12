<?php
class PlaylistController extends ControllerBase
{
	const ModuleRightView='playlist_view';
	const ModuleRightEdit='playlist_edit';
	const ObjName = Playlist::ObjName;
	
	public function Init($app, $request, $view)
	{
		$this->_perpage = 60;
		parent::Init($app, $request, $view);
		
		$this->CheckPermission(self::ModuleRightView);
	}
	
	public function listAction()
	{
		$canedit = $this->CheckPermission(self::ModuleRightEdit,false);
		$totalcount = 0;
		$join_options[] = 
			array('table'=>'template', 'left_field'=>'playlistid',
			'right_field'=>'playlistid','select'=>'layoutpreview,screenratio');
		$filter_fields = array(
			'playlistname' => '%like%', 
			'playlistfolderid' => '=',
			'template.screenratio' => '=',
			);
		$list = DBHelper::GetJoinRecordSet(Playlist::GetTable(), 
			$join_options, $this->GetFilterOption($filter_fields), 
			$this->GetSortOption('playlistid','desc'),
			$this->_offset, $this->_perpage, $totalcount);
		foreach($list as $key => $rs)
		{
			$list[$key]['preview'] = Playlist::ThumbnailUrl($rs['playlistid']);
			$list[$key]['duration'] = OpenDev::GetDurationDesc($rs['duration']);
		}
		$ratios = SysCode::GetCodeList(SysCode::ScreenRatio);
		
		$table = new HtmlTable(self::ObjName." 列表",'border');
		$table -> BindField('playlistid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('playlistname', self::ObjName."名称", '', '', 'asc');
		$table -> BindField('duration', "播放时间", 'duration', '', 'asc');
		$table -> BindField('preview', "预览", 'image', '', 'asc', array('target'=>'_blank','width'=>50));
		
		$table -> BindData($list);
		$pager = HtmlHelper::Pager($totalcount, $this->_perpage, $this->_page);
		
		if($this->CheckPermission(self::ModuleRightEdit,false))
		{
		$table -> BindField('cp', "操作", 'cp', '','', 
			array(
			'设置节目'=>array('','playedit','playlistid'),
			'查看引用'=>array('','viewref','playlistid'),
			'删除'=>array('','delete','playlistid') ));
		$table -> BindHeadbar(" ".HtmlHelper::Button('创建'.self::ObjName,'link', 
			urlhelper('action',$this->GetControllerId(),'add')));
		}
		else
		{
		$table -> BindField('cp', "操作", 'cp', '','', 
			array(
			'查看引用'=>array('','viewref','playlistid') ));
		}
		
		$table -> BindHeadbar(" 名称".HtmlHelper::Input('playlistname','','text'));
//		$table -> BindHeadbar(" ".HtmlHelper::ListBox('screenratio',
//			'',$ratios,'syscode','syscodename',"按".SysCode::ScreenRatio));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		
		$this->GetView()->Assign('canedit', $canedit);
		$this->GetView()->Assign('list', $list);
		$this->GetView()->Assign('pager', $pager);
		$this->GetView()->Display('playlist_list.htm');
	}
	
	public function viewrefAction()
	{
		$id = $this->GetRequest()->GetInput('playlistid','int','没有指定'.self::ObjName);
		$showdate = $this->GetRequest()->GetInput('showdate','string');
		
		$obj = new Playlist($id);
		if (!$obj->GetData())
		{
			$this->Message("没有找到".self::ObjName." ".$id, LOCATION_BACK);
		}
		$refs = $obj->GetReferences($showdate);
		foreach ($refs as $key => $rs)
		{
			$refs[$key]['date'] = HtmlHelper::Link(date($this->GetWebSite()->dateformat,$rs['scheduledate']), 
				urlhelper('action',$this->GetControllerId(), 'viewref', 
				"&playlistid=$id&showdate=".date('Y-m-d',$rs['scheduledate'])));
			$refs[$key]['cp'] = HtmlHelper::Link('节目时间表', 
				urlhelper('action','Schedule', 'index', 
				"&subaction=week&logicalgroupid=".$rs['logicalgroupid']
				."&begin_date=".date('Y-m-d',$rs['scheduledate'])));
		}
		$table = new HtmlTable("节目包引用列表 ".$showdate,'border');
		$table -> BindField('date', "日期", '', '');
		if($showdate)
		{
			$table -> BindField('begintime', "时间", '', '');
		}
		$table -> BindField('logicalgroupname', "逻辑组", '', '');
		$table -> BindField('schedulecount', "节目排程数", '', '');
		$table -> BindField('cp', "操作", '', '');
		$table -> BindData($refs);
		$table -> BindNavbar(" ".HtmlHelper::Button(' 返回上一页 ','button', 
			"onclick='history.back();'"),'button');
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function deleteAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$id = $this->GetRequest()->GetInput('playlistid','int','没有指定要删除的'.self::ObjName);
		
		$obj = new Playlist($id);
		if (!$obj->GetData())
		{
			$this->Message("没有找到要删除的".self::ObjName." ".$id, LOCATION_BACK);
		}
		if($obj -> Delete())
		{
			$this->Message("删除".self::ObjName."成功", LOCATION_REFERER);
		}
		else
		{
			$this->Message("删除 ".self::ObjName."失败", LOCATION_BACK);
		}
	}
	
	private function _bindFormFields(HtmlForm $form, &$rs)
	{
		$form -> BindField('playlistname', self::ObjName.'名', $rs['playlistname'],'text');
		
		$form -> BindField('playlistdesc', '备注', $rs['playlistdesc'],'text');		
		
		$form -> BindField('playlistid','',$rs['playlistid'],'hidden');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');		
	}
	
	public function applytemplateAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$id = $this->GetRequest()->GetInput('playlistid', 'int', '没有指定'.self::ObjName);
		$obj = new Playlist($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到".self::ObjName." ".$id, LOCATION_BACK);
		}
		$tpls = DBHelper::GetRecords(MediaTemplate::GetTable(), 'playlistid','0','templatename','asc');
		
		$form = new HtmlForm('选择'.self::ObjName.'要应用的模板 ');
		$form -> BindField('note','注意','<font color=red>应用模板将清空全部已设置的布局与文件列表, 如不修改原应用模板请勿提交本表单</font>','');
		$form -> BindField('name',self::ObjName.'名字',$rs['playlistname'],'');
		$form -> BindField('templateid', '应用模板', $rs['templateid'],'select',$tpls,'templateid','templatename');
		
		$form -> BindField('playlistid','',$rs['playlistid'],'hidden');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');	
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function playeditAction()
	{
		$this->CheckPermission(array(self::ModuleRightView,self::ModuleRightEdit));
		$canedit = $this->CheckPermission(self::ModuleRightEdit,false);
		
		$id = $this->GetRequest()->GetInput('playlistid', 'int', '没有指定要设置的'.self::ObjName);
		$obj = new Playlist($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到要设置的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$layout = $obj->GetLayout();
		if($layout)
		{
			$zones = $layout->GetZones();
			$rs['screenratio'] = $layout->screenratio;
		}
		if(isset($zones[0]))
		{
			$zones[0]['selected'] = 1;
			$right_url = urlhelper('action','PlaylistItem','editzone','&zoneid='.$zones[0]['zoneid']);
		}
		$rs['durationdesc'] = OpenDev::GetDurationDesc($rs['duration']);
		$acc = new Account($rs['creatorid']);
		if($acc->GetData())
		{
			$rs['creatorname'] = $acc->username." (".$acc->realname.")";
		}
		if ($rs['bgaudioid']>0)
		{
			$bgaudio = new Media($rs['bgaudioid']);
			if ($bgaudio->GetData())
			{
				$rs['bgaudioname'] = $bgaudio->medianame;
				$rs['bgaudiopath'] = $bgaudio->savefilepath;
			}
		}
		$rs['zones'] = $zones;
		$rs['templateid'] = $layout->templateid;
		$rs['layout'] = MediaTemplate::GetZoneStr($zones);
		
		$this->GetView()->Assign('canedit', $canedit);
		$this->GetView()->Assign('page_title', '修改'.self::ObjName);
		$this->GetView()->Assign('controller', $this->GetControllerId());
		$this->GetView()->Assign('action', 'do'.$this->GetActionId());
		$this->GetView()->Assign('rs', $rs);
		$this->GetView()->Assign('layout', $layout->GetData());
		$this->GetView()->Assign('zones', $zones);
		$this->GetView()->Assign('sel_zoneid', $zones[0]['zoneid']);
		$this->GetView()->Assign('sel_zonetype', $zones[0]['type']);
		$this->GetView()->Assign('right_url', $right_url);
		$this->GetView()->Display('playlist_edit.htm');
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
	
	private function _parseFormInput()
	{
		$input = array();
		$input['playlistname'] = $this->GetRequest()->GetInput('playlistname','string','名称必须填写');
		$input['playlistdesc'] = $this->GetRequest()->GetInput('playlistdesc','string');
		$input['bgaudioid'] = $this->GetRequest()->GetInput('bgaudioid','int');
		return $input;
	}
	
	public function doaddAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$input = $this->_parseFormInput();
		$initRatio = $this->GetRequest()->GetInput('screenratio','string');
		
		$rs = DBHelper::GetSingleRecord(Playlist::GetTable(), 'playlistname', $input['playlistname']);
		if ($rs)
		{
			$this->Message("同名".self::ObjName."已存在  ".$input['playlistname'], LOCATION_BACK);
		}
		$referer = $this->GetRequest()->GetInput('referer');
		$object = new Playlist();
		$object->playlistname = $input['playlistname'];
		$object->playlistdesc = $input['playlistdesc'];
		$object->createtime = $this->GetApp()->GetEnv('timestamp');
		$object->creatorid = $this->GetAccount()->GetId();
		
		if($object->SaveData())
		{
			$object->SaveThumbnail();
			$this->Message("新建".self::ObjName."成功, 可以开始设置节目", urlhelper('action',
				$this->GetControllerId(), 'playedit', '&playlistid='.$object->GetId()));
		}
		else
		{
			$this->Message("新建".self::ObjName."失败", LOCATION_BACK);
		}
	}
	
	public function doapplytemplateAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$playlistid = $this->GetRequest()->GetInput('playlistid', 'int', '没有指定要设置的'.self::ObjName);
		$templateid = $this->GetRequest()->GetInput('templateid','int','必须选择要应用的模板');
		
		$template = new MediaTemplate($templateid);
		if(!$template->GetData())
		{
			$this->Message('找不到选择的模板');
		}
		$referer = $this->GetRequest()->GetInput('referer');
		$object = new Playlist($playlistid);
		
		if($object->ApplyLayout($template))
		{
			$this->Message("应用模板".$template->templatename."成功, 可以开始设置节目", urlhelper('action',
				$this->GetControllerId(), 'playedit', '&playlistid='.$object->GetId()));
		}
		else
		{
			$this->Message("应用模板失败", LOCATION_BACK);
		}
	}
	
	public function doplayeditAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$id = $this->GetRequest()->GetInput('playlistid', 'int', '没有指定要修改的'.self::ObjName);
		$templateid = $this->GetRequest()->GetInput('templateid','int','节目包布局Id参数不正确');
		
		$input = $this->_parseFormInput();
		
		$zonelayout = $this->GetRequest()->GetInput('layout','string');
		
		$object = new Playlist($id);
		if (!$object->GetData())
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		
		$object->playlistname = $input['playlistname'];
		$object->playlistdesc = $input['playlistdesc'];
		$object->bgaudioid = $input['bgaudioid'];
		//$object->layoutpreview = $object->SaveThumbnail();
		if($object->SaveData())
		{
			$object->SaveThumbnail();
			$template = new MediaTemplate($templateid);
			if (!$template->GetData()|| $template->playlistid!=$id)
			{
				$this->Message("节目包布局不正确 ".$id, LOCATION_REFERER);
			}
			$template->bgcolor=$this->GetRequest()->GetInput('bgcolor','string');
			$template->bgmediaid=$this->GetRequest()->GetInput('bgmediaid','int');
			$template->templatename = $object->playlistname." 的布局";
			if(!$template->SaveData())
			{
				$this->Message("修改".self::ObjName."的布局失败", LOCATION_REFERER);
			}
			
			$this->Message("修改".self::ObjName."成功", urlhelper('action',$this->GetControllerId(),'list'));
		}
		else
		{
			$this->Message("修改".self::ObjName."失败", LOCATION_BACK);
		}
	}
	
	public function previewAction()
	{
		$id = $this->GetRequest()->GetInput('playlistid','int','没有提供要预览的节目包ID');
		$playlist = new Playlist($id);
		if (!$playlist->GetData()) 
		{
			$this->Message('没有找到节目包'.$id);
		}
		
		$layout = $playlist->GetLayout();
		
		$rs = array_merge($playlist->GetData(), $layout->GetData());
		
		$arr = explode(':',$rs['screenratio']);
		$rs['height'] = 600;
		$rs['width'] = intval(600 * $arr[0] / $arr[1]);
	
		$zones = $layout->GetZones();
		foreach ($zones as $key => $zoners)
		{
			$medialist = '';
			if($zoners['type']==MediaZone::ZoneTypeVideo)
			{
				$zone = new MediaZone($zoners);
				$items = $zone->GetItems();
				foreach ($items as $item)
				{
					$media = new Media($item['mediaid']);
					if ($media->GetData())
					{
						$medialist .= $medialist? ('|'.$media->savefilepath) : $media->savefilepath;
					}
				}
			}
			$zones[$key]['medialist'] = $medialist;
		}
		$this->GetView()->Assign('rs',$rs); 
		$this->GetView()->Assign('zones',$zones);
		$this->GetView()->Display('playlist_preview.htm');
		$this->GetApp()->AppExit();
	}
	
	public function previewtextAction()
	{
		$zoneid = $this->GetRequest()->GetInput('zoneid','int','没有指定 区域ID');
		$zone = new MediaZone($zoneid);
		$rs = $zone->GetData();
		if ($rs)
		{
			$items = $zone->GetItems();
			foreach ($items as $item)
			{
				$media = new Media($item['mediaid']);
				if ($media->GetData())
				{
					$itemstr = $media->savefilepath ."|". $item['text'];
					$medialist .= $medialist? '|'.$itemstr : $itemstr;
				}
			}
			$rs['medialist'] = $medialist;
		}
		$this->GetView()->Assign('param',$rs);
		$this->GetView()->Display('playlist_preview_text.htm');
		$this->GetApp()->AppExit();
	}
	
	public function treeAction()
	{
		$parentid = $this->GetRequest()->GetInput('parentid','int');
		$this->GetView()->Assign('treeview_url', 
			urlhelper('action', $this->GetControllerId(), 'xml', "&parentid=$parentid"));
		$this->GetView()->Display('_tree_view.htm');
		$this->GetApp()->AppExit();
	}
	
	public function xmlAction()
	{
		$parentid = $this->GetRequest()->GetInput('parentid','int');
		$repeaters = DBHelper::GetRecords(Playlist::GetTable(), 'parentid', $parentid, Playlist::GetPK(), 'asc');
		foreach($repeaters as $key=>$rs)
		{
			$repeaters[$key]['id'] = $rs['playlistid'];
			$repeaters[$key]['text'] = $rs['playlistname'];
			$repeaters[$key]['title'] = $rs['playlistname'];
			$repeaters[$key]['xml'] = htmlentities(urlhelper('action',$this->GetControllerId(),$this->GetActionId(),"&parentid=".$rs['playlistid']));
			$repeaters[$key]['href'] = htmlentities(urlhelper('action',$this->GetControllerId(),'list',
				"&playlistid=".$rs['playlistid']));
			$repeaters[$key]['target'] = 'main_right';
			$repeaters[$key]['image'] = './images/endnode.png';
			$preview = Playlist::ThumbnailUrl($rs['playlistid']);
			$repeaters[$key]['hover'] =  htmlentities(HtmlHelper::Image($preview,'','',120,120));
		}
		if ($parentid==0)
		{
			$root['id'] = 0;
			$root['text'] = '节目包列表';
			$root['title'] = '节目包列表';
			$root['xml'] = null;
			$root['href'] = htmlentities(urlhelper('action',$this->GetControllerId(),
				'list',"&parentid=0"));
			$root['target'] = 'main_right';
			$root['image'] = './images/endnode.png';
		}
		ob_clean();
		header("Content-Type: text/xml");
		$this->GetView()->Assign('root', $root);
		$this->GetView()->Assign('list', $repeaters);
		$this->GetView()->Display('_tree_view.xml');
		$this->GetApp()->AppExit();
	}
	
	public function indexAction()
	{
		$this->listAction();
//		$left_url = urlhelper('action',$this->GetControllerId(), 'tree');
//		$right_url = urlhelper('action',$this->GetControllerId(), 'list');
//		$this->GetView()->Assign('left_url', $left_url);
//		$this->GetView()->Assign('right_url', $right_url);
//		$this->GetView()->Display('_tree_frame.htm');
//		$this->GetApp()->AppExit();
	}

	
}