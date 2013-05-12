<?php
class TFCategoryController extends ControllerBase
{
	const ModuleRightView='tf_category_view';
	const ModuleRightEdit='tf_category_edit';
	const ObjName = "分类";
	
	private $_canedit = false;
	
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		
		$this->CheckPermission(array(self::ModuleRightView,self::ModuleRightEdit));
		$this->_canedit = $this->CheckPermission(self::ModuleRightEdit,false);
		
		$this->_perpage = 100;
	}
	
	public function listAction()
	{
		$totalcount = 0;
		$join_options[] = array(
			'table'=>TFCategory::GetTable(), 'alia'=>'p', 
			'left_field'=>'parentid','right_field'=>'categoryid','select'=>'categoryname as parentname');
		$join_options[] = array(
			'table'=>TFFormat::GetTable(), 'alia'=>'c', 
			'left_field'=>'formatid','right_field'=>'formatid','select'=>'formatname');
		$filter_fields = array(
			'categoryname' => '%like%', 
			'parentid' => '==',
			'formatid' => '==',
			);
		$parentid = $this->GetRequest()->GetInput('parentid','int');
		$formatid = $this->GetRequest()->GetInput('formatid','int');
		
		$cates = DBHelper::GetRecords(TFCategory::GetTable(),'parentid','0','categoryorder','asc');
		
		$list = DBHelper::GetJoinRecordSet(TFCategory::GetTable(), 
			$join_options, $this->GetFilterOption($filter_fields), 
			$this->GetSortOption('parentid,categoryorder','asc'),
			$this->_offset, $this->_perpage, $totalcount);
		foreach ($list as $key => $rs)
		{
			$editurl = urlhelper(
				'action',$this->GetControllerId(),'edit','&categoryid='.$rs['categoryid']);
			$list[$key]['categoryname'] = HtmlHelper::Link($rs['categoryname'], $editurl);
			$list[$key]['categoryicon'] = TFCategory::IconUrl($rs['categoryicon']);
			$list[$key]['cp'] = HtmlHelper::Link('子分类列表', urlhelper(
				'action',$this->GetControllerId(),$this->GetActionId(),'&parentid='.$rs['categoryid']));
			if($this->_canedit)
			{
			$list[$key]['cp'] .= ' '.HtmlHelper::Link('设置', $editurl);
			$list[$key]['cp'] .= ' '.HtmlHelper::Link('删除', urlhelper(
				'action',$this->GetControllerId(),'delete','&categoryid='.$rs['categoryid'])
				,'','',"onclick=\"if(!confirm('是否确定删除该分类?'))return false;\"");
			}
		}
		
		$table = new HtmlTable(self::ObjName."列表",'border');
		$table -> BindField('categoryid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('categoryicon', "icon", 'image', '', 'asc', array('width'=>50));
		$table -> BindField('categoryname', self::ObjName."名称", '', '', 'asc');
		$table -> BindField('formatname', "格式", '', '', 'asc');
		$table -> BindField('parentname', "父分类", '', '', 'asc');
		$table -> BindField('categoryorder', "排序", '', '', 'asc');
		$table -> BindField('categoryisactive', "启用", 'bool', '', 'asc');
		$table -> BindField('cp', "操作", '');
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		if($this->_canedit)
		{
		$table -> BindHeadbar(" ".HtmlHelper::Button('创建'.self::ObjName,'link',
			urlhelper('action',$this->GetControllerId(),'add',"&parentid=$parentid")));
		}
		$table -> BindHeadbar(" | 分类名".HtmlHelper::Input('categoryname','','text'));
		$table -> BindHeadbar(" 按父分类".HtmlHelper::ListBox('parentid',$parentid,$cates,
			'categoryid','categoryname',array(''=>'全部分类','0'=>'主分类')));
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('formatid',
			$formatid,DBHelper::GetRecords(TFFormat::GetTable(),'','','formatorder'),
			'formatid','formatname','全部格式'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
				
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function deleteAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$id = $this->GetRequest()->GetInput('categoryid','int','没有指定要删除的'.self::ObjName);
		
		$obj = new TFCategory($id);
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
		$iconjava = "<script type='text/javascript'>
		function TFCateSelectIcon(iconid,categoryiconid){
			var imgval = showModalDialog('?controller=TFCategory&action=iconlist', '', 
			'dialogWidth:480px; dialogHeight:400px; status:0; help:0');
			if(imgval!=null){
			window.document.getElementById(iconid).src = '".TFCategory::TFCateIconDir."'+imgval;
			window.document.getElementById(categoryiconid).value = imgval;
			}
		}</script>";
		$iconlink = "javascript:TFCateSelectIcon('icon', 'categoryicon');";
		$iconurl = TFCategory::IconUrl($rs['categoryicon']);
		
		$cates = DBHelper::GetRecords(TFCategory::GetTable(),'parentid','0','categoryorder','asc');
		$form -> BindField('parentid', '所属父分类', $rs['parentid'],
			'select', $cates, 'categoryid','categoryname','无');
		
		$form -> BindField('formatid', '格式', $rs['formatid'],
			'radiolist', DBHelper::GetRecords(TFFormat::GetTable(),'','','formatorder'), 'formatid','formatname');
		
		$form -> BindField('categoryname', self::ObjName.'名(*)', $rs['categoryname'],'text');
		$form -> BindField('categoryorder', '显示顺序', $rs['categoryorder'],'text');
		$form -> BindField('icon', '当前图标', $iconurl,'image',
			array('width'=>200,'height'=>200,'link'=>$iconlink,'target'=>'_self'));
		$form -> BindField('categoryicon', '', $rs['categoryicon'],'hidden');
		$form -> BindField('upload_icon', '上传新图标'.$iconjava, '','file');
		$form -> BindField('categoryisactive', '是否启用', $rs['categoryisactive'],'check');
		$form -> BindField('categoryid','',$rs['categoryid'],'hidden');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
	}
	
	public function editAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$id = $this->GetRequest()->GetInput('categoryid', 'int', '没有指定要修改的'.self::ObjName);
		$obj = new TFCategory($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		
		$form = new HtmlForm('修改'.self::ObjName);
		$form -> BindField('categoryid', 'ID', $rs['categoryid'],'');
		
		$this->_bindFormFields($form, $rs);		
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function addAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$rs = array();
		$rs['parentid'] = $this->GetRequest()->GetInput('parentid','int');
				
		$form = new HtmlForm('新建'.self::ObjName);
		
		$this->_bindFormFields($form, $rs);
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
		
	private function _parseFormInput()
	{
		$input = array();
		
		$input['categoryname'] = $this->GetRequest()->GetInput('categoryname','string', self::ObjName.'名必须填写');
		$input['parentid'] = $this->GetRequest()->GetInput('parentid','int');
		$input['categoryorder'] = $this->GetRequest()->GetInput('categoryorder','int');
		$input['categoryisactive'] = $this->GetRequest()->GetInput('categoryisactive','int');
		
		$upload_iconid = TFCategory::UploadIcon('upload_icon', $this->GetAccount());
		$sel_icon = $this->GetRequest()->GetInput('categoryicon','string');
		$input['categoryicon'] = $upload_iconid ? $upload_iconid : $sel_icon;
		
		$input['formatid'] = $this->GetRequest()->GetInput('formatid','int');
			
		if ($input['parentid']==0)
		{
			$input['formatid'] = 0;
		}
		return $input;
	}
	
	public function doaddAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$input = $this->_parseFormInput();
		
		$referer = $this->GetRequest()->GetInput('referer');
		$object = new TFCategory();
		if($object->Insert($input))
		{
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
		
		$id = $this->GetRequest()->GetInput('categoryid', 'int', '没有指定要修改的'.self::ObjName);
		$input = $this->_parseFormInput();	
		if ($input['parentid']==0)
		{
			$input['categorytype'] = TFCategory::TFCateNone;
		}
				
		$object = new TFCategory($id);
		if (!$object->GetData())
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$referer = $this->GetRequest()->GetInput('referer');
		if($object->Update($input))
		{
			$backurl = urlhelper('action',$this->GetControllerId(),'list',"&parentid=".$input['parentid']);
			$this->Message("修改".self::ObjName."成功", $backurl);
		}
		else
		{
			$this->Message("修改".self::ObjName."失败", LOCATION_BACK);
		}
	}
	
	public function iconlistAction()
	{
		$dir = realpath(TFCategory::TFCateIconDir);
		$it = new FilesystemIterator($dir);
		$filelist = array();
		foreach ($it as $fileinfo) {
			$filename = $fileinfo->getFilename();
			$ext = strtolower(end(explode('.', $filename)));
			if(in_array($ext, array('bmp','jpg','jpeg','gif','png')))
			{
		    	$filelist[] = $fileinfo->getFilename();
			}
		}
		$this->GetView()->Assign('dir',TFCategory::TFCateIconDir);
		$this->GetView()->Assign('list',$filelist);
		$this->GetView()->Display('iconlist.htm');
		$this->GetApp()->AppExit();
	}
}