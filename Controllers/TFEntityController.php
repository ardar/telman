<?php
class TFEntityController extends ControllerBase
{
	const ModuleRightView='tf_entity_view';
	const ModuleRightEdit = "tf_entity_edit";
	const ObjName = "信息";
	
	private $_canedit = false;
	
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		
		$this->CheckPermission(array(self::ModuleRightView,self::ModuleRightEdit));
		$this->_canedit = $this->CheckPermission(self::ModuleRightEdit, false);
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
			//'categoryid' => '==',
			);
		$filter_options = $this->GetFilterOption($filter_fields);
		$categoryid = $this->GetRequest()->GetInput('categoryid','int');
		if($categoryid>0)
		{
			$filter_options[] = array('sql'=>" (cate.categoryid='$categoryid' or cate.parentid='$categoryid')");
		}
		
		$list = DBHelper::GetJoinRecordSet(TFEntity::GetTable(), 
			$join_options, $filter_options, 
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
		$table -> BindField('addtime', "提交时间", 'datetime', '', 'asc');
		if($this->_canedit)
		{
		$table -> BindField('cp', "操作", '');
		$table -> BindHeadbar(" ".HtmlHelper::Button('添加'.self::ObjName,'link',
			urlhelper('action',$this->GetControllerId(),'preadd',"&categoryid=$categoryid")));
		}
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
				
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
		$this->CheckPermission(self::ModuleRightEdit);
		
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
		$actionname = $this->_canedit ? "修改" : "查看";
		$form = new HtmlForm($actionname.self::ObjName);
		$form->SetReadOnly(!$this->_canedit);
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
		$this->CheckPermission(self::ModuleRightEdit);
		
		$categoryid = $this->GetRequest()->GetInput('categoryid','int');
		if ($categoryid>0)
		{
			//header("Location: ".urlhelper($this->GetControllerId(),'add',"&categoryid=".$categoryid));
			//$this->GetApp()->AppExit();
		}
		$cates = TFCategory::BuildComboList(0);
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
		$this->CheckPermission(self::ModuleRightEdit);
		
		$categoryid = $this->GetRequest()->GetInput('categoryid','int','请选择有效的分类');
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
		$rs['entityisactive'] = $categoryid;
		
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
			if($field['fieldtype']=='imagefile')
			{
				$uploadfile = new UploadFile('field_'.$field['fieldid'].'_upload');
				if($uploadfile->HasValue())
				{
					$uploadfile->Validate(UploadFile::CheckAllow, "jpeg|jpg|gif|png|bmp", 0, 10240);
					$tffile = new TFFile();
					$tffile->GetUpload($uploadfile);
					$tffile->uploaderid = $this->GetAccount()->GetId();
					$tffile->uploadtime = $this->GetApp()->GetEnv('timestamp');
					
					if($tffile->SaveData())
					{
						$uploadfile->Commit();
						$tffile -> SaveThumbnail(TFFile::ThumbnailMaxSize, TFFile::ThumbnailMaxSize);
						$fileurl = urlhelper('action','TFFile','getfile',"&fileid=".$tffile->GetId());
						$fieldvalue = $fileurl;
					}
				}
				else
				{
					$fieldvalue = $this->GetRequest()->GetInput(
					"field_".$field['fieldid'],$field['fieldtype'],$errmsg);
				}
			}
			else 
			{
				$fieldvalue = $this->GetRequest()->GetInput(
					"field_".$field['fieldid'],$field['fieldtype'],$errmsg);
			}
			//echo "<BR><BR><BR>pasrse:";print_r($field)."<BR>\n";
			$object->SetExField($field['fieldid'],$fieldvalue);
		}
		return $object;
	}
	
	public function doaddAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$fields = array();
		$referer = $this->GetRequest()->GetInput('referer');
		$object = new TFEntity();
		$this->_parseFormInput($object);
		$object->addtime = $this->GetApp()->GetEnv('timestamp');
		$object->adduserid = $this->GetAccount()->GetId();
		if($object->SaveData())
		{
			$backurl = urlhelper('action',$this->GetControllerId(),'list',"&categoryid=".$object->categoryid);
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
				$this->Message('',$referer);
			}
			$backurl = urlhelper('action',$this->GetControllerId(),'list',"&categoryid=".$object->categoryid);
			$this->Message("修改".self::ObjName."成功", $backurl);
		}
		else
		{
			$this->Message("修改".self::ObjName."失败", LOCATION_BACK);
		}
	}

	public function indexAction()
	{
		$left_url = urlhelper('action',$this->GetControllerId(), 'tree', "");
		$right_url = urlhelper('action','TFEntity', 'list');
		$this->GetView()->Assign('left_width','15%');
		$this->GetView()->Assign('left_url', $left_url);
		$this->GetView()->Assign('right_url', $right_url);
		$this->GetView()->Display('_tree_frame.htm');
		$this->GetApp()->AppExit();
	}
	
	public function treeAction()
	{
		$parentid = $this->GetRequest()->GetInput('parentid','int');
		$url = urlhelper('action', $this->GetControllerId(), 'xml', 
			"&parentid=$parentid");
		//echo $url;die();
		$this->GetView()->Assign('treeview_url', $url);
		$this->GetView()->Display('_tree_view.htm');
		$this->GetApp()->AppExit();
	}
	
	public function xmlAction()
	{
		$parentid = $this->GetRequest()->GetInput('parentid','int');
		$list = DBHelper::GetRecords(TFCategory::GetTable(), 'parentid', $parentid, 'categoryorder', 'asc');
		foreach($list as $key=>$rs)
		{
			$list[$key]['id'] = $rs['categoryid'];
			$list[$key]['text'] = $rs['categoryname'];
			$list[$key]['title'] = $rs['categoryname'];
			if($rs['parentid']>0)
			{
				$list[$key]['xml'] = null;
				$list[$key]['image'] = './images/endnode.png';
				$list[$key]['image_open'] = './images/endnode.png';
			}
			else 
			{
				$list[$key]['xml'] = htmlentities(urlhelper('action',$this->GetControllerId(),$this->GetActionId(),
				"&parentid=".$rs['categoryid']));
			}
			$list[$key]['href'] = htmlentities(urlhelper('action',$this->GetControllerId(),'list',
				"&categoryid=".$rs['categoryid']));
			$list[$key]['target'] = 'main_right';
		}
		if ($parentid==0)
		{
			$root['id'] = 0;
			$root['text'] = '信息分类';
			$root['title'] = '信息分类';
			$root['xml'] = null;
			$root['href'] = htmlentities(urlhelper('action',$this->GetControllerId(),'list',
				"&categoryid=".$rs['categoryid']));
			$root['target'] = 'main_right';
		}
		ob_clean();
		header("Content-Type: text/xml");
		$this->GetView()->Assign('root', $root);
		$this->GetView()->Assign('list', $list);
		$this->GetView()->Display('_tree_view.xml');
		$this->GetApp()->AppExit();
	}
}