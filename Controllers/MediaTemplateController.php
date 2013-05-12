<?php
class MediaTemplateController extends ControllerBase
{
	const ModuleRightView='template_view';
	const ModuleRightEdit='template_edit';
	const ObjName = MediaTemplate::ObjName;
	
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		
		$this->CheckPermission(self::ModuleRightView);
	}
	
	public function listAction()
	{
		$totalcount = 0;
		$table_options = array('table'=>MediaTemplate::GetTable(), 'select'=>'*');
		$join_options[] = 
			array('table'=>'media', 'left_field'=>'bgmediaid',
			'right_field'=>'mediaid','select'=>'savefilepath,medianame as bgfilename');
		$filter_fields = array(
			'templatename' => '%like%', 
			'screenratio' => '=',
			);
		$filter_options = $this->GetFilterOption($filter_fields);
		$filter_options[] = array('field'=>'playlistid', 'value'=>'0', 'op'=>'=');
		$screenratio = $this->GetRequest()->GetInput('screenratio', 'string');
		$list = DBHelper::GetJoinRecordSet($table_options, 
			$join_options, $filter_options, 
			$this->GetSortOption('templateid','desc'),
			$this->_offset, $this->_perpage, $totalcount);		
		
		$ratios = SysCode::GetCodeList(SysCode::ScreenRatio);
		
		$table = new HtmlTable(self::ObjName."列表",'border');
		$table -> BindField('templateid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('templatename', "模板名称", '', '', 'asc');
		$table -> BindField('screenratio', SysCode::ScreenRatio, '', '', 'asc');
		$table -> BindField('layoutpreview', "预览", 'image', '', 'asc', array('target'=>'_blank','width'=>80));
		
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		if($this->CheckPermission(self::ModuleRightEdit,false))
		{
		$table -> BindField('cp', "操作", 'cp', '','', 
			array(
			'设置'=>array('','edit','templateid'),
			'删除'=>array('','delete','templateid') ));
		$table -> BindHeadbar(" ".HtmlHelper::Button('创建'.self::ObjName,'link', 
			urlhelper('action',$this->GetControllerId(),'add')));
		}
		
		$table -> BindHeadbar(" 模板名".HtmlHelper::Input('templatename','','text'));
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('screenratio',
			$screenratio,$ratios,'syscode','syscodename',"按".SysCode::ScreenRatio));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function thumbnailAction()
	{
		$id = $this->GetRequest()->GetInput('templateid','int');
		$obj = new MediaTemplate($id);
		if (!$obj->GetData())
		{
			echo ("没有找到".self::ObjName." ".$id);
			$this->AppExit();
		}
		
		ob_clean();
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-type: image/png");
		if ($obj->layoutpreview)
		{
			echo $obj->layoutpreview;
		}
		else
		{
			$im = @imagecreate(50, 30)
			    or die("Cannot Initialize new image stream");
			$background_color = imagecolorallocate($im, 255, 255, 255);
			$text_color = imagecolorallocate($im, 233, 14, 91);
			imagestring($im, 5, 5, 5,  "none", $text_color);
			imagepng($im);
			imagedestroy($im);
		}
		$this->AppExit();
	}
	
	public function deleteAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$id = $this->GetRequest()->GetInput('templateid','int','没有指定要删除的'.self::ObjName);
		
		$obj = new MediaTemplate($id);
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
		$form -> BindField('templatename', self::ObjName.'名', $rs['templatename'],'text');
		
		$form -> BindField('screenratio', '宽高比', $rs['screenratio'],'select', 
			SysCode::GetCodeList(SysCode::ScreenRatio), 'syscode','syscodename');		
		
		$form -> BindField('bgcolor', '背景颜色', $rs['bgcolor'],'text');
		$form -> BindField('bgmediaid', '背景图', $rs['bgmediaid'],'text');
		$form -> BindField('templateid','',$rs['templateid'],'hidden');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');		
	}
	
	public function editAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$id = $this->GetRequest()->GetInput('templateid', 'int', '没有指定要修改的'.self::ObjName);
		$referer = $this->GetRequest()->GetInput('referer');
		$referer = $referer ? $referer : $this->GetApp()->GetEnv('referer');
		$obj = new MediaTemplate($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		
		$zones = $obj->GetZones();		
		$rs['layout'] = MediaTemplate::GetZoneStr($zones);
		
		$rs['id'] = $rs['templateid'];
		$rs['name'] = $rs['templatename'];
		$page_title = $rs['playlistid'] > 0? "修改节目布局" : "修改模板";
		$this->GetView()->Assign('page_title', $page_title);
		$this->GetView()->Assign('controller', $this->GetControllerId());
		$this->GetView()->Assign('action', 'do'.$this->GetActionId());
		$this->GetView()->Assign('referer', $referer);
		$this->GetView()->Assign('rs', $rs);
		$this->GetView()->Assign('ratios', SysCode::GetCodeList(SysCode::ScreenRatio));
		$this->GetView()->Display('layout_edit.htm');
	}
	
	
	public function addAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$rs = array();
		$rs['templateid'] = $this->GetRequest()->GetInput('templateid','int');
		
		$form = new HtmlForm('新建'.self::ObjName);
		
		$this->_bindFormFields($form, $rs);
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	private function _parseFormInput()
	{
		$input = array();
		$input['templatename'] = $this->GetRequest()->GetInput('templatename','string','模板名必须填写');
		$input['bgcolor'] = $this->GetRequest()->GetInput('bgcolor','string');
		$input['bgmediaid'] = $this->GetRequest()->GetInput('bgmediaid','int');
		$input['screenratio'] = $this->GetRequest()->GetInput('screenratio','string');
		return $input;
	}
	
	public function doaddAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$input = $this->_parseFormInput();
		
		$rs = DBHelper::GetSingleRecord(MediaTemplate::GetTable(), 'templatename', $input['templatename']);
		if ($rs)
		{
			$this->Message("模板名已存在  ".$input['templatename'], LOCATION_BACK);
		}
		$referer = $this->GetRequest()->GetInput('referer');
		$object = new MediaTemplate();
		if($object->Insert($input))
		{
			$this->Message("新建".self::ObjName."成功, 可以开始设置模板区域", urlhelper('action',
				$this->GetControllerId(), 'edit', '&templateid='.$object->GetId()));
		}
		else
		{
			$this->Message("新建".self::ObjName."失败", LOCATION_BACK);
		}
	}
	
	public function doeditAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$id = $this->GetRequest()->GetInput('id', 'int', '没有指定要修改的'.self::ObjName);
				
		$zonelayout = $this->GetRequest()->GetInput('layout','string');

		$object = new MediaTemplate($id);
		if (!$object->GetData())
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$result = $object->SaveZonesInput($zonelayout);
		
		$object->templatename = $this->GetRequest()->GetInput('name','string','模板名必须填写');
		$object->screenratio = $this->GetRequest()->GetInput('screenratio','string');
		$object->bgcolor = $this->GetRequest()->GetInput('bgcolor','string');
		$object->bgmediaid = $this->GetRequest()->GetInput('bgmediaid','int');
		$object->layoutpreview = $object->SaveThumbnail();
		if($object->SaveData())
		{
			$referer = $this->GetRequest()->GetInput('referer');
			$this->Message("修改".self::ObjName."成功", $referer);
		}
		else
		{
			$this->Message("修改".self::ObjName."失败", LOCATION_REFERER);
		}
	}
	
	public function indexAction()
	{
		$this->CheckPermission(self::ModuleRightView);
		$this->listAction();
//		$left_url = urlhelper('action',$this->GetControllerId(), 'tree');
//		$right_url = urlhelper('action',$this->GetControllerId(), 'list');
//		$this->GetView()->Assign('left_url', $left_url);
//		$this->GetView()->Assign('right_url', $right_url);
//		$this->GetView()->Display('_tree_frame.htm');
//		$this->GetApp()->AppExit();
	}
}