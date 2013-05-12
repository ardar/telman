<?php

class AccountGroupController extends ControllerBase
{
	const ModuleRight='accountgroup';
	const ObjName = "用户组";

	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		$this->CheckPermission(self::ModuleRight);
	}
	
	public function listAction()
	{
		$totalcount = 0;
		
		$list = DBHelper::GetRecordSet(AccountGroup::GetTable(), $this->_offset, $this->_perpage, $totalcount);
		
		$table = new HtmlTable(self::ObjName."列表",'border');
		$table -> BindField('groupid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('groupname', "角色名", '', '', 'asc');
		$table -> BindField('groupdesc', "备注", '', '', 'asc');
		$table -> BindField('issystem', "系统组",'bool');
		$table -> BindField('isactive', "是否启用",'bool');
		$table -> BindField('cp', "操作", 'cp', '','', 
			array('设置'=>array('', 'edit','groupid')));//,'删除'=>array('delete','username')
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		$table -> BindHeadbar(" ".HtmlHelper::Button('创建'.self::ObjName,'link',urlhelper('action',$this->GetControllerId(),'add')));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	private function _buildModuleList()
	{
	    $list = array('ALL'=>'全部权限');
	    foreach (ModuleConfig::$LoadModules as $modulename)
	    {
	        $module = $this->GetApp()->GetExt($modulename);
	        if($module)
	        {
	            $list = array_merge($list, $module->GetPriveleges());
	        }
	    }
	    return $list;
	}

	private function _bindFormFields(HtmlForm $form, $rs)
	{
		$form -> BindField('groupname', '角色名', $rs['groupname'],'text');
		$form -> BindField('groupdesc', '备注', $rs['groupdesc'],'text');
		$form -> BindField('moduleright', '管理权限', $rs['currrights'],'checklist',$this->_buildModuleList());//TRACE($rs['currrights']);
		$form -> BindField('isactive', '是否启用', $rs['isactive'],'check');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
	}
	
	public function editAction()
	{
		$id = $this->GetRequest()->GetInput('groupid', 'int', '没有指定要修改的'.self::ObjName);
		$obj = new AccountGroup($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		if ($group->issystem)
		{
			$this->Message("角色 " .$group->groupname." 属于系统组,无法编辑 .", LOCATION_BACK);
		}
		$rs['currrights'] = $obj->GetModuleRights();
		
		$form = new HtmlForm('修改'.self::ObjName);
		$form -> BindField('groupid', 'ID', $rs['groupid'],'');
		$this->_bindFormFields($form, $rs);
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function addAction()
	{
		$rs = array();
		$rs['currrights'] = array();
		$form = new HtmlForm('新建'.self::ObjName);
		$this->_bindFormFields($form, $rs);
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function doaddAction()
	{		
		$input = $this->_parseFormInput();
		
		$rs = DBHelper::GetSingleRecord(AccountGroup::GetTable(), 'groupname', $input['groupname']);
		if ($rs)
		{
			$this->Message("角色名已存在  ".$input['groupname'], LOCATION_BACK);
		}
		$referer = $this->GetRequest()->GetInput('referer');
		$object = new AccountGroup();
		if($object->Insert($input))
		{
			$this->Message("新建".self::ObjName."成功", $referer);
		}
		else
		{
			$this->Message("新建".self::ObjName."失败", LOCATION_BACK);
		}
	}
	
	private function _parseFormInput()
	{
		$input['groupname'] = $this->GetRequest()->GetInput('groupname','string','角色名必须填写');
		$input['groupdesc'] = $this->GetRequest()->GetInput('groupdesc','string');
		$input['isactive'] = $this->GetRequest()->GetInput('isactive','int');
		$modulearray = $this->GetRequest()->GetInput('moduleright' , 'array');
		$modulestring = '';
		if($modulearray)
		{
			foreach ($modulearray as $val)
			{
				if($val=='ALL')
				{
					$modulestring = $val;
					break;
				}
				$modulestring .= $modulestring?('|'.$val):$val;
			}
		}
		$input['moduleright'] = $modulestring;
		return $input;
	}
	
	public function doeditAction()
	{
		$id = $this->GetRequest()->GetInput('groupid', 'string', '没有指定要编辑的角色');
		
		$input = $this->_parseFormInput();
		
		$object = new AccountGroup($id);
		if (!$object->GetData())
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
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