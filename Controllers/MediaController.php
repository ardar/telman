<?php
class MediaController extends ControllerBase
{
	const ModuleRight='media_view';
	const ModuleRightEdit='media_edit';
	const ModuleRightAudit='media_audit';
	const ObjName = "媒体文件";
	
	public function Init(IApplication $app, IRequest& $request, IView& $view)
	{
		parent::Init($app, $request, $view);
		
		$this->CheckPermission(array(self::ModuleRight,self::ModuleRightEdit,self::ModuleRightAudit));
	}
	
	public function listAction()
	{
		$canaudit = $this->CheckPermission(self::ModuleRightAudit,false);
		$canedit = $this->CheckPermission(self::ModuleRightEdit,false);
		
		$totalcount = 0;
		$join_options[] = array('table'=>Folder::GetTable(), 'on_field'=>'folderid','select'=>'foldername');
		$join_options[] = array('table'=>Account::GetTable(), 'left_field'=>'uploaderid', 
			'right_field'=>'userid','select'=>'username as uploadername, realname as uploaderrealname');
		$join_options[] = array('table'=>SysCode::GetTable(), 'left_field'=>'mediatype', 
			'right_field'=>'syscode','select'=>'syscodename as mediatypename, syscode as mediatypecode');
		
		$filter_fields = array(
			'medianame' => '%like%', 
			'isaudited' => '=',
			'mediatype' => '=',
			);
		$filter_options = $this->GetFilterOption($filter_fields);
		
		$mediatype = $this->GetRequest()->GetInput('mediatype','string');

		$folderid = $this->GetRequest()->GetInput('folderid', 'int',null,-1);
		if ($folderid!=0)
		{
			$filter_options[] = array('table'=>null, 'field'=>'folderid', 'value'=>$folderid, 'op'=>'=');
		}
		$foldername = "搜索全部目录";
		if($folderid>0)
		{
			$folder = new Folder($folderid);
			$folderrs = $folder->GetData();
			$foldername = $folderrs['foldername'];
		}
		elseif ($folderid==-1)
		{
			$foldername = "根目录";
		}
		
		$folders = Folder::BuildComboList();
		
		$list = DBHelper::GetJoinRecordSet(Media::GetTable(), 
				$join_options, $filter_options, 
				$this->GetSortOption('mediaid','desc'),
				$this->_offset, $this->_perpage, $totalcount);
		foreach($list as $key => $rs)
		{
			$list[$key]['duration'] = OpenDev::GetDurationDesc($rs['duration']);
			$list[$key]['filesize'] = OpenDev::GetFileSizeDesc($rs['filesize']);
			$list[$key]['width'] = $rs['width'] ? ($rs['width'].'x'.$rs['height']) : '';
			
			$list[$key]['foldername'] = HtmlHelper::Link($rs['foldername'], 
				urlhelper('action',$this->GetControllerId(),'list','&folderid='.$rs['folderid']));
			$list[$key]['mediatype'] = HtmlHelper::Image("images/".$rs['mediatype'].".png",'','','16','16');
//			if ($canaudit)
//			{
//				if ($rs['isaudited'])
//				{
//					$list[$key]['cp'] = HtmlHelper::Link('取消审核',
//						urlhelper('action',$this->GetControllerId(),'audit','&isaudited=0&mediaid='.$rs['mediaid']),
//						'','');
//				}
//				else
//				{
//					$list[$key]['cp'] = HtmlHelper::Link('通过审核',
//						urlhelper('action',$this->GetControllerId(),'audit','&isaudited=1&mediaid='.$rs['mediaid']),
//						'','');
//				}
//			}
			$color = $rs['isaudited'] ? "green" : "red";
			$style = " style='color:$color;display:block'";
			$list[$key]['medianame'] = "<b>".HtmlHelper::Link($rs['medianame'],
				urlhelper('action',$this->GetControllerId(),'edit','&mediaid='.$rs['mediaid']),
				'','',$style).'</b>';
			$list[$key]['preview'] = Media::MediaThumbnailImage($rs, 
				urlhelper('action',$this->GetControllerId(),'preview','&mediaid='.$rs['mediaid']),60,60);
		}
		
		$table = new HtmlTable(self::ObjName."列表",'border');
		$table -> BindField('mediaid', "ID", 'checkbox', '', 'asc');
		$table -> BindField('mediaid', "ID", '', 'list_pk', 'asc');
		$table -> BindField('preview', "预览", '');
		$table -> BindField('medianame', "名称", '', '', 'asc',' style="width:200"','left');
		$table -> BindField('mediatype', "类型", '', '', 'asc');
		$table -> BindField('mimetype', "MIME", '', '', 'asc');
		$table -> BindField('duration', "播放时间", '', '', 'asc');
		$table -> BindField('width', "分辨率", '', '', 'asc');
		$table -> BindField('filesize', "文件大小", '', '', 'asc');
		$table -> BindField('foldername', "文件夹", '', '', 'asc');
		if ($canaudit)
		{
		//$table -> BindField('cp', "操作", '', '','');
		}
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		$table -> BindToolbar("<b>当前:［".$foldername."］</b>");
		if ($folderid && $canedit)
		{
		$table -> BindToolbar(" | ".HtmlHelper::Button('创建子目录','link', 
			urlhelper('action','Folder','add','&parentid='.$folderid)));
		}
		if($folderid>0 && $canedit)
		{
		$table -> BindToolbar(" | ".HtmlHelper::Button('重命名/移动文件夹','link', 
			urlhelper('action','Folder','edit','&folderid='.$folderid)));
		$table -> BindToolbar(" | ".HtmlHelper::Button('删除当前文件夹','button', 
			"onclick=\"if(!confirm('确认删除当前文件夹?'))return false;window.location.href='"
			.urlhelper('action','Folder','delete','&folderid='.$folderid)."'\""));
		$table -> BindHeadbar(" | ".HtmlHelper::Button('添加'.self::ObjName,'link', 
			urlhelper('action',$this->GetControllerId(),'add','&folderid='.$folderid)));
		}
		$mediatypelist = SysCode::GetCodeList(SysCode::MediaType);

		$auditarray = array('1'=>'已审核','0'=>'未审核');
		$table -> BindHeadbar(" | 按文件名".HtmlHelper::Input('medianame','','text'));
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('folderid',$folderid,$folders,'folderid','title','全部目录'));
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('mediatype',$mediatype,
			$mediatypelist,'syscode','syscodename','全部类型'));
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('isaudited','',$auditarray,'','','按审核状态'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		
		if ($canaudit || $canedit)
		{
		$table -> BindFootbar("将选中项:");
		$table -> BindFootbar(HtmlHelper::Input('isaudited', 'none','hidden'));
		$table -> BindFootbar(HtmlHelper::Input(FIELD_ACTION, 'none','hidden'));
		$table -> BindFootbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		}
		if ($canaudit)
		{
		$table -> BindFootbar(" | ".HtmlHelper::Button('通过审核', 'button',
			"onclick=\"if(!confirm('是否通过审核选中的条目'))return false;form.action.value='audit';form.isaudited.value='1';form.submit();\""));
		$table -> BindFootbar(" | ".HtmlHelper::Button('取消审核', 'button',
			"onclick=\"if(!confirm('是否取消审核选中的条目'))return false;form.action.value='audit';form.isaudited.value='0';form.submit();\""));
		}
		if($canedit)
		{
		$table -> BindFootbar(" | ".HtmlHelper::Button('删除', 'button',
			"onclick=\"if(!confirm('是否确认删除选中的条目'))return false;form.action.value='delete';form.submit();\""));
		
		$table -> BindFootbar(" | ".HtmlHelper::Button('移动到目录', 'button',
			"onclick=\"if(!confirm('是否确认移动选中的条目'))return false;form.action.value='move';form.submit();\""));	
		$table -> BindFootbar(" ".HtmlHelper::ListBox('folderid',$folderid,$folders,'folderid','title','根目录'));
		}
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function sellistAction()
	{
		$this->CheckPermission(array(self::ModuleRight,PlaylistController::ModuleRightEdit));
		
		$canupload = $this->CheckPermission(self::ModuleRightEdit, UnForceDown);
		
		$return_field = $this->GetRequest()->GetInput('return_field','string');
		$mediatype = $this->GetRequest()->GetInput('mediatype','string');
		if ($mediatype)
		{
			$allowexts = Media::GetMediaTypeExts($mediatype);
		}
		
		$folders = Folder::BuildComboList();
		
		$totalcount = 0;
		$join_options[] = array('table'=>Folder::GetTable(), 'on_field'=>'folderid','select'=>'foldername');
		$join_options[] = array('table'=>Account::GetTable(), 'left_field'=>'uploaderid', 
			'right_field'=>'userid','select'=>'username as uploadername, realname as uploaderrealname');
		
		$filter_fields = array(
			'medianame' => '%like%', 
			);
		$filter_options = $this->GetFilterOption($filter_fields);
		$filter_options[] = array('table'=>Media::GetTable(), 'field'=>'isaudited', 'value'=>'1', 'op'=>'=');
		
		$folderid = $this->GetRequest()->GetInput('folderid', 'int',null,-1);
		if ($folderid!=0)
		{
			$filter_options[] = array('table'=>null, 'field'=>'folderid', 'value'=>$folderid, 'op'=>'=');
		}
		$foldername = "搜索全部目录";
		if($folderid>0)
		{
			$folder = new Folder($folderid);
			$folderrs = $folder->GetData();
			$foldername = $folderrs['foldername'];
		}
		elseif ($folderid==-1)
		{
			$foldername = "根目录";
		}
				
		$list = DBHelper::GetJoinRecordSet(Media::GetTable(), 
				$join_options, $filter_options, 
				$this->GetSortOption('mediaid','desc'),
				$this->_offset, $this->_perpage, $totalcount);
		foreach($list as $key => $rs)
		{
			if (count($allowexts)>0 && !in_array($rs['extname'], $allowexts))
			{
				unset($list[$key]);
				continue;
			}
			$list[$key]['duration'] = OpenDev::GetDurationDesc($rs['duration']);
			$list[$key]['filesize'] = OpenDev::GetFileSizeDesc($rs['filesize']);
			$list[$key]['width'] = $rs['width'] ? ($rs['width'].'x'.$rs['height']) : '';
			$ret_val = $rs['mediaid'].'*'.$rs['medianame'].'*'.$rs['savefilepath'];
			$link = "javascript:window.parent.returnUp('$ret_val');";
			$list[$key]['medianame'] = "<b>".HtmlHelper::Link($rs['medianame'],$link,'','').'</b>';
			$img  = Media::MediaThumbnailImage($rs, '', 50, 50);
			$list[$key]['preview']  = HtmlHelper::Link($img, $link, '', '');
		}
		
		$table = new HtmlTable(self::ObjName."列表 ",'border');
		$table -> BindField('mediaid', "ID", '', 'list_pk', 'asc');
		$table -> BindField('preview', "预览", '', '','');
		$table -> BindField('medianame', "名称", '', '', 'asc',' style="width:40%"','left');
		$table -> BindField('duration', "播放时长", '', '', 'asc');
		$table -> BindField('width', "分辨率", '', '', 'asc');
		$table -> BindField('filesize', "文件大小", '', '', 'asc');
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		$table -> BindToolbar("<b>当前目录:［".$foldername."］</b>");

		if($folderid>0 && $canupload)
		{
		$table -> BindToolbar(" | ".HtmlHelper::Button('上传'.self::ObjName,'link', 
			urlhelper('action',$this->GetControllerId(),'add','&folderid='.$folderid)));
		}
		$table -> BindHeadbar(" | 按文件名".HtmlHelper::Input('medianame','','text'));
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('folderid',$folderid,$folders,'folderid','title','查找全部目录'));
		$table -> BindHeadbar(HtmlHelper::Input('mediatype', $mediatype,'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
				
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function deleteAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		$ids = $this->GetRequest()->GetInput('mediaid', DT_ARRAY);
		if (!$ids)
		{
			$ids[] = $this->GetRequest()->GetInput('mediaid','int','没有指定要删除的'.self::ObjName);
		}
		
		foreach ($ids as $id)
		{
			$obj = new Media($id);
			if (!$obj->GetData())
			{
				$this->Message("没有找到要删除的".self::ObjName." ".$id, LOCATION_REFERER);
			}
			$devicers = $obj->GetData();
			if(!$obj -> Delete())
			{
				$this->Message("删除 ".self::ObjName."失败", LOCATION_REFERER);
			}
			FWLog::MediaLog(FWLog::ActionDelete, null,$devicers, '',0,
				$this->GetAccount()->GetId(), $devicers);
		}
		$this->Message("删除".self::ObjName."成功", LOCATION_REFERER);
	}
	
	public function auditAction()
	{
		$this->CheckPermission(self::ModuleRightAudit);
		$isaudited = $this->GetRequest()->GetInput('isaudited', 'int');
		$actionname = $isaudited ? "通过审核" : '取消审核';
		$ids = $this->GetRequest()->GetInput('mediaid', DT_ARRAY);
		if (!$ids)
		{
			$ids[] = $this->GetRequest()->GetInput('mediaid','int','没有指定要$actionname的'.self::ObjName);
		}
		
		foreach ($ids as $id)
		{
			$obj = new Media($id);
			if (!$obj->GetData())
			{
				$this->Message("没有找到要$actionname的".self::ObjName." ".$id, LOCATION_REFERER);
			}
			if($obj->isaudited!=$isaudited)
			{
				if(!$obj -> Audit($this->GetAccount(), $isaudited))
				{
					$this->Message($actionname.self::ObjName."失败", LOCATION_REFERER);
				}
				FWLog::MediaAuditLog($isaudited, $obj->GetData(), $this->GetAccount()->GetId());
			}
		}
		$this->Message($actionname.self::ObjName."成功", LOCATION_REFERER);
	}
	
	public function moveAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		$folderid = $this->GetRequest()->GetInput('folderid', 'int', '请指定移动的目的文件夹');
		$isoverwrite = $this->GetRequest()->GetInput('isoverwrite', 'int');
		$ids = $this->GetRequest()->GetInput('mediaid', DT_ARRAY);
		if (!$ids)
		{
			$ids[] = $this->GetRequest()->GetInput('mediaid','int','没有指定要移动的'.self::ObjName);
		}
		$folder = new Folder($folderid);
		if(!$folder->GetData())
		{
			$this->Message("没有找到目的文件夹 ".$folderid, LOCATION_REFERER);
		}
		
		foreach ($ids as $id)
		{
			$obj = new Media($id);
			if (!$obj->GetData())
			{
				$this->Message("没有找到要移动的".self::ObjName." ".$id, LOCATION_REFERER);
			}
			if ($folderid==$obj->folderid)
			{
				$this->Message("目的文件夹和源文件夹相同 ", LOCATION_REFERER);
			}
			if(!$obj -> Move($folderid, $isoverwrite))
			{
				$this->Message("移动 ".self::ObjName."失败", LOCATION_REFERER);
			}
			
			FWLog::MediaLog(FWLog::ActionMove, "", $obj->GetData(),
				$folder->foldername, $folder->GetId(), 
				$this->GetAccount()->GetId(), $this->GetData());
		}
		$this->Message("移动".self::ObjName."成功", LOCATION_REFERER);
	}
	
	private function _bindFormFields(HtmlForm $form, &$rs)
	{
		$parents = Folder::BuildComboList();
		
		$form -> BindField('medianame', self::ObjName.'名称', $rs['medianame'],'text');
		$form -> BindField('folderid', '所属文件夹', $rs['folderid'],'select', $parents, 'folderid','title','根目录');		
			
		$form -> BindField('mediaid','',$rs['mediaid'],'hidden');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');		
	}
	
	public function editAction()
	{
		$this->CheckPermission(array(self::ModuleRightEdit,self::ModuleRight,self::ModuleRightAudit));
		$id = $this->GetRequest()->GetInput('mediaid', 'int', '没有找到'.self::ObjName);
		$obj = new Media($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到".self::ObjName." ".$id, LOCATION_BACK);
		}
		if ($obj->folderid>0)
		{
			$folder = new Folder($obj->folderid);
			$folder -> GetData();
			//$folder->GetParentList();
		}
		//$mediainfo = new MediaInfo($obj->savefilepath);
		//echo "<pre>".$mediainfo -> GetInfo(false)."</pre>";
		
		$form = new HtmlForm(self::ObjName." 详情");
		
		$this->_bindFormFields($form, $rs);
		$form -> SetReadOnly(!$this->CheckPermission(self::ModuleRightEdit,false));
		$form -> BindField('mediaid', 'ID', $rs['mediaid'],'');
		$form -> BindField('medianame', self::ObjName.'名称', $rs['medianame'],'');
		$form -> BindField('extname', '原始文件名', $rs['originalname'],'');
		$form -> BindField('mimetypename', '媒体类型', 
			SysCode::GetCodeName(SysCode::MediaType, $rs['mediatype']),'');
		$form -> BindField('mimetype', 'MIME', $rs['mimetype'],'');
		$form -> BindField('filesize', '文件大小', OpenDev::GetFileSizeDesc($rs['filesize']),'');
		$form -> BindField('imagesize', '图像尺寸', $rs['width'].' x '.$rs['height'],'');
		$form -> BindField('ratio', '宽高比', $rs['ratio'],'');
		$form -> BindField('duration', '播放时间',  OpenDev::GetDurationDesc($rs['duration']),'');
		$form -> BindField('mediainfo', '编码信息', $rs['mediainfo'],'');
		$form -> BindField('filehash', '哈希值', $rs['filehash'],'');
		$form -> BindField('','上传用户',$rs['uploadername'].' ('.$rs['uploaderrealname'].')','');
		$form -> BindField('','上传时间',$rs['uploadtime'],'datetime');
		$color = $rs['isaudited'] ? 'green' : 'red';
		$audittext = $rs['isaudited'] ? '已通过审核' : '未通过审核';
		$form -> BindField('','审核状态',"<font color=$color>$audittext</a>",'');
		if ($rs['isaudited'])
		{
		//$form -> BindField('','审核用户',$rs['auditerid'],'');
		$form -> BindField('','审核时间',$rs['audittime'],'datetime');
		}
		
		$preview = $obj->GetThumbnailUrl();
		if ($preview)
		{
			$preview_img = HtmlHelper::Image($preview)."\n";
		}
		$btns = "<span style='margin:3px'>".HtmlHelper::Link(' 预览... ',
					urlhelper('action',$this->GetControllerId(),'preview',"&mediaid=".$obj->GetId(), 
					'_blank', 'button'), '_blank', 'button')
				.HtmlHelper::Link(' 下载文件... ',$rs['savefilepath'], '_blank', 'button')."</span>";
		$form -> BindField('', '文件', $preview_img.$btns,'');
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
		
		$refs = $obj->GetReferences();	
			
//			
		$table1 = new HtmlTable("模板布局引用",'border');
		$table1 -> BindField('type', "引用类型", '', 'list_pk');
		$table1 -> BindField('templatename', "所属模板", '', '');
		$table1 -> BindField('zonename', "所属区域", '', '', 'asc','');
		$table1 -> BindField('cp', "操作", 'cp', '','', 
			array(
			'设置'=>array('MediaTemplate','edit','templateid','frame_main') ));
		$table1 -> BindData($refs['template']);
		$this->GetView()->Assign('table', $table1);
		$this->GetView()->Display('_data_table.htm');
//			
		$table2 = new HtmlTable("节目包布局引用",'border');
		$table2 -> BindField('type', "引用类型", '', 'list_pk');
		$table2 -> BindField('playlistname', "所属节目包", '', '');
		$table2 -> BindField('zonename', "所属区域", '', '', 'asc','');
		$table2 -> BindField('cp', "操作", 'cp', '','', 
			array(
			'查看节目包'=>array('Playlist','playedit','playlistid','frame_main') ));
		$table2 -> BindData($refs['playlist']);
		$this->GetView()->Assign('table', $table2);
		$this->GetView()->Display('_data_table.htm');
			
//		$table3 = new HtmlTable("节目引用",'border');
//		$table3 -> BindField('type', "引用类型", '', 'list_pk');
//		$table3 -> BindField('playlistname', "所属节目包", '', '');
//		$table3 -> BindField('zonename', "所属区域名称", '', '');
//		$table3 -> BindData($refs['item']);
//		$this->GetView()->Assign('table', $table3);		
//		$this->GetView()->Display('_data_table.htm');
	}
	
	public function previewAction()
	{
		$this->CheckPermission(array(self::ModuleRightEdit,self::ModuleRight,self::ModuleRightAudit));
		$id = $this->GetRequest()->GetInput('mediaid', 'int', '没有指定'.self::ObjName);
		$obj = new Media($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到".self::ObjName." ".$id, LOCATION_BACK);
		}
		
		$this->GetView()->Assign('rs', $rs);
		$this->GetView()->Display('media_preview.htm');
	}
		
	public function addAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		$rs = array();
		$rs['folderid'] = $this->GetRequest()->GetInput('folderid','int','请选择所属文件夹');
		
		$form = new HtmlForm('新建'.self::ObjName);
		
		$this->_bindFormFields($form, $rs);
		$form -> BindField('savefilepath', '上传文件', $rs['savefilepath'],'requirefile',
			'','','','',
			"onchange=\"medianame.value=GetFileNameFromPath(this.value);\"");
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	private function _parseFormInput()
	{
		$input = array();
		$input['medianame'] = $this->GetRequest()->GetInput('medianame','string','媒体文件名必须填写');
		$input['folderid'] = $this->GetRequest()->GetInput('folderid','int','请选择所属文件夹');
			
		return $input;
	}
	
	public function doaddAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		$referer = $this->GetRequest()->GetInput('referer');
		
		$input = $this->_parseFormInput();
	
		$foldercount = DBHelper::GetRecordCount(Media::GetTable(), 
			" folderid='".$input['folderid']."' 
			and medianame='".$input['medianame']."'");
		if ($foldercount)
		{
			$this->Message("该文件夹下已存在同名文件  ".$input['medianame'], LOCATION_BACK);
		}
		
		$uploadfile = new UploadFile('savefilepath');
		$uploadfile->HasValue("必须上传媒体文件");
		$uploadfile->Validate(Media::CheckFileType, Media::CheckFileExts, 0, 102400);
	
		$input['filehash'] = $uploadfile->GetHash();
		$hashcount = DBHelper::GetRecordCount(Media::GetTable(), 
			"filehash='".$input['filehash']."'");
		if ($hashcount)
		{
			$this->Message("已存在相同内容文件  HASH:".$input['filehash'], LOCATION_BACK);
		}
		
		$uploadfile->Save(AppHost::GetAppSetting()->UploadDir, UploadFile::PathMonthExt, UploadFile::RenameMd5);
		$input['savefilepath'] = $uploadfile->savefilename;
		$input['originalname'] = $uploadfile->filename;
		$input['extname'] = $uploadfile->ext;
		$input['mimetype'] = $uploadfile->mimetype;
		$input['filesize'] = $uploadfile->filesize;	
		$input['mediatype'] = Media::GetMediaTypeByExt($uploadfile->ext);
		
		$input['uploaderid'] = $this->GetAccount()->GetId();
		$input['uploadtime'] = $this->GetApp()->GetEnv('timestamp');
		
		if ($input['mediatype']==Media::MediaTypeImage 
			|| $input['mediatype']==Media::MediaTypeVideo
			|| $input['mediatype']==Media::MediaTypeAudio)
		{
			$mediainfo = new MediaInfo($input['savefilepath']);
			if($mediainfo -> GetInfo())
			{
				$input['width'] = $mediainfo->Width;
				$input['height'] = $mediainfo->Height;
				$input['duration'] = $mediainfo->DurationTime;
				$input['ratio'] = $mediainfo->Ratio;
				$input['mediainfo'] = $mediainfo->Format;
			}
		}
		
		$object = new Media();
		if($object->Insert($input))
		{
			$uploadfile->Commit();
			$object->GetData();
			$object->SaveThumbnail();
			FWLog::MediaLog(FWLog::ActionAdd, null,$object->GetData(),'',0, 
				$this->GetAccount()->GetId(),$object->GetData());
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
		$id = $this->GetRequest()->GetInput('mediaid', 'int', '没有指定要修改的'.self::ObjName);
				
		$input = $this->_parseFormInput();
	
		$foldercount = DBHelper::GetRecordCount(Media::GetTable(), 
			" folderid='".$input['folderid']."' 
			and medianame='".$input['medianame']."' 
			and mediaid<>'".$id."'");
		if ($foldercount)
		{
			$this->Message("该文件夹下已存在同名文件  ".$input['medianame'], LOCATION_BACK);
		}
		$object = new Media($id);
		if (!$object->GetData())
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$referer = $this->GetRequest()->GetInput('referer');
			
		if($object->Update($input))
		{
			FWLog::MediaLog(FWLog::ActionEdit,null,$object->GetData(),'',0,
				 $this->GetAccount()->GetId(),$object->GetData());
			$this->Message("修改".self::ObjName."成功", $referer);
		}
		else
		{
			//$uploadfile->Rollback();
			$this->Message("修改".self::ObjName."失败", LOCATION_BACK);
		}
	}
	
	public function indexAction()
	{
		$this->CheckPermission(self::ModuleRight);
		$left_url = urlhelper('action','Folder', 'tree',"&showfile=1");
		$right_url = urlhelper('action','Media', 'list');
		$this->GetView()->Assign('left_url', $left_url);
		$this->GetView()->Assign('right_url', $right_url);
		$this->GetView()->Display('_tree_frame.htm');
		$this->GetApp()->AppExit();
	}
	
	public function selindexAction()
	{
		$this->CheckPermission(array(self::ModuleRight,PlaylistController::ModuleRightEdit));
		
		$mediatype = $this->GetRequest()->GetInput('mediatype','string');
		$left_url = urlhelper('action','Folder', 'tree', "&urlaction=sellist&mediatype=$mediatype");
		$right_url = urlhelper('action','Media', 'sellist');
		$this->GetView()->Assign('left_url', $left_url);
		$this->GetView()->Assign('right_url', $right_url);
		$this->GetView()->Display('_tree_select_frame.htm');
		$this->GetApp()->AppExit();
	}
}