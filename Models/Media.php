<?php

class Media extends DataObject
{
	const CheckFileType = "allow";
	const CheckFileExts = "mp3|wav|wma|midi|mp4|mpeg|mpg|avi|wmv|jpg|jpeg|gif|png|bmp|txt";
	
	const MediaTypeVideo = "video";
	const MediaTypeAudio = "audio";
	const MediaTypeImage = "image";
	const MediaTypeText = "text";
	const MediaTypeHtml = "html";
	const MediaTypeOther = "";
	
	const ThumbnailMaxWidth = 300;
	const ThumbnailMaxHeight = 200;
	
	
	public static function GetTable()
	{
		return 'media';
	}
	
	public static function GetPK()
	{
		return 'mediaid';
	}
	
	public static function GetObjName()
	{
		return '媒体';
	}
	
	public static function GetMediaTypeByExt($fileextname)
	{
		$codelist = SysCode::GetCodeList(SysCode::MediaType);
		foreach ($codelist as $code)
		{
			$codeexts = explode('|',$code['syscodeparam']);
			if (in_array($fileextname, $codeexts))
			{
				return $code['syscode'];
			}
		}
		return null;
	}
	
	public static function GetMediaTypeExts($mediatypes)
	{
		$exts = array();
		$arr = explode('|', $mediatypes);
		//print_r($arr);
		if (count($arr)==0)
		{
			return $exts;
		}
		$codelist = SysCode::GetCodeList(SysCode::MediaType);
		foreach ($arr as $type)
		{
			$codeexts = explode('|',$codelist[$type]['syscodeparam']);
			$exts = array_merge($exts, $codeexts);
		}
		return $exts;
	}
	
	public function GetData($refresh=false)
	{
		if (!$refresh && $this->_data)
		{
			return $this->_data;
		}
		
		//$join_options[] = array('table'=>Folder::GetTable(), 'on_field'=>'folderid','select'=>'foldername');
		$join_options[] = array('table'=>Account::GetTable(), 'left_field'=>'uploaderid', 
			'right_field'=>'userid','select'=>'username as uploadername, realname as uploaderrealname');
		$filter_options[] = array('table'=>null, 'field'=>'mediaid', 'value'=>$this->GetId(), 'op'=>'=');
		
		return $this->_data = DBHelper::GetJoinRecord($this->GetTable(), 
			$join_options, $filter_options, null);
	}
	
	public function GetRefCount()
	{
		$totalcount = 0;
		$totalcount += DBHelper::GetRecordCount(
			PlaylistItem::GetTable(), "mediaid='".$this->GetId()."'");
		$totalcount += DBHelper::GetRecordCount(
			MediaTemplate::GetTable(), "bgmediaid='".$this->GetId()."'");
		$totalcount += DBHelper::GetRecordCount(
			MediaZone::GetTable(), "bgmediaid='".$this->GetId()."'");
		return $totalcount;
	}
	
	public function GetReferences()
	{
		$ret_array = array();
		$sql = "select * from (
			template tp
			left join playlist pl on tp.playlistid=pl.playlistid)
			where tp.bgmediaid='".$this->GetId()."'
			order by tp.templateid";
		$temp_tp = DBHelper::GetQueryRecords($sql);
		$sql = "select z.*,tp.templatename,pl.playlistname,tp.playlistid
			 from ((mediazone z 
			left join template tp on z.templateid=tp.templateid)
			left join playlist pl on tp.playlistid=pl.playlistid)
			where z.bgmediaid='".$this->GetId()."'
			order by z.templateid, z.zindex";
		$temp_zone = DBHelper::GetQueryRecords($sql);
		//print_r($temp_zone);
		
