<?php
class TFFormatController extends ControllerBase
{
	const ModuleRight='tf_setting';
	const ObjName = "格式";
	
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		
		$this->CheckPermission(self::ModuleRight);
	}
	
	public function listAction()
	{
		$totalcount = 0;
		$list = array();
		//$groups = SysCode::GetCodeList(SysCode::TFCetegoryType);
		$sql = "select fm.*,count(fieldid) as fieldcount 
			from ".TFFormat::GetTable()." fm
			left join ".TFField::GetTable()." f on f.formatid=fm.formatid 
			group by fm.formatid
			order by formatorder";
		$list = DBHelper::GetQueryRecords($sql);
		foreach ($list as $key=>$rs)
		{
			$list[$key]['link'] = urlhelper('action',$this->GetControllerId(), 'list', '&formatid='.$rs['formatid']);
			
			$list[$key]['cp'] .= ' '.HtmlHelper::Link('设置', 
				urlhelper('action',$this->GetControllerId(), 'edit', '&formatid='.$rs['formatid']));
			$list[$key]['cp'] .= ' '.HtmlHelper::Link('删除', 
				urlhelper('action',$this->GetControllerId(), 'delete', '&formatid='.$rs['formatid']));
		}
		$table = new HtmlTable("格式列表", 'border');
		$table -> BindField('formatname', "分类格式", '', '', 'desc');
		$table -> BindField('fieldcount', "字段数量", '', '', 'desc');
		$table -> BindField('formatorder', "显示排序", '', '', 'desc');
		$table -> BindField('cp', "操作", '', '');
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function deleteAction()
	{
		$id = $this->GetRequest()->GetInput('fieldid','int','没有指定要删除的'.self::ObjName);
		
		$obj = new TFFormat($id);
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

	public function fieldlistAction()
	{
		$totalcount = 0;
		$join_options[] = array(
			'table'=>SysCode::GetTable(), 'alia'=>'c2', 
			'left_field'=>'fieldtype','right_field'=>'syscode','select'=>'syscodename as fieldtypename');

		$formatid = $this->GetRequest()->GetInput('formatid','int');
		$filter_options[] = DBHelper::FilterOption(TFField::GetTable(), 'formatid', $formatid, '=');
		
		$list = DBHelper::GetJoinRecordSet(TFField::GetTable(), 
			$join_options, $filter_options, 
			$this->GetSortOption('formatid,fieldorder','asc'),
			$this->_offset, $this->_perpage, $totalcount);
		//TRACE($list);
		foreach ($list as $key => $rs)
		{
			$editurl = urlhelper(
				'action','TFField','edit','&fieldid='.$rs['fieldid']);
			$list[$key]['fieldname'] = HtmlHelper::Link($rs['fieldname'], $editurl);
			$list[$key]['fieldctrl'] = HtmlHelper::$InputTypes[$rs['fieldctrl']];
			$list[$key]['cp'] .= ' '.HtmlHelper::Link('设置', $editurl);
			$list[$key]['cp'] .= ' '.HtmlHelper::Link('删除', urlhelper(
				'action','TFField','delete','&fieldid='.$rs['fieldid'])
				,'','',"onclick=\"if(!confirm('是否确定删除该字段?'))return false;\"");
		}
		
		$table = new HtmlTable("扩展字段列表",'border');
		$table -> BindField('fieldid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('fieldname', self::ObjName."名称", '', '', 'asc');
		$table -> BindField('fieldtypename', "数据类型", '', '', 'asc');
		$table -> BindField('fieldctrl', "编辑控件", '', '', 'asc');
		//$table -> BindField('formatname', "所属格式", '', '', 'asc');
		$table -> BindField('isrequired', "是否必填", 'bool', '', 'asc');
		$table -> BindField('isshowinlist', "列表中显示", 'bool', '', 'asc');
		$table -> BindField('fieldorder', "排序", '', '', 'asc');
		$table -> BindField('cp', "操作", '');
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		$table -> BindHeadbar(" ".HtmlHelper::Button('添加字段','link',
			urlhelper('action','TFField','add',"&formatid=$formatid")));
			
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	private function _bindFormFields(HtmlForm $form, &$rs)
	{
		$form -> BindField('formatname', self::ObjName.'名(*)', $rs['formatname'],'text');
		$form -> BindField('formatorder', '显示顺序', $rs['formatorder'],'text');
		$form -> BindField('formatid','',$rs['formatid'],'hidden');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
	}
	
	public function editAction()
	{
		$id = $this->GetRequest()->GetInput('formatid', 'int', '没有指定要修改的'.self::ObjName);
		$obj = new TFFormat($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		
		$form = new HtmlForm('修改'.self::ObjName);
		$form -> BindField('formatid', 'ID', $rs['formatid'],'');
		$tfformat = new TFFormat($obj->formatid);
		$tfformat->GetData();
		
		$this->_bindFormFields($form, $rs);		
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
		
		$this->fieldlistAction();
	}
	
	public function addAction()
	{
		$rs = array();				
		$form = new HtmlForm('新建'.self::ObjName);
		
		$this->_bindFormFields($form, $rs);
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
		
	private function _parseFormInput()
	{
		$input = array();
		
		$input['formatname'] = $this->GetRequest()->GetInput('formatname','string', self::ObjName.'名必须填写');
		$input['formatorder'] = $this->GetRequest()->GetInput('formatorder','int');
	
		return $input;
	}
	
	public function doaddAction()
	{
		$input = $this->_parseFormInput();
		$referer = $this->GetRequest()->GetInput('referer');
		$object = new TFFormat();
		if($object->Insert($input))
		{
			$backurl = urlhelper('action',$this->GetControllerId(),'list');
			$this->Message("新建".self::ObjName."成功", $backurl);
		}
		else
		{
			$this->Message("新建".self::ObjName."失败", LOCATION_BACK);
		}
	}
	
	public function doeditAction()
	{
		$id = $this->GetRequest()->GetInput('formatid', 'int', '没有指定要修改的'.self::ObjName);
		$input = $this->_parseFormInput();	
						
		$object = new TFFormat($id);
		if (!$object->GetData())
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$referer = $this->GetRequest()->GetInput('referer');
		if($object->Update($input))
		{
			$backurl = urlhelper('action',$this->GetControllerId(),'list');
			$this->Message("修改".self::ObjName."成功", $backurl);
		}
		else
		{
			$this->Message("修改".self::ObjName."失败", LOCATION_BACK);
		}
	}
}