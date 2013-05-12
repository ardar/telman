<?php

class TelManBabyController extends ControllerBase
{
	const ModuleRight='telmantel';
	const ModuleRightEdit='telmantel_admin';
	const ObjName = "宝宝";
	
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		
		$this->CheckPermission(self::ModuleRight);
	}
	
	public function listAction()
	{
		$totalcount = 0;
		$filter_fields = array(
			'maminame' => '%like%', 
			);
		$join_options[] = array('table'=>TelManTel::GetTable(), 'left_field'=>'telid', 
			'right_field'=>'telid','select'=>'telnumber');
		$join_options[] = array('table'=>TelManBrand::GetTable(), 'left_field'=>'brandid', 
			'right_field'=>'brandid','select'=>'brandname');
		$join_options[] = array('table'=>Account::GetTable(), 'left_field'=>'ownerid', 
			'right_field'=>'userid','select'=>'username as ownername');
		
		$list = DBHelper::GetJoinRecordSet(TelManBaby::GetTable(), 
			$join_options, $this->GetFilterOption($filter_fields), $this->GetSortOption(),
			$this->_offset, $this->_perpage, $totalcount);
		
		$table = new HtmlTable(self::ObjName."列表",'border');
		$table -> BindField('babyid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('maminame', "妈咪名字", '', '', 'asc');
		$table -> BindField('telnumber', "手机号码", '', '', 'asc');
		$table -> BindField('brandname', "所接品牌", '', '', 'asc');
		$table -> BindField('birthday', "出生日期", 'date', '', 'asc');
		$table -> BindField('ownername', "所有者", '', '', 'asc');
		$table -> BindField('babyisactive', "启用", 'bool', '', 'asc');
		if(	$this->CheckPermission(self::ModuleRightEdit,false))
		{
		$table -> BindField('cp', "操作", 'cp', '','', 
			array(
			'设置'=>array('', 'edit','babyid'),
			'删除'=>array('', 'delete','babyid')));
		
		$table -> BindHeadbar(HtmlHelper::Button('创建'.self::ObjName,'link',
			urlhelper('action',$this->GetControllerId(),'add'),''));
		}
		else
		{
		$table -> BindField('cp', "操作", 'cp', '','', 
			array('查看'=>array('', 'edit','babyid')));
		}
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		$table -> BindHeadbar(" 按妈咪名字".HtmlHelper::Input('maminame','','text'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	private function _bindFormFields(HtmlForm $form, &$rs)
	{
	    $numberlist = TelManTel::GetNumberList($this->GetAccount()->GetId());
	    $brandlist = TelManBrand::GetActiveBrands();
		$form -> BindField('maminame', '妈咪名字', $rs['maminame'],'text');
		$form -> BindField('birthday', '出生日期', $rs['birthday'],'date_picker');
		$form -> BindField('telid', '使用号码', $rs['telid'],'radiolist',
		    $numberlist,'telid','title','未分配');
		$form -> BindField('brandid', '所接品牌', $rs['brandid'],'radiolist',
		    $brandlist,'brandid','brandname','未分配');
		$form -> BindField('submittime', '上报日期', $rs['submittime'],'date_picker');
		$form -> BindField('resulttime', '返回结果日期', $rs['resulttime'],'date_picker');
		$form -> BindField('desc', '备注', $rs['desc'],'textarea');
		$form -> BindField('babyisactive', '是否启用', $rs['babyisactive'],'check');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
	}
	
	public function editAction()
	{
		$canedit = $this->CheckPermission(self::ModuleRightEdit, false);
		
		$id = $this->GetRequest()->GetInput('babyid', 'int', '没有指定要修改的'.self::ObjName);
		$obj = new TelManBaby($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$form = new HtmlForm('修改'.self::ObjName);
		$form->SetReadOnly(!$canedit);
		$form -> BindField('babyid', 'ID', $rs['babyid'],'');
		
		$this->_bindFormFields($form, $rs);
		
		$form -> BindField('babyid','',$rs['babyid'],'hidden');
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
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
	
	private function _parseFormInput(&$configrs)
	{
		$input = array();
		$input['maminame'] = $this->GetRequest()->GetInput('maminame','string',self::ObjName.'名称必须填写');
		$input['telid'] = $this->GetRequest()->GetInput('telid','int');
		$input['brandid'] = $this->GetRequest()->GetInput('brandid','int');
		$birthday = $this->GetRequest()->GetInput('birthday','string');
		$input['birthday'] =  intval(strtotime($birthday));
		$submittime = $this->GetRequest()->GetInput('submittime','string');
		$input['submittime'] =  intval(strtotime($submittime));
		$resulttime = $this->GetRequest()->GetInput('resulttime','string');
		$input['resulttime'] = intval(strtotime($resulttime));
		$input['ownerid'] = $this->GetAccount()->GetId();
		$input['desc'] = $this->GetRequest()->GetInput('desc','string');
	    $input['babyisactive'] = $this->GetRequest()->GetInput('babyisactive','int');
	
		return $input;
	}
	
	public function doaddAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$configrs = array();
		$input = $this->_parseFormInput($configrs);
		$rs = DBHelper::GetSingleRecord(TelManBaby::GetTable(), 'maminame', $input['maminame']);
		if ($rs)
		{
			$this->Message(self::ObjName."名称已存在  ".$input['maminame'], LOCATION_BACK);
		}
		$referer = $this->GetRequest()->GetInput('referer');
		$object = new TelManBaby();
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
		$this->CheckPermission(self::ModuleRightEdit);
		
		$id = $this->GetRequest()->GetInput('babyid', 'int', '没有指定要编辑的'.self::ObjName);
		$configrs = array();
		$input = $this->_parseFormInput($configrs);
		
		$object = new TelManBaby($id);
		if (!$object->GetData())
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$count = DBHelper::GetRecordCount(TelManBaby::GetTable(), 
			" maminame='".$input['maminame']."' 
			and babyid<>'".$id."'");
		if ($count)
		{
			$this->Message("已存在同名记录  ".$input['maminame'], LOCATION_BACK);
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
}