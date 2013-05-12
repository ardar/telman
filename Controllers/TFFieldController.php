<?php
class TFFieldController extends ControllerBase
{
	const ModuleRight='tf_setting';
	const ObjName = "格式字段";
	
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		
		$this->CheckPermission(self::ModuleRight);
	}
	
	public function listAction()
	{
		$totalcount = 0;
		$join_options[] = array(
			'table'=>TFFormat::GetTable(), 'alia'=>'fm', 
			'left_field'=>'formatid','right_field'=>'formatid','select'=>'formatname');
		$join_options[] = array(
			'table'=>SysCode::GetTable(), 'alia'=>'c2', 
			'left_field'=>'fieldtype','right_field'=>'syscode','select'=>'syscodename as fieldtypename');
//		$filter_fields = array(
//			'fieldname' => '%like%',
//			'formatid' => '==',
//			);
		$formatid = $this->GetRequest()->GetInput('formatid','int');
		$filter_options[] = DBHelper::FilterOption(TFField::GetTable(), 'formatid', $formatid, '=');
		//$fieldtype = $this->GetRequest()->GetInput('fieldtype','string');
		
		$list = DBHelper::GetJoinRecordSet(TFField::GetTable(), 
			$join_options, $filter_options, 
			$this->GetSortOption('formatid,fieldorder','asc'),
			$this->_offset, $this->_perpage, $totalcount);
		foreach ($list as $key => $rs)
		{
			$editurl = urlhelper(
				'action',$this->GetControllerId(),'edit','&fieldid='.$rs['fieldid']);
			$list[$key]['fieldname'] = HtmlHelper::Link($rs['fieldname'], $editurl);
			$list[$key]['fieldctrl'] = HtmlHelper::$InputTypes[$rs['fieldctrl']];
			$list[$key]['cp'] .= ' '.HtmlHelper::Link('设置', $editurl);
			$list[$key]['cp'] .= ' '.HtmlHelper::Link('删除', urlhelper(
				'action',$this->GetControllerId(),'delete','&fieldid='.$rs['fieldid'])
				,'','',"onclick=\"if(!confirm('是否确定删除该字段?'))return false;\"");
		}
		
		$table = new HtmlTable(self::ObjName."列表",'border');
		$table -> BindField('fieldid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('fieldname', self::ObjName."名称", '', '', 'asc');
		$table -> BindField('fieldtypename', "数据类型", '', '', 'asc');
		$table -> BindField('fieldctrl', "编辑控件", '', '', 'asc');
		$table -> BindField('formatname', "所属格式", '', '', 'asc');
		$table -> BindField('isrequired', "是否必填", 'bool', '', 'asc');
		$table -> BindField('isshowinlist', "列表中显示", 'bool', '', 'asc');
		$table -> BindField('fieldorder', "排序", '', '', 'asc');
		$table -> BindField('cp', "操作", '');
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		$formatlist = DBHelper::GetRecords(TFFormat::GetTable(),'','','formatorder');
				
		$table -> BindHeadbar(" ".HtmlHelper::Button('添加'.self::ObjName,'link',
			urlhelper('action',$this->GetControllerId(),'add',"&formatid=$formatid")));
//		$table -> BindHeadbar(" | 字段名".HtmlHelper::Input('fieldname','','text'));
//		$table -> BindHeadbar(" ".HtmlHelper::ListBox('formatid',
//			$formatid,$formatlist,
//			'formatid','formatname','按格式'));
//		$table -> BindHeadbar(" ".HtmlHelper::ListBox('fieldtype',
//			$fieldtype,SysCode::GetCodeList(Syscode::TFFieldType),
//			'syscode','syscodename','按数据类型'));
//		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
//		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
//		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
				
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function deleteAction()
	{
		$id = $this->GetRequest()->GetInput('fieldid','int','没有指定要删除的'.self::ObjName);
		
		$obj = new TFField($id);
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
		$fieldtypes = SysCode::GetCodeList(SysCode::TFFieldType);
		$form -> BindField('fieldtype', '数据类型', $rs['fieldtype'],
			'radiolist', $fieldtypes, 'syscode','syscodename');
		$form -> BindField('fieldname', self::ObjName.'名(*)', $rs['fieldname'],'text');
		$form -> BindField('fieldctrl', '编辑控件', $rs['fieldctrl'],'select',HtmlHelper::$InputTypes);
		$form -> BindField('fieldorder', '显示顺序', $rs['fieldorder'],'text');
		$form -> BindField('isrequired', '是否必填', $rs['isrequired'],'check');
		$form -> BindField('isshowinlist', '是否显示在列表', $rs['isshowinlist'],'check');
		$form -> BindField('fieldid','',$rs['fieldid'],'hidden');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
	}
	
	public function editAction()
	{
		$id = $this->GetRequest()->GetInput('fieldid', 'int', '没有指定要修改的'.self::ObjName);
		$obj = new TFField($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		
		$form = new HtmlForm('修改'.self::ObjName);
		$form -> BindField('fieldid', 'ID', $rs['fieldid'],'');
		$tfformat = new TFFormat($obj->formatid);
		$tfformat->GetData();
		
		$form -> BindField('formatid', '所属格式',$tfformat->formatname,'');
		$this->_bindFormFields($form, $rs);		
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function addAction()
	{
		$rs = array();
		$rs['formatid'] = $this->GetRequest()->GetInput('formatid','string');
				
		$form = new HtmlForm('新建'.self::ObjName);
		if($rs['formatid'])
		{
			$tfformat = new TFFormat($rs['formatid']);
			$tfformat->GetData();			
			$form -> BindField('', '所属格式',$tfformat->formatname,'');
			$form -> BindField('formatid', '',$rs['formatid'],'hidden');
		}
		else 
		{
			$formatlist = DBHelper::GetRecords(TFFormat::GetTable());
			$form -> BindField('formatid', '所属格式', $rs['formatid'] ,'select', $formatlist,
				'formatid','formatname','','readonly');
		}
		$this->_bindFormFields($form, $rs);
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
		
	private function _parseFormInput()
	{
		$input = array();
		
		$input['fieldname'] = $this->GetRequest()->GetInput('fieldname','string', self::ObjName.'名必须填写');
		$input['fieldtype'] = $this->GetRequest()->GetInput('fieldtype','string','字段数据类型必须选择');
		$input['fieldctrl'] = $this->GetRequest()->GetInput('fieldctrl','string');
		$input['fieldorder'] = $this->GetRequest()->GetInput('fieldorder','int');
		$input['isrequired'] = $this->GetRequest()->GetInput('isrequired','int');
		$input['isshowinlist'] = $this->GetRequest()->GetInput('isshowinlist','int');
	
		if (!SysCode::CodeExist(Syscode::TFFieldType, $input['fieldtype']))
		{
			throw new PCException('所选数据类型不正确'.$input['fieldtype']);
		}
		return $input;
	}
	
	public function doaddAction()
	{
		$input = $this->_parseFormInput();
		$input['formatid'] = $this->GetRequest()->GetInput('formatid','int','所属格式必须选择');
		$referer = $this->GetRequest()->GetInput('referer');
		$object = new TFField();
		if($object->Insert($input))
		{
			$backurl = urlhelper('action','TFFormat','edit',"&formatid=".$input['formatid']);
			$this->Message("新建".self::ObjName."成功", $backurl);
		}
		else
		{
			$this->Message("新建".self::ObjName."失败", LOCATION_BACK);
		}
	}
	
	public function doeditAction()
	{
		$id = $this->GetRequest()->GetInput('fieldid', 'int', '没有指定要修改的'.self::ObjName);
		$input = $this->_parseFormInput();	
						
		$object = new TFField($id);
		if (!$object->GetData())
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$referer = $this->GetRequest()->GetInput('referer');
		if($object->Update($input))
		{
			$backurl = urlhelper('action','TFFormat','edit',"&formatid=".$object->formatid);
			$this->Message("修改".self::ObjName."成功", $backurl);
		}
		else
		{
			$this->Message("修改".self::ObjName."失败", LOCATION_BACK);
		}
	}
}