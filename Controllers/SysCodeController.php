<?php

class SysCodeController extends ControllerBase
{
	const ModuleRight='system';
	const ObjName = "系统常量";
	
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		
		$this->CheckPermission(self::ModuleRight);
	}
	
	public function indexAction()
	{
		$totalcount = 0;
		$list = array();
		$groups = SysCode::GetGroups();
		foreach ($groups as $rs)
		{
			$link = urlhelper('action',$this->GetControllerId(), 'list', '&syscodegroup='.$rs);
			$list[] = array( 'syscodegroup'=> $rs, 'link'=>HtmlHelper::Link($rs, $link));
		}
		
		$table = new HtmlTable(self::ObjName."分类",'border');
		$table -> BindField('link', "分类名称", '', '', 'desc');
		//$table -> BindField('cp', "操作", 'cp', '','', 
		//	array(
		//	'添加常量'=>array('','add','syscodegroup')));
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function listAction()
	{
		$totalcount = 0;
		$filter_fields = array(
			'syscode' => 'like%', 
			'syscodename' => '%like%', 
			'syscodegroup' => '=', 
			);
		$syscodegroup = $this->GetRequest()->GetInput('syscodegroup', 'string');
		$list = DBHelper::GetJoinRecordSet(SysCode::GetTable(), 
			null, $this->GetFilterOption($filter_fields), 
			$this->GetSortOption('syscodeorder,syscodename','asc'),
			$this->_offset, $this->_perpage, $totalcount);
				
		$table = new HtmlTable("[$syscodegroup] 列表  ",'border');
		$table -> BindField('syscodeid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('syscode', self::ObjName."", '', '', 'asc');
		$table -> BindField('syscodename', "别名", '', '', 'asc');
		$table -> BindField('syscodeorder', "显示顺序", '', '', 'asc');
		$table -> BindField('syscodegroup', "分类", '', '', 'asc');
		$table -> BindField('cp', "操作", 'cp', '','', 
			array(
			'设置'=>array('','edit','syscodeid'),
			'删除'=>array('','delete','syscodeid') ));
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		$table -> BindHeadbar(" ".HtmlHelper::Button('返回分类列表','link', 
			urlhelper('action',$this->GetControllerId(),'index')));
		if($syscodegroup)
		$table -> BindHeadbar(" ".HtmlHelper::Button('创建'.self::ObjName,'link', 
			urlhelper('action',$this->GetControllerId(),'add','&syscodegroup='.$syscodegroup)));
		$table -> BindHeadbar(" 常量值".HtmlHelper::Input('syscode','','text'));
		$table -> BindHeadbar(" 别名".HtmlHelper::Input('syscodename','','text'));
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('syscodegroup',$syscodegroup,SysCode::GetGroups(),'','','按常量分类'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function deleteAction()
	{
		$id = $this->GetRequest()->GetInput('syscodeid','int','没有指定要删除的'.self::ObjName);
		
		$obj = new SysCode($id);
		if (!$obj->GetData())
		{
			$this->Message("没有找到要删除的".self::ObjName." ".$id, LOCATION_BACK);
		}
		if($obj -> Delete(false))
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
		$form -> BindField('syscode', '系统常量(*)', $rs['syscode'],'text');
		$form -> BindField('syscodename', '别名', $rs['syscodename'],'text');
		$form -> BindField('syscodeparam', '参数设置', $rs['syscodeparam'],'text');
		$form -> BindField('syscodeorder', '显示顺序', $rs['syscodeorder'],'text');
		
		$form -> BindField('syscodegroup', '分类', $rs['syscodegroup'],'textreadonly');	
		$form -> BindField('syscodegroup', '分类', $rs['syscodegroup'],'hidden');		
		
		$form -> BindField('syscodeid','',$rs['syscodeid'],'hidden');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');		
	}
	
	public function editAction()
	{
		$id = $this->GetRequest()->GetInput('syscodeid', 'int', '没有指定要修改的'.self::ObjName);
		$obj = new SysCode($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$form = new HtmlForm('修改'.self::ObjName);
		$form -> BindField('syscodeid', 'ID', $rs['syscodeid'],'');
		
		$this->_bindFormFields($form, $rs);
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function addAction()
	{
		$rs = array();
		$rs['syscodegroup'] = $this->GetRequest()->GetInput('syscodegroup','string','请指定添加的分类');
		
		$form = new HtmlForm('新建'.self::ObjName);
		
		$this->_bindFormFields($form, $rs);
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	private function _parseFormInput()
	{
		$input = array();
		$input['syscode'] = $this->GetRequest()->GetInput('syscode','string','常量值必须填写');
		$input['syscodename'] = $this->GetRequest()->GetInput('syscodename','string');
		$input['syscodename'] = $input['syscodename']? $input['syscodename'] : $input['syscode'];
		$input['syscodegroup'] = $this->GetRequest()->GetInput('syscodegroup','string','必须选择常量分类');
		$input['syscodeparam'] = $this->GetRequest()->GetInput('syscodeparam','string');
		$input['syscodeorder'] = $this->GetRequest()->GetInput('syscodeorder','int');
			
		return $input;
	}
	
	public function doaddAction()
	{
		$input = $this->_parseFormInput();
	
		$foldercount = DBHelper::GetRecordCount(SysCode::GetTable(), 
			" syscodegroup='".$input['syscodegroup']."' 
			and syscode='".$input['syscode']."'");
		if ($foldercount)
		{
			$this->Message("该分类下已存在同名项目  ".$input['syscode'], LOCATION_BACK);
		}
		
		$referer = $this->GetRequest()->GetInput('referer');
		$object = new SysCode();
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
		$referer = $this->GetRequest()->GetInput('referer');
		$id = $this->GetRequest()->GetInput('syscodeid', 'int', '没有指定要修改的'.self::ObjName);
		
		$input = $this->_parseFormInput();	
					
		$object = new SysCode($id);
		if (!$object->GetData())
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
	
		$foldercount = DBHelper::GetRecordCount(SysCode::GetTable(), 
			" syscodegroup='".$input['syscodegroup']."' 
			and syscode='".$input['syscode']."'
			and syscodeid<>'".$object->GetId()."'");
		if ($foldercount)
		{
			$this->Message("该分类下已存在同名项目  ".$input['syscode'], LOCATION_BACK);
		}
		
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