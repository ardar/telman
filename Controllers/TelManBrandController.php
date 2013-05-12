<?php

class TelManBrandController extends ControllerBase
{
	const ModuleRight='telmanbrand_view';
	const ModuleRightEdit='telmanbrand_edit';
	const ObjName = "奶粉品牌";
	
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		
	}
	
	public function listAction()
	{
		$this->CheckPermission(self::ModuleRight);
		$totalcount = 0;
		$filter_fields = array(
			'brandname' => '%like%', 
			);
		$list = DBHelper::GetJoinRecordSet(TelManBrand::GetTable(), 
			null, $this->GetFilterOption($filter_fields), 
			$this->GetSortOption('brandorder','asc'),
			$this->_offset, $this->_perpage, $totalcount);
		
		$table = new HtmlTable(self::ObjName."列表",'border');
		$table -> BindField('brandid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('brandname', self::ObjName."名", '', '', 'asc');
		$table -> BindField('totalprice', "总价格", '', '', 'asc');
		$table -> BindField('giveprice', "价格", '', '', 'asc');
		$table -> BindField('brandorder', "显示顺序", '', '', 'asc');
		$table -> BindField('brandisactive', "启用", 'bool', '', 'asc');
		$table -> BindField('desc', "备注");
		if(	$this->CheckPermission(self::ModuleRightEdit,false))
		{
		$table -> BindField('cp', "操作", 'cp', '','', 
			array(
			'设置'=>array('', 'edit','brandid'),
			'删除'=>array('', 'delete','brandid')));
		
		$table -> BindHeadbar(HtmlHelper::Button('创建'.self::ObjName,'link',
			urlhelper('action',$this->GetControllerId(),'add'),''));
		}
		else
		{
		$table -> BindField('cp', "操作", 'cp', '','', 
			array('查看'=>array('', 'edit','brandid')));
		}
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		$table -> BindHeadbar(" 按名字".HtmlHelper::Input('brandname','','text'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	private function _bindFormFields(HtmlForm $form, &$rs)
	{
		$form -> BindField('brandname', self::ObjName.'名', $rs['brandname'],'text');
		$form -> BindField('totalprice', '总价格', $rs['totalprice'],'text');
		$form -> BindField('giveprice', '总价格', $rs['giveprice'],'text');
		$form -> BindField('desc', '备注', $rs['desc'],'textarea');
		$form -> BindField('brandorder', '排序', $rs['brandorder'],'text');
		$form -> BindField('brandisactive', '是否启用', $rs['brandisactive'],'check');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
	}
	
	public function editAction()
	{
		$this->CheckPermission(self::ModuleRight);
		$canedit = $this->CheckPermission(self::ModuleRightEdit, false);
		
		$id = $this->GetRequest()->GetInput('brandid', 'int', '没有指定要修改的'.self::ObjName);
		$obj = new TelManBrand($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$form = new HtmlForm('修改'.self::ObjName);
		$form->SetReadOnly(!$canedit);
		$form -> BindField('brandid', 'ID', $rs['brandid'],'');
		
		$this->_bindFormFields($form, $rs);
		
		$form -> BindField('brandid','',$rs['brandid'],'hidden');
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function addAction()
	{
		$this->CheckPermission(self::ModuleRight);
		$this->CheckPermission(self::ModuleRightEdit);
		
		$rs = array();
		$rs['brandisactive'] = 1;
		$form = new HtmlForm('新建'.self::ObjName);
		$this->_bindFormFields($form, $rs);
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	private function _parseFormInput(&$configrs)
	{
		$input = array();
		$input['brandname'] = $this->GetRequest()->GetInput('brandname','string',self::ObjName.'名称必须填写');
		$input['totalprice'] = $this->GetRequest()->GetInput('totalprice','number');
		$input['giveprice'] = $this->GetRequest()->GetInput('giveprice','number');
		$input['desc'] = $this->GetRequest()->GetInput('desc','string');
	    $input['brandorder'] = $this->GetRequest()->GetInput('brandorder','int');
	    $input['brandisactive'] = $this->GetRequest()->GetInput('brandisactive','int');
	
		return $input;
	}
	
	public function doaddAction()
	{
		$this->CheckPermission(self::ModuleRight);
		$this->CheckPermission(self::ModuleRightEdit);
		
		$configrs = array();
		$input = $this->_parseFormInput($configrs);
		$rs = DBHelper::GetSingleRecord(TelManBrand::GetTable(), 'brandname', $input['brandname']);
		if ($rs)
		{
			$this->Message(self::ObjName."名称已存在  ".$input['brandname'], LOCATION_BACK);
		}
		$referer = $this->GetRequest()->GetInput('referer');
		$object = new TelManBrand();
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
		$this->CheckPermission(self::ModuleRight);
		$this->CheckPermission(self::ModuleRightEdit);
		
		$id = $this->GetRequest()->GetInput('brandid', 'int', '没有指定要编辑的'.self::ObjName);
		$configrs = array();
		$input = $this->_parseFormInput($configrs);
		
		$object = new TelManBrand($id);
		if (!$object->GetData())
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$count = DBHelper::GetRecordCount(TelManBrand::GetTable(), 
			" brandname='".$input['brandname']."' 
			and brandid<>'".$id."'");
		if ($count)
		{
			$this->Message("已存在同名记录  ".$input['brandname'], LOCATION_BACK);
		}
		$referer = $this->GetRequest()->GetInput('referer');
		
		if($object->Update($input))
		{
			$this->Message("修改".self::ObjName."成功", $referer);
		}
		else
		{
			$this->Message("修改".self::ObjName."失败", LOCATION_BACK);
		}
	}
	
	public function treeAction()
	{
		$this->CheckPermission('');
		$urlcontroller = $this->GetRequest()->GetInput('urlcontroller','string');
		$urlaction = $this->GetRequest()->GetInput('urlaction','string');
		$this->GetView()->Assign('treeview_url', 
			urlhelper('action', $this->GetControllerId(), 'xml', 
			"&urlaction=$urlaction&urlcontroller=$urlcontroller"));
		$this->GetView()->Display('_tree_view.htm');
		$this->GetApp()->AppExit();
	}
	
	public function xmlAction()
	{
		$this->CheckPermission('');
		$urlcontroller = $this->GetRequest()->GetInput('urlcontroller','string');
		$urlcontroller = $urlcontroller ? $urlcontroller : $this->GetControllerId();
		$urlaction = $this->GetRequest()->GetInput('urlaction','string');
		$urlaction = $urlaction ? $urlaction : 'list';
		$list = TelManBrand::GetActiveBrands();
		foreach($list as $key=>$rs)
		{
			$list[$key]['id'] = $rs['brandid'];
			$list[$key]['text'] = $rs['brandname'];
			$list[$key]['title'] = $rs['brandname'];
			$list[$key]['xml'] = null;
			$list[$key]['href'] = htmlentities(urlhelper('action',$urlcontroller,$urlaction,
				"&brandid=".$rs['brandid']));
			$list[$key]['target'] = 'main_right';
			//$list[$key]['image'] = './images/endnode.png';
		}
		if (true)
		{
			$root['id'] = 0;
			$root['text'] = '品牌列表';
			$root['title'] = '品牌列表';
			$root['xml'] = null;
			$root['href'] = htmlentities(urlhelper('action',$urlcontroller,
				$urlaction,""));
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