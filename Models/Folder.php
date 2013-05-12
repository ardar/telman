<?php
class Folder extends DataObject
{
	const RootFolderId = -1;
	public static function GetTable()
	{
		return 'folder';
	}
	
	public static function GetPK()
	{
		return 'folderid';
	}
	
	public static function GetObjName()
	{
		return '文件夹';
	}
	
	public static function BuildComboList()
	{
		$rss = DBHelper::GetRecords(self::GetTable());
		$list = array();
		self::_getListByParent(0, $rss, $list, "|");
		//print_r($list);
		return $list;
	}

	private static function _getListByParent($parentid, $rss, &$list, $prefix)
	{
		foreach ($rss as $rs)
		{
			if ($rs['parentid']==$parentid)
			{
				$rs['title'] = $prefix.'－'.$rs['foldername'];
				$list[] = $rs;
				self::_getListByParent($rs['folderid'], $rss, $list, $prefix."　|");
			}
		}
	}
	
	public function Delete($recursive=false)
	{
		$childcount = DBHelper::GetRecordCount(Folder::GetTable(), "parentid='".$this->GetId()."'");
		if ($childcount>0)
		{
			if (!$recursive)
			{
				throw new PCException("[".$this->foldername."]下包含  $childcount 个子目录,无法删除");
			}
			$folders = $this->GetSubFolderList();
			foreach ($folders as $rs)
			{
				$obj = new Folder($rs);
				if(!$obj->Delete())
				{
					throw new PCException("删除[".$this->foldername."] 下的子目录[".$obj->foldername."] 失败");
				}
			}
		}
		$filecount = DBHelper::GetRecordCount(Media::GetTable(), "folderid='".$this->GetId()."'");
		if ($filecount>0)
		{
			if (!$recursive)
			{
				throw new PCException("[".$this->foldername."]下包含 $filecount 个文件,无法删除");
			}
			$files = $this->GetFileList();
			foreach ($files as $rs)
			{
				$media = new Media($rs);
				if(!$media->Delete())
				{
					throw new PCException("删除[".$this->foldername."]下的文件 [".$media->medianame."] 失败");
				}
			}
		}
		return parent::Delete();
	}
		
	public function Move($toFolderId)
	{
		if(	$this->parentid == $toFolderId)
		{
			return true;
		}
		$destFolder = new Folder($toFolderId);
		if (!$destFolder->GetData())
		{
			throw new PCException("移动文件失败, 没有找到目的文件夹 $toFolderId");
		}
		$samecount = DBHelper::GetRecordCount(self::GetTable(), 
			"parentid='".$toFolderId."' 
			and foldername='".$this->foldername."' 
			and folderid<>'".$this->GetId()."'");
		if ($samecount>0)
		{
			throw new PCException("不能移动文件夹 ".$this->foldername.", 目的文件夹下有同名文件");
		}
		$fields = array();
		$fields['parentid'] = $toFolderId;
		if(!$this->Update($fields))
		{
			throw new PCException("移动文件夹 ".$this->foldername."失败");
		}
		$this->parentid = $toFolderId;
		return true;
	}
	
	public function GetSubFolderList()
	{
		return DBHelper::GetRecords(Folder::GetTable(), 'parentid', $this->GetId(), 'foldername', 'asc');
	}
	
	public function GetFileList()
	{
		return DBHelper::GetRecords(Media::GetTable(), 'folderid', $this->GetId(), 'medianame', 'asc');
	}
}