		$sql = "select distinct(i.zoneid),i.*,z.zonename,pl.playlistname from ((playlistitem i 
			left join mediazone z on i.zoneid=z.zoneid)
			left join playlist pl on pl.playlistid=z.playlistid)
			where i.mediaid='".$this->GetId()."' 
			order by z.playlistid, playlistitemid";
		$temp_plitem = DBHelper::GetQueryRecords($sql);
		
		foreach ($temp_tp as $key=>$pl)
		{
			if ($pl['playlistid']<=0) continue;
			$pl['type'] = '节目包背景';
			$ret_array['playlist'][] = $pl;
			foreach ($temp_zone as $key2=>$zone)
			{
				if($zone['templateid']==$pl['templateid'])
				{
					$zone['type'] = '区域背景';
					$ret_array['playlist'][] = $zone;
					unset($temp_zone[$key2]);
				}
			}
			unset($temp_tp[$key]);
		}
		foreach ($temp_tp as $key=>$tp)
		{
			if ($pl['playlistid']>0) continue;
			$tp['type'] = '模板背景';
			$ret_array['template'][] = $tp;
			foreach ($temp_zone as $key2=>$zone)
			{
				if($zone['templateid']==$tp['templateid'])
				{
					$zone['type'] = '区域背景';
					$zone['name'] = $zone['zonename'];
					$ret_array['template'][] = $zone;
					unset($temp_zone[$key2]);
				}
			}
		}
		foreach ($temp_zone as $zone)
		{
			$zone['type'] = '区域背景';
			$zone['name'] = $zone['zonename'];
			if ($zone['playlistid']>0)
			{
				$ret_array['playlist'][] = $zone;
			}
			else 
			{
				$ret_array['template'][] = $zone;
			}	
		}
		foreach ($temp_plitem as $item)
		{
			$item['type'] = '播放列表';
			$item['name'] = $item['zonename'];
			$ret_array['playlist'][] = $item;
		}
		return $ret_array;
	}
		
	public function Delete()
	{
		if($this->GetId()>0)
		{
			if(!$this->GetData())
			{
				throw new PCException('媒体文件不存在'.$this->GetId());
			}
			$refcount = $this->GetRefCount();
			if ($refcount>0)
			{
				throw new PCException("媒体文件Id:".$this->GetId()." 被引用  ".$refcount." 次, 不能被删除");
			}
			if ($this->savefilepath)
			{
				OpenDev::deletefile($this->savefilepath);
			}
			$result = parent::Delete();
			return $result;
		}
	}
		
	public function Audit(Account &$auditer, $isAudit=true)
	{
		if ($this->isaudited==$isAudit)
		{
			return true;
		}
		$fields = array();
		if ($isAudit)
		{
			$fields['isaudited'] = 1;
			$fields['audittime'] = time();
			$fields['auditerid'] = $auditer->GetId();
		}
		else
		{
			$fields['isaudited'] = 0;
			$fields['audittime'] = 0;
			$fields['auditerid'] = $auditer->GetId();
		}
		$result = parent::Update($fields);
		return $result;
	}
		
	public function Move($toFolderId, $isoverwrite=false)
	{
		if(	$this->folderid == $toFolderId)
		{
			return true;
		}
		$destFolder = new Folder($toFolderId);
		if (!$destFolder->GetData())
		{
			throw new PCException("移动文件失败, 没有找到目的文件夹 $toFolderId");
		}
		if (!$isoverwrite)
		{
			$samecount = DBHelper::GetRecordCount(self::GetTable(), 
				"folderid='".$toFolderId."' 
				and medianame='".$this->medianame."' 
				and mediaid<>'".$this->GetId()."'");
			if ($samecount>0)
			{
				throw new PCException("不能移动文件".$this->medianame.", 目的文件夹下有同名文件");
			}
		}
		else 
		{
			$filter_options[] = array('field'=>'folderid', 'value'=>$toFolderId, 'op'=>'=');
			$filter_options[] = array('field'=>'medianame', 'value'=>$this->medianame, 'op'=>'=');
			$totalcount = 0;
			$filelist = DBHelper::GetJoinRecordSet(self::GetTable(), 
				null, $filter_options, null,
				0, 10000, $totalcount);
			foreach ($filelist as $rs)
			{
				if ($rs['medianame']==$this->medianame)
				{
					$todelfile = new Media($rs);
					$todelfile->Delete();
				}
			}
		}
		$fields = array();
		$fields['folderid'] = $toFolderId;
		if(!$this->Update($fields))
		{
			throw new PCException("移动文件".$this->medianame."失败");
		}
		$this->folderid = $toFolderId;
		return true;
	}
	
	public function GetThumbnailUrl()
	{
		return Media::MediaThumbnailUrl($this->GetData());
	}
	
	public static function MediaThumbnailImage($mediars, $link=null, $maxwidth=100, $maxheight=100,$imgid='')
	{
		$imgurl = self::MediaThumbnailUrl($mediars);
		if ($imgurl)
		{
			return HtmlHelper::Image($imgurl, $link, $maxwidth, $maxheight, $imgid);
		}
		return '';
	}
	
	public static function MediaThumbnailUrl($mediars)
	{
		if ($mediars['mediatype'] == Media::MediaTypeImage 
			|| $mediars['mediatype']==Media::MediaTypeVideo)
		{
			$rangemin = intval($mediars['mediaid']/1000)*1000;
			$rangemax = (intval($mediars['mediaid']/1000)+1)*1000;
			$subdir = $rangemin.'-'.$rangemax;
			$dir = AppHost::GetAppSetting()->ThumbnailDir."medias/$subdir/";
			if (!is_dir(PUBLIC_DIR.$dir))
			{
				mkdir(PUBLIC_DIR.$dir);
			}
			return $dir.$mediars['mediaid'].".jpg";
		}
		else
		{
			return null;
		}
	}
	
	public function SaveThumbnail()
	{
		$url = self::MediaThumbnailUrl($this->GetData());
		$width = $this->width;
		$height = $this->height;
		if ($width > self::ThumbnailMaxWidth)
		{
			$height = intval($height * (self::ThumbnailMaxWidth/$width));
			$width = self::ThumbnailMaxWidth;
		}
		if ($height > self::ThumbnailMaxHeight)
		{
			$width = intval($width * (self::ThumbnailMaxHeight/$height));
			$height = self::ThumbnailMaxHeight;
		}
		if ($this->mediatype==self::MediaTypeVideo)
		{
			$ffmpeg = new Ffmpeg($this->savefilepath);
			$from_second = $this->duration>=1 ? 0.5 : 0.1;
			$ffmpeg->SaveThumbnail($url,$from_second, $width, $height);
		}
		elseif ($this->mediatype==self::MediaTypeImage)
		{
			$imgbuf = OpenDev::readfromfile($this->savefilepath);
			OpenDev::create_thumbnail($imgbuf, $url, $width, $height);
		}
		else
		{
			//echo "no thumbnail:".$this->mediatype."\n";
		}
	}
	
	public static function UpdateAllThumbnails()
	{
		$rss = DBHelper::GetRecords(self::GetTable(),'','','mediaid');
		foreach ($rss as $rs)
		{
			$media = new Media($rs);
			$media->SaveThumbnail();
		}
	}
}