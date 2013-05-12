<?php

class FolderController extends ControllerBase
{
	const ModuleRight='media_view';
	const ModuleRightEdit='media_edit';
	const ObjName = "文件夹";
	
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		
		$this->CheckPermission(self::ModuleRight);
	}
	
	public function listAction()
	{
		$totalcount = 0;
		$filter_fields = array(
			'foldername' => '%like%', 
			'parentid' => '==',
			);
		$parentid = $this->GetRequest()->GetInput('parentid', 'int');
		$list = DBHelper::GetJoinRecordSet(Folder::GetTable(), 
			null, $this->GetFilterOption($filter_fields), $this->GetSortOption(),
			$this->_offset, $this->_perpage, $totalcount);
				
		$table = new HtmlTable(self::ObjName."列表",'border');
		$table -> BindField('folderid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('foldername', self::ObjName."名", '', '', 'asc');
		
		if($this->CheckPermission(self::ModuleRightEdit,false))
		{
		$table -> BindField('cp', "操作", 'cp', '','', 
			array(
			'设置'=>array('','edit','folderid'),
			'删除'=>array('','delete','folderid') ));
		$table -> BindHeadbar(" ".HtmlHelper::Button('创建'.self::ObjName,'link', 
			urlhelper('action',$this->GetControllerId(),'add','&parentid='.$parentid)));
		}
		
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		$table -> BindHeadbar(" 文件夹名".HtmlHelper::Input('foldername','','text'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function deleteAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$id = $this->GetRequest()->GetInput('folderid','int','没有指定要删除的'.self::ObjName);
		
		$obj = new Folder($id);
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
	
	public function moveAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$parentid = $this->GetRequest()->GetInput('parentid', 'int');
		$ids = $this->GetRequest()->GetInput('folderid', DT_ARRAY);
		if (!$ids)
		{
			$ids[] = $this->GetRequest()->GetInput('folderid','int','没有指定要移动的'.self::ObjName);
		}
		
		foreach ($ids as $id)
		{
			$obj = new Media($id);
			if (!$obj->GetData())
			{
				$this->Message("没有找到要移动的".self::ObjName." ".$id, LOCATION_REFERER);
			}
			if ($parentid==$obj->parentid)
			{
				$this->Message("目的文件夹和源文件夹相同 ", LOCATION_REFERER);
			}
			if(!$obj -> Move($parentid))
			{
				$this->Message("移动 ".self::ObjName."失败", LOCATION_REFERER);
			}
		}
		$this->Message("移动".self::ObjName."成功", LOCATION_REFERER);
	}
	
	private function _bindFormFields(HtmlForm $form, &$rs)
	{
		$parents = Folder::BuildComboList();
		
		$form -> BindField('foldername', '文件夹名称', $rs['foldername'],'text');
		
		$form -> BindField('parentid', '上级'.self::ObjName, $rs['parentid'],'select', $parents, 'folderid','title','根目录');		
		
		//$form -> BindField('deployareaid', '所属区域', $rs['deployareaid'],'select', $areas, 'deployareaid','deployareaname','无');		
			
		//$form -> BindField('isprivate', '是否私人文件夹', $rs['owneruserid']>0,'check');
		$form -> BindField('folderid','',$rs['folderid'],'hidden');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');		
	}
	
	public function editAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$id = $this->GetRequest()->GetInput('folderid', 'int', '没有指定要修改的'.self::ObjName);
		$obj = new Folder($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$form = new HtmlForm('修改'.self::ObjName);
		$form -> BindField('folderid', 'ID', $rs['folderid'],'');
		
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
		$input['foldername'] = $this->GetRequest()->GetInput('foldername','string','服务器名必须填写');
		$isprivate = $this->GetRequest()->GetInput('isprivate','int');
		$input['owneruserid'] = $isprivate ? $this->GetAccount()->GetId() : 0;
		$input['parentid'] = $this->GetRequest()->GetInput('parentid','int');
			
		return $input;
	}
	
	public function doaddAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$input = $this->_parseFormInput();
	
		$foldercount = DBHelper::GetRecordCount(Folder::GetTable(), 
			" parentid='".$input['parentid']."' 
			and foldername='".$input['foldername']."'");
		if ($foldercount)
		{
			$this->Message("该文件夹下已存在同名文件夹  ".$input['foldername'], LOCATION_BACK);
		}
		
		$referer = $this->GetRequest()->GetInput('referer');
		$object = new Folder();
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
		
		$referer = $this->GetRequest()->GetInput('referer');
		$id = $this->GetRequest()->GetInput('folderid', 'int', '没有指定要修改的'.self::ObjName);
		
		$input = $this->_parseFormInput();	
					
		$object = new Folder($id);
		if (!$object->GetData())
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		if($object->GetId()==$input['folderid'])
		{
			$this->Message("不能选择自己作为上级".self::ObjName." ".$id, LOCATION_BACK);
		}
	
		$foldercount = DBHelper::GetRecordCount(Folder::GetTable(), 
			" parentid='".$input['parentid']."' 
			and foldername='".$input['foldername']."'
			and folderid<>'".$object->GetId()."'");
		if ($foldercount)
		{
			$this->Message("该文件夹下已存在同名文件夹  ".$input['foldername'], LOCATION_BACK);
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
	
	public function treeAction()
	{
		$parentid = $this->GetRequest()->GetInput('parentid','int');
		$urlaction = $this->GetRequest()->GetInput('urlaction','string');
		$showfile = $this->GetRequest()->GetInput('showfile','int');
		$mediatype = $this->GetRequest()->GetInput('mediatype','string');
		$this->GetView()->Assign('treeview_url', 
			urlhelper('action', $this->GetControllerId(), 'xml', 
			"&parentid=$parentid&urlaction=$urlaction&showfile=$showfile&mediatype=$mediatype"));
		$this->GetView()->Display('_tree_view.htm');
		$this->GetApp()->AppExit();
	}
	
	public function xmlAction()
	{
		$parentid = $this->GetRequest()->GetInput('parentid','int');
		$urlaction = $this->GetRequest()->GetInput('urlaction','string');
		$urlaction = $urlaction ? $urlaction : 'list';
		$showfile = $this->GetRequest()->GetInput('showfile','int');
		$mediatype = $this->GetRequest()->GetInput('mediatype','string');
		$list = DBHelper::GetRecords(Folder::GetTable(), 'parentid', $parentid, Folder::GetPK(), 'asc');
		foreach($list as $key=>$rs)
		{
			$list[$key]['id'] = $rs['folderid'];
			$list[$key]['text'] = $rs['foldername'];
			$list[$key]['title'] = $rs['foldername'];
			$list[$key]['xml'] = htmlentities(urlhelper('action',$this->GetControllerId(),$this->GetActionId(),
				"&parentid=".$rs['folderid']."&urlaction=$urlaction&showfile=$showfile&mediatype=$mediatype"));
			$list[$key]['href'] = htmlentities(urlhelper('action','Media',$urlaction,
				"&folderid=".$rs['folderid']."&mediatype=$mediatype"));
			$list[$key]['target'] = 'main_right';
			//$list[$key]['image'] = './images/endnode.png';
		}
		if($showfile)
		{
			$files = DBHelper::GetRecords(Media::GetTable(), 'folderid',$parentid, 'medianame','asc');
			foreach($files as $key=>$rs)
			{
				$rs['id'] = -$rs['mediaid'];
				$rs['text'] = $rs['medianame'];
				$rs['title'] = $rs['medianame'];
				$rs['xml'] = null;
				$rs['href'] = htmlentities(urlhelper('action','Media','edit',"&mediaid=".$rs['mediaid']));
				$rs['target'] = 'main_right';
				$rs['image'] = './images/'.$rs['mediatype'].'.png';
				
				$rs['hover'] = htmlentities(Media::MediaThumbnailImage($rs, '', 100, 100));
				$list[] = $rs;
			}
		}
		if ($parentid==0)
		{
			$root['id'] = 0;
			$root['text'] = '媒体库';
			$root['title'] = '媒体库';
			$root['xml'] = null;
			$root['href'] = htmlentities(urlhelper('action','Media',
				$urlaction,"&folderid=-1"));
			$root['target'] = 'main_right';
		}
		ob_clean();
		header("Content-Type: text/xml");
		$this->GetView()->Assign('root', $root);
		$this->GetView()->Assign('list', $list);
		$this->GetView()->Display('_tree_view.xml');
		$this->GetApp()->AppExit();
	}
	
	public function indexAction()
	{
		$showfile = $this->GetRequest()->GetInput('showfile','int');
		$mediatype = $this->GetRequest()->GetInput('mediatype','string');
		$left_url = urlhelper('action',$this->GetControllerId(), 'tree', "&showfile=$showfile&mediatype=$mediatype");
		$right_url = urlhelper('action','Media', 'list');
		$this->GetView()->Assign('left_url', $left_url);
		$this->GetView()->Assign('right_url', $right_url);
		$this->GetView()->Display('_tree_frame.htm');
		$this->GetApp()->AppExit();
	}
	
	/**
	 * 菜单中选择文件
	 */	
	public function picktreeAction()
	{
		$parentid = $this->GetRequest()->GetInput('parentid','int');
		$showfile = $this->GetRequest()->GetInput('showfile','int');
		$return_field = $this->GetRequest()->GetInput('return_field','string');
		$mediatype = $this->GetRequest()->GetInput('mediatype','string');
		$url = urlhelper('action', $this->GetControllerId(), 'pickxml', 
			"&parentid=$parentid&return_field=$return_field&showfile=$showfile&mediatype=$mediatype");
		//echo $url;die();
		$this->GetView()->Assign('treeview_url', $url);
		$this->GetView()->Display('_tree_view.htm');
		$this->GetApp()->AppExit();
	}
	
	public function pickxmlAction()
	{
		$parentid = $this->GetRequest()->GetInput('parentid','int');
		$return_field = $this->GetRequest()->GetInput('return_field','string');
		$showfile = $this->GetRequest()->GetInput('showfile','int');
		$mediatype = $this->GetRequest()->GetInput('mediatype','string');
		$list = DBHelper::GetRecords(Folder::GetTable(), 'parentid', $parentid, Folder::GetPK(), 'asc');
		foreach($list as $key=>$rs)
		{
			$list[$key]['id'] = $rs['folderid'];
			$list[$key]['text'] = $rs['foldername'];
			$list[$key]['title'] = $rs['foldername'];
			$list[$key]['xml'] = htmlentities(urlhelper('action',$this->GetControllerId(),$this->GetActionId(),
				"&parentid=".$rs['folderid']."&return_field=$return_field&showfile=$showfile&mediatype=$mediatype"));
			$list[$key]['href'] = htmlentities("javascript:window.parent.document.getElementById('$return_field').value='';");
			$list[$key]['target'] = 'main_hidden';
		}
		if($showfile)
		{
			$allowtypes = explode('|',$mediatype);
			$filter_options[] = array('field'=>'isaudited', 'value'=>'1', 'op'=>'=');
			$filter_options[] = array('field'=>'folderid', 'value'=>$parentid, 'op'=>'=');
			$sort_options[] = " medianame asc";
			$icount = 0;
			$files = DBHelper::GetJoinRecordSet(Media::GetTable(), null, $filter_options, $sort_options, 
				0, 999999, $icount);
			//	print_r($files);
			foreach($files as $key=>$rs)
			{
				if ($mediatype && !in_array($rs['mediatype'],$allowtypes))
				{
					continue;
				}
				$rs['id'] = -$rs['mediaid'];
				$rs['text'] = $rs['medianame'];
				$rs['title'] = $rs['medianame'];
				$rs['xml'] = null;
				$rs['href'] = htmlentities(
					"javascript:window.parent.document.getElementById('$return_field').value='".$rs['mediaid']."';");
				$rs['target'] = 'main_hidden';
				$rs['image'] = './images/'.$rs['mediatype'].'.png';
				$imgurl = Media::MediaThumbnailUrl($rs);
				if($imgurl)
				{
					$rs['hover'] = htmlentities(HtmlHelper::Image($imgurl,'','',100,100));
				}
				$list[] = $rs;
			}
		}
		if ($parentid==0)
		{
			$root['id'] = 0;
			$root['text'] = '媒体库';
			$root['title'] = '媒体库';
			$root['xml'] = null;
			$root['href'] = htmlentities("javascript:window.parent.document.getElementById('$return_field').value='';return false;");
			$root['target'] = 'main_hidden';
		}
		ob_clean();
		header("Content-Type: text/xml");
		$this->GetView()->Assign('root', $root);
		$this->GetView()->Assign('list', $list);
		$this->GetView()->Assign('return_field', $return_field);
		$this->GetView()->Display('_tree_view.xml');
		$this->GetApp()->AppExit();
	}
}