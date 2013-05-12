<?php
class TouchController extends ControllerBase
{
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
	}
		
	public function listAction()
	{
		$totalcount = 0;
		$join_options[] = array(
			'table'=>TFCategory::GetTable(), 'alia'=>'cate', 
			'left_field'=>'categoryid','right_field'=>'categoryid','select'=>'categoryname');
		$join_options[] = array(
			'table'=>Account::GetTable(), 'alia'=>'u', 
			'left_field'=>'adduserid','right_field'=>'userid','select'=>'username as addusername');
		$filter_fields = array(
			'entityname' => '%like%',
			'categoryid' => '==',
			);
		$categoryid = $this->GetRequest()->GetInput('categoryid','int');
		
		$list = DBHelper::GetJoinRecordSet(TFEntity::GetTable(), 
			$join_options, $this->GetFilterOption($filter_fields), 
			$this->GetSortOption('toprange,entityid','desc'),
			$this->_offset, $this->_perpage, $totalcount);
		foreach ($list as $key => $rs)
		{
			$editurl = urlhelper(
				'action',$this->GetControllerId(),'edit','&entityid='.$rs['entityid']);
			$list[$key]['entityname'] = HtmlHelper::Link($rs['entityname'], $editurl);
			$list[$key]['cp'] .= ' '.HtmlHelper::Link('修改', $editurl);
			$list[$key]['cp'] .= ' '.HtmlHelper::Link('删除', urlhelper(
				'action',$this->GetControllerId(),'delete','&entityid='.$rs['entityid'])
				,'','',"onclick=\"if(!confirm('是否确定删除该信息?'))return false;\"");
		}
		
		$table = new HtmlTable(self::ObjName."列表",'border');
		$table -> BindField('entityid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('entityname', self::ObjName."标题", '', '', 'asc');
		$table -> BindField('categoryname', "所属分类", '', '', 'asc');
		$table -> BindField('toprange', "是否置顶", 'bool', '', 'asc');
		$table -> BindField('entityisactive', "是否显示", 'bool', '', 'asc');
		$table -> BindField('addusername', "提交用户名", '', '', 'asc');
		$table -> BindField('cp', "操作", '');
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
				
		$table -> BindHeadbar(" ".HtmlHelper::Button('添加'.self::ObjName,'link',
			urlhelper('action',$this->GetControllerId(),'preadd',"&categoryid=$categoryid")));
		$table -> BindHeadbar(" | 标题".HtmlHelper::Input('entityname','','text'));
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('categoryid',
			$categoryid,TFCategory::BuildComboList(0),
			'categoryid','title','按分类'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
				
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function deleteAction()
	{
		$id = $this->GetRequest()->GetInput('entityid','int','没有指定要删除的'.self::ObjName);
		
		$obj = new TFEntity($id);
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
		
	private function _bindFormFields(HtmlForm $form, &$rs, &$fields)
	{
		$form -> BindField('entityname', self::ObjName.'标题(*)', $rs['entityname'],'text');
		foreach ($fields as $field)
		{
			$require = $field['isrequired'] ? "(*)" : '';
			$form -> BindField("field_".$field['fieldid'], "<扩展字段> ".$field['fieldname'].$require, 
				$field['fieldvalue'],$field['fieldctrl']);
		}
		$form -> BindField('content','信息内容',OpenDev::dstripslashes($rs['content']),'htmledit');
		$form -> BindField('entityisactive','是否显示',$rs['entityisactive'],'check');
		$form -> BindField('entityid','',$rs['entityid'],'hidden');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','','','hidden');
	}
	
	public function editAction()
	{
		$id = $this->GetRequest()->GetInput('entityid', 'int', '没有指定要修改的'.self::ObjName);
		$obj = new TFEntity($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		
		$form = new HtmlForm('修改'.self::ObjName);
		$form -> BindField('entityid', 'ID', $rs['entityid'],'');
		$category = new TFCategory($rs['categoryid']);
		if(!$category->GetData())
		{
			$this->Message('没有找到所选分类 '.$rs['categoryid']);
		}
		$fields = $obj->GetFieldList();
		
		$ext = "onchange=\"javascript:referer.value=window.location.href;this.form.submit();\"";
		$form -> BindField('categoryid', '所属分类', $rs['categoryid'],'select',
			TFCategory::BuildComboList(0),'categoryid','title','',$ext);
			
		$this->_bindFormFields($form, $rs, $fields);
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function preaddAction()
	{
		$categoryid = $this->GetRequest()->GetInput('categoryid','int');
		if (false && $categoryid>0)
		{
			header("Location: ".urlhelper($this->GetControllerId(),'add',"&categoryid=".$categoryid));
			$this->GetApp()->AppExit();
		}
		$cates = TFCategory::BuildComboList($categoryid);
		$form = new HtmlForm('新建'.self::ObjName." 第一步: 选择分类");
		$form->BindParam('method', 'get');
		$form -> BindField('categoryid', '选择所属分类', $categoryid,
			'select', $cates, 'categoryid','title');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','add','hidden');
		$form -> BindField('referer','','','hidden');
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function addAction()
	{
		$categoryid = $this->GetRequest()->GetInput('categoryid','int');
		$category = new TFCategory($categoryid);
		if(!$category->GetData())
		{
			$this->Message('没有找到所选分类 '.$categoryid);
		}
		if ($category->parentid==0)
		{
			$this->Message('不能添加信息到主分类 ['.$category->categoryname.'] 下 ');
		}
		
		$rs = array();
		$rs['categoryid'] = $categoryid;
		
		$form = new HtmlForm('新建'.self::ObjName);
		
		$fields = array();
		$tfformat = new TFFormat($category->formatid);
		if($tfformat->GetData())
		{
			$fields = $tfformat->GetFieldList();
		}
		
		$url = urlhelper('action',$this->GetControllerId(),$this->GetActionId(),"&categoryid=");
		$ext = "onchange=\"javascript:window.location.href='".$url."'+this.value;\"";
		$form -> BindField('categoryid', '所属分类', $rs['categoryid'],'select',
			TFCategory::BuildComboList(0),'categoryid','title','',$ext);
		
		$this->_bindFormFields($form, $rs, $fields);
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	/**
	 * @param TFEntity $object
	 */
	private function _parseFormInput(TFEntity &$object)
	{
		$ori_categoryid = $object->categoryid;
		$object->entityname = $this->GetRequest()->GetInput('entityname','string', self::ObjName.'名必须填写');
		$object->categoryid = $this->GetRequest()->GetInput('categoryid','int',"所属分类必须选择");
		$object->content = $this->GetRequest()->GetInput('content','string');
		$object->entityisactive = $this->GetRequest()->GetInput('entityisactive','int');
	
		$category = new TFCategory($object->categoryid);
		if (!$category->GetData())
		{
			$this->Message('没有找到所选分类 '.$object->categoryid);
		}
		$field_defs = $object->GetFieldList();
		foreach ($field_defs as $key=>$field)
		{
			$errmsg = '';
			if($ori_categoryid==$object->categoryid && $field['isrequired'])
			{
				$errmsg = $field['fieldname']."必须填写";
			}
			$fieldvalue = $this->GetRequest()->GetInput(
				"field_".$field['fieldid'],$field['fieldtype'],$errmsg);
			//echo "<BR><BR><BR>pasrse:";print_r($field)."<BR>\n";
			$object->SetExField($field['fieldid'],$fieldvalue);
		}
		return $object;
	}
	
	public function doaddAction()
	{
		$fields = array();
		$referer = $this->GetRequest()->GetInput('referer');
		$object = new TFEntity();
		$this->_parseFormInput($object);
		if($object->SaveData())
		{
			$backurl = urlhelper('action',$this->GetControllerId(),'list',"&formatid=".$object->formatid);
			$this->Message("新建".self::ObjName."成功", $backurl);
		}
		else
		{
			$this->Message("新建".self::ObjName."失败", LOCATION_BACK);
		}
	}
	
	public function doeditAction()
	{
		$id = $this->GetRequest()->GetInput('entityid', 'int', '没有指定要修改的'.self::ObjName);
						
		$object = new TFEntity($id);
		if (!$object->GetData())
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		
		$this->_parseFormInput($object);
		
		$referer = $this->GetRequest()->GetInput('referer');
		if($object->SaveData())
		{
			if ($referer)
			{
				$this->Redirect($referer);
			}
			$backurl = urlhelper('action',$this->GetControllerId(),'list',"&formatid=".$object->formatid);
			$this->Message("修改".self::ObjName."成功", $backurl);
		}
		else
		{
			$this->Message("修改".self::ObjName."失败", LOCATION_BACK);
		}
	}
}