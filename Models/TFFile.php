<?php
class TFFile extends DataObject
{			
	const SimpleSelectList = "fileid,filename,filesize,minetype,filehash,uploaderid,uploadtime";
	const ThumbnailMaxSize = 200;
	
	private $_bufHasSlashes;
	
	public static function GetTable()
	{
		return 'tf_file';
	}
	
	public static function GetPK()
	{
		return 'fileid';
	}
	
	public static function GetObjName()
	{
		return "上传文件";
	}
	
	public static function FileUrl($tffileid)
	{
		return urlhelper('action','TFFile','getfile','&fileid='.intval($tffileid));
	}
	
	public static function ThumbnailUrl($tffileid)
	{
		return urlhelper('action','TFFile','thumbnail','&fileid='.intval($tffileid));
	}
	
	public static function AdminThumbnailUrl($tffileid)
	{
		return AppHost::GetAppSetting()->ThumbnailDir."tffile/".intval($tffileid).".jpg";
	}
	
	public function GetFileUrl()
	{
		return self::FileUrl($this->GetId());
	}
	
	public function GetThumbnailUrl()
	{
		return self::ThumbnailUrl($this->GetId());
	}
	
	public function GetAdminThumbnailUrl()
	{
		return self::AdminThumbnailUrl($this->GetId());
	}
	
	/**
	 * Save the upload file to this object.
	 * @param UploadFile $uploadfile
	 */
	public function GetUpload(UploadFile $uploadfile)
	{
		$this->filename = $uploadfile->filename;
		$this->mimetype = $uploadfile->mimetype;
		$this->filesize = $uploadfile->filesize;
		$this->filehash = $uploadfile->GetHash();
		$this->filebuf = $uploadfile->GetBuffer();
	}
	
	public function GetBuffer()
	{
		return OpenDev::dstripslashes($this->filebuf);
	}
	
	public function SaveData()
	{
		if (!$this->_bufHasSlashes)
		{
			$this->filebuf = OpenDev::daddslashes($this->filebuf);
			$this->_bufHasSlashes = true;
		}
		return parent::SaveData();
	}
	
	public function OutputThumbnail($maxwidth,$maxheight=0)
	{
		if (!$this->filehash)
		{
			return false;
		}
		return OpenDev::output_thumbnail($this->GetBuffer(), $maxwidth, $maxheight);
	}
	
	public function SaveThumbnail($maxwidth,$maxheight=0)
	{
		if (!$this->filehash)
		{
			return false;
		}
		$ext = end(explode('.',$this->filename));
		$savefilename = AppHost::GetAppSetting()->ThumbnailDir."tffile/".$this->GetId().".jpg";
		return OpenDev::create_thumbnail($this->GetBuffer(), $savefilename, $maxwidth, $maxheight);
	}
	
	public function Delete()
	{
		$thumbnail_filename = AppHost::GetAppSetting()->ThumbnailDir."tffile/".$this->GetId().".jpg";
		if(file_exists($thumbnail_filename))
		{
			OpenDev::deletefile($thumbnail_filename);
		}
		return parent::Delete();
	}
}