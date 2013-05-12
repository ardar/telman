<?php
class TFFileController extends ControllerBase
{
	const ModuleRight='tf_file';
	const ObjName = "附件";
	
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		
	}
	
	public function fcklistAction()
	{
		$this->CheckPermission(self::ModuleRight);
		$totalcount = 0;
		$join_options[] = array(
			'table'=>Account::GetTable(), 'alia'=>'u', 
			'left_field'=>'uploaderid','right_field'=>'userid','select'=>'username as uploadername');
		$filter_fields = array(
			'filename' => '%like%', 
			'uploadername' => '=',
			);
		$table_options = array('table'=>TFFile::GetTable(), 'select'=>TFFile::SimpleSelectList);
		$list = DBHelper::GetJoinRecordSet(TFFile::GetTable(), 
			$join_options, $this->GetFilterOption($filter_fields), 
			$this->GetSortOption('fileid','desc'),
			$this->_offset, $this->_perpage, $totalcount);
		foreach ($list as $key => $rs)
		{
			$url = urlhelper('action','TFFile','getfile',"&fileid=".$rs['fileid']);
			$thumnail = urlhelper('action','TFFile','thumbnail',"&fileid=".$rs['fileid']);
			$script= "javascript:window.returnValue='$url';window.close();";
			$list[$key]['fileimg'] = HtmlHelper::Image($thumnail,$script,'',50,50);
			$list[$key]['filename'] = "<a style='display:block' href=\"$script\">".$rs['filename']."</a>";
			$list[$key]['filesize'] = OpenDev::GetFileSizeDesc($rs['filesize']);
		}
		
		$table = new HtmlTable(self::ObjName."列表",'border');
		$table -> BindField('fileid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('fileimg', "预览", '', '');
		$table -> BindField('filename', self::ObjName."名称", '', '', 'asc');
		$table -> BindField('mimetype', "MIME类型", '', '', 'asc');
		$table -> BindField('filesize', "大小", '', '', 'asc');
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
				
		$table -> BindHeadbar(" 文件名".HtmlHelper::Input('filename','','text'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
				
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
		$this->GetApp()->AppExit();
	}
	
	public function listAction()
	{
		$this->CheckPermission(self::ModuleRight);
		$totalcount = 0;
		$join_options[] = array(
			'table'=>Account::GetTable(), 'alia'=>'u', 
			'left_field'=>'uploaderid','right_field'=>'userid','select'=>'username as uploadername');
		$filter_fields = array(
			'filename' => '%like%', 
			'uploadername' => '=',
			);
		$table_options = array('table'=>TFFile::GetTable(), 'select'=>TFFile::SimpleSelectList);
		$list = DBHelper::GetJoinRecordSet(TFFile::GetTable(), 
			$join_options, $this->GetFilterOption($filter_fields), 
			$this->GetSortOption('fileid','desc'),
			$this->_offset, $this->_perpage, $totalcount);
		foreach ($list as $key => $rs)
		{
			$url = urlhelper('action','TFFile','getfile',"&fileid=".$rs['fileid']);
			$thumnail = TFFile::AdminThumbnailUrl($rs['fileid']);
			$list[$key]['fileurl'] = $thumnail;
			$list[$key]['filesize'] = OpenDev::GetFileSizeDesc($rs['filesize']);
		}
		
		$table = new HtmlTable(self::ObjName."列表",'border');
		$table -> BindField('fileid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('fileurl', "预览", 'image', '', 'asc', array('width'=>50,'height'=>50));
		$table -> BindField('filename', self::ObjName."名称", '', '', 'asc');
		$table -> BindField('mimetype', "MIME类型", '', '', 'asc');
		$table -> BindField('filesize', "大小", '', '', 'asc');
		$table -> BindField('uploadername', "上传", '', '', 'asc');
		$table -> BindField('cp', "操作", 'cp', '','', 
			array(
			'查看'=>array('','getfile','fileid','_blank'),
			'修改'=>array('','edit','fileid'),
			'删除'=>array('','delete','fileid','',"onclick=\"if(!confirm('是否确定删除该文件?'))return false;\"")));
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
				
		$table -> BindHeadbar(" ".HtmlHelper::Button('上传'.self::ObjName,'link',
			urlhelper('action',$this->GetControllerId(),'add')));
		$table -> BindHeadbar(" | 文件名".HtmlHelper::Input('filename','','text'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
				
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function deleteAction()
	{
		$this->CheckPermission(self::ModuleRight);
		$id = $this->GetRequest()->GetInput('fileid','int','没有指定要删除的'.self::ObjName);
		
		$obj = new TFFile($id);
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
	
	public function getfileAction()
	{
		$id = $this->GetRequest()->GetInput('fileid', 'int');
		if ($id>0)
		{
			$obj = new TFFile($id);
			if ($obj->GetData())
			{
				ob_clean();
				//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				//header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
				//header("Cache-Control: no-cache, must-revalidate");
				//header("Pragma: no-cache");
				header("Content-type: ".$obj->mimetype);
				header("Content-Length: ".$obj->filesize);
				header('Content-Disposition: filename="'.$obj->filename.'"');
				echo OpenDev::dstripslashes($obj->filebuf);
				unset($obj);
				$this->GetApp()->SysExit();
			}
		}
	}
	
	public function thumbnailAction()
	{
		$id = $this->GetRequest()->GetInput('fileid', 'int');
		if ($id>0)
		{
			$obj = new TFFile($id);
			if($obj->GetData())
			{
				$obj -> OutputThumbnail(TFFile::ThumbnailMaxSize, TFFile::ThumbnailMaxSize);
			}
			$this->GetApp()->SysExit();
		}
	}
	
	public function adminthumbnailAction()
	{
		$id = $this->GetRequest()->GetInput('fileid', 'int');
		if ($id>0)
		{
			$obj = new TFFile($id);
			$url = $obj -> GetAdminThumbnailUrl();
			if (!file_exists($url))
			{
				if($obj->GetData())
				{
					$obj -> SaveThumbnail(TFFile::ThumbnailMaxSize, TFFile::ThumbnailMaxSize);
				}
			}
			header("Location: ".$url);
			$this->GetApp()->SysExit();
		}
	}
		
	private function _bindFormFields(HtmlForm $form, &$rs)
	{
		$form -> BindField('upload', '上传新文件', '','file');
		$form -> BindField('fileid','',$rs['fileid'],'hidden');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
	}
	
	public function editAction()
	{
		$this->CheckPermission(self::ModuleRight);
		$id = $this->GetRequest()->GetInput('fileid', 'int', '没有指定要修改的'.self::ObjName);
		$obj = new TFFile($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		
		$form = new HtmlForm('修改'.self::ObjName);
		$form -> BindField('fileid', 'ID', $rs['fileid'],'');
		$form -> BindField('filename', '当前'.self::ObjName.'', $rs['filename'],'');
		$thumbnail_url = $obj->GetAdminThumbnailUrl();
		$url = $obj -> GetFileUrl();
		$form -> BindField('thumbnail', '预览', $thumbnail_url, 'image', array('link'=>$url));
		
		$this->_bindFormFields($form, $rs);		
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function addAction()
	{
		$this->CheckPermission(self::ModuleRight);
		$rs = array();
				
		$form = new HtmlForm('新建'.self::ObjName);
		
		$this->_bindFormFields($form, $rs);
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	private function _parseFormInput()
	{
		$input = array();
		
		return $input;
	}
	
	public function dofckaddAction()
	{
		$this->CheckPermission(self::ModuleRight);
		$uploadfile = new UploadFile('upload');
		$uploadfile->HasValue('没有上传有效的文件');
		$uploadfile->Validate(UploadFile::CheckAllow, "jpeg|jpg|gif|png|bpm", 0, 10240);
		$object = new TFFile();
		$object->GetUpload($uploadfile);
		$object->uploaderid = $this->GetAccount()->GetId();
		$object->uploadtime = $this->GetApp()->GetEnv('timestamp');
		
		if($object->SaveData())
		{
			$backurl = urlhelper('action',$this->GetControllerId(),'list');
			$fileurl = urlhelper('action','TFFile','getfile',"&fileid=".$object->GetId());
			echo "<script type='text/javascript'>alert('上传文件成功');window.parent.OnUploadFinished('$fileurl');alert('end');</script>";
			$this->GetApp()->AppExit();
		}
		else
		{
			$this->Message("新建".self::ObjName."失败", LOCATION_BACK);
		}
	}
	
	public function doaddAction()
	{
		$this->CheckPermission(self::ModuleRight);
		$uploadfile = new UploadFile('upload');
		$uploadfile->HasValue('没有上传有效的文件');
		$uploadfile->Validate(UploadFile::CheckAllow, "jpeg|jpg|gif|png|bmp", 0, 10240);
		$object = new TFFile();
		$object->GetUpload($uploadfile);
		$object->uploaderid = $this->GetAccount()->GetId();
		$object->uploadtime = $this->GetApp()->GetEnv('timestamp');
		
		if($object->SaveData())
		{
			$object -> SaveThumbnail(TFFile::ThumbnailMaxSize, TFFile::ThumbnailMaxSize);
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
		$this->CheckPermission(self::ModuleRight);
		$id = $this->GetRequest()->GetInput('fileid', 'int', '没有指定要修改的'.self::ObjName);
		
		$uploadfile = new UploadFile('upload');
		$uploadfile->HasValue('没有上传有效的文件');
		$uploadfile->Validate(UploadFile::CheckDeny, "php|php3|asp|exe|aspx", 0, 10240);
		
		$object = new TFFile($id);
		if (!$object->GetData())
		{
			$this->Message("没有找到要修改的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$object->GetUpload($uploadfile);
		$object->uploaderid = $this->GetAccount()->GetId();
		$object->uploadtime = $this->GetApp()->GetEnv('timestamp');
		
		$referer = $this->GetRequest()->GetInput('referer');
		if($object->SaveData())
		{
			$object -> SaveThumbnail(TFFile::ThumbnailMaxSize, TFFile::ThumbnailMaxSize);
			$backurl = urlhelper('action',$this->GetControllerId(),'list');
			$this->Message("修改".self::ObjName."成功", $backurl);
		}
		else
		{
			$this->Message("修改".self::ObjName."失败", LOCATION_BACK);
		}
	}
}