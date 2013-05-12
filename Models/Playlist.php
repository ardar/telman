<?php

class Playlist extends DataObject
{	
	public static function GetTable()
	{
		return 'playlist';
	}
	
	public static function GetPK()
	{
		return 'playlistid';
	}
	
	const ObjName = '节目包';
	
	/**
	 * the layout template.
	 * @var MediaTemplate
	 */
	private $_layout = null;
	
	public function Delete()
	{
		if($this->GetRefCount()>0)
		{
			throw new PCException('节目包已排入节目时间表, 不能删除, 请先删除节目时间表中相关排程.');
		}
		if($this->GetLayout())
		{
			$this->GetLayout()->Delete();
		}
		return parent::Delete();
	}
	
	public function SaveData()
	{
		$needadd = !$this->_id;
		$result = parent::SaveData();
		if($result && $needadd)
		{
			$tpl = new MediaTemplate();
			$tpl->playlistid = $this->GetId();
			$tpl->templatename =  $this->playlistname."的布局";
			$ratio = DBHelper::GetSingleRecord(SysCode::GetTable(), 'syscodegroup', SysCode::ScreenRatio);
			$tpl->screenratio = $ratio['syscode'];
			$tpl->layoutpreview = $tpl->SaveThumbnail();
			$tpl -> SaveData();
		}
		return $result;
	}
	
	/**
	 * @return MediaTemplate
	 */
	public function GetLayout()
	{
		if ($this->_layout==null)
		{
			$rs = DBHelper::GetSingleRecord(MediaTemplate::GetTable(), 'playlistid', $this->GetId());

			if($rs)
			{
				$this->_layout = new MediaTemplate($rs['templateid']);
				$this->_layout->GetData();
			}
		}
		return $this->_layout;
	}
	
	public function ApplyLayout($template)
	{
		if($this->GetLayout())
		{
			$this->GetLayout()->CopyFrom($template);
		}
		return true;
	}
	
	public function DeleteUselessRefs()
	{
		$todaytime = strtotime(date('Y-m-d'));
		$totalcount = 0;
		$sql = "SELECT s.* 
			FROM scheduleitem s 
			WHERE s.playlistid='".$this->GetId()."' 
			and isaudited=0 and scheduledate<'$todaytime'";
		$rss = DBHelper::GetQueryRecords($sql);
		$delcount = 0;
		foreach ($rss as $rs)
		{
			$item = new ScheduleItem($rs);
			$item->Delete();
			$delcount ++;
		}
		return $delcount;
	}
	
	public function GetRefCount()
	{
		$todaytime = strtotime(date('Y-m-d'));
		$totalcount = 0;
		$sql = "select count(scheduleitemid) as recordcount from "
			.ScheduleItem::GetTable()." 
			where playlistid='".$this->GetId()."'";
			//and (isaudited=1 or (scheduledate>='$todaytime'))";
		$rs = DBHelper::GetQueryRecord($sql);
		return intval($rs['recordcount']);
	}
	
	public function GetReferences($showdate=null)
	{
		$groupby = $showdate? "scheduledate,begintime,logicalgroupid" 
			: "scheduledate,logicalgroupid";
		$showdatetime = strtotime($showdate);
		$showdatewhere = $showdate? " and scheduledate='$showdatetime'" : '';
		$todaytime = strtotime(date('Y-m-d'));
		$totalcount = 0;
		$sql = "SELECT COUNT(scheduleitemid) AS schedulecount,
			s.scheduledate,s.begintime,s.isaudited,lg.* 
			FROM scheduleitem s 
			LEFT JOIN logicalgroup lg ON s.logicalgroupid=lg.logicalgroupid
			WHERE s.playlistid='".$this->GetId()."' $showdatewhere
			GROUP BY $groupby
			ORDER BY scheduledate desc,begintime asc, itemorder asc, logicalgroupid asc";
			//and (isaudited=1 or (scheduledate>='$todaytime'))";
		$rss = DBHelper::GetQueryRecords($sql);
		return $rss;
	}
	
	public function UpdateDuration()
	{
		if ($this->GetId())
		{
			$this->ComputeDuration();
			return $this->SaveData();
		}
		return false;
	}
	
	public function ComputeDuration()
	{
		$times = array();
		$zones = $this->GetLayout()->GetZones();
		foreach ($zones as $key=>$zone)
		{
			if ($zone['type']=='text'||$zone['type']=='html')
			{
				$times[$zone['zoneid']] = $zone['looptimes'];
			}
		}
		$sql = "select m.duration,pli.zoneid from playlistitem pli
			left join media m on pli.mediaid=m.mediaid 
			where pli.playlistid='".$this->GetId()."' order by pli.zoneid,pli.playorder";
		$rss = DBHelper::GetQueryRecords($sql);
		foreach ($rss as $rs)
		{
			$times[$rs['zoneid']] += $rs['duration'];
		}
		$maxtime = 0;
		foreach ($times as $time)
		{
			if ($time>$maxtime)
			{
				$maxtime = $time;
			}
		}
		$this->duration = $maxtime;
		return $this->duration;
	}
	
	public static function ThumbnailUrl($playlistid)
	{
		return AppHost::GetAppSetting()->ThumbnailDir."playlists/".intval($playlistid).".jpg";
	}
	
	public function SaveThumbnail()
	{
		$sql = "select m.* from playlistitem i 
			left join media m on i.mediaid=m.mediaid
			where i.playlistid='".$this->GetId()."' 
			and (m.mediatype='".Media::MediaTypeVideo."' or m.mediatype='".Media::MediaTypeImage."')
			order by playorder,zoneid asc, mediatype desc limit 3";
		$rss = DBHelper::GetQueryRecords($sql);
		
		$im = @imagecreatetruecolor(170, 140);
		$background_color = imagecolorallocate($im, 250, 250, 250);
		imagefill($im, 0, 0, $background_color);
		$border_color = imagecolorallocate($im, 50, 50, 50);
		imagerectangle ($im, 0, 0, 170-1, 140-1, $border_color);
		
		if(count($rss)==0)
		{
			imagestring($im, 5, 40, 50, 'no preview', $border_color);
			
		}
		$currx = 1;
		$curry = 1;
		foreach ($rss as $rs)
		{
			$mediapreview = Media::MediaThumbnailUrl($rs);
			$mediaimg = imagecreatefromjpeg($mediapreview);
			if(!$mediaimg)
			{
				continue;
			}
			$swidth = imagesx($mediaimg);
			$sheight = imagesy($mediaimg);
			imagecopyresampled($im, $mediaimg, $currx, $curry, 0, 0, 120, 90, $swidth, $sheight);
			//imagerectangle ($im, $currx, $curry, $currx+120-1, $curry+90-1, $border_color);
			imagedestroy($mediaimg);
			$currx += 15;
			$curry += 15;
		}
		
		$currx = 3;
		$curry = 140-14;
		$zones = $this->GetLayout()->GetZones();
		foreach ($zones as $rs)
		{
			$typeimg = "images/layout/".$rs['type'].".png";
			$mediaimg = imagecreatefrompng($typeimg);
			if(!$mediaimg)
			{
				continue;
			}
			$swidth = imagesx($mediaimg);
			$sheight = imagesy($mediaimg);
			imagecopyresampled($im, $mediaimg, $currx, $curry, 0, 0, 12, 12, $swidth, $sheight);
			//imagerectangle ($im, $currx, $curry, $currx+120-1, $curry+90-1, $border_color);
			imagedestroy($mediaimg);
			$currx+=15;
		}
		
		// template preview
		$layoutpreview = $this->GetLayout()->layoutpreview;
		$layoutimg = imagecreatefrompng($layoutpreview);
		if($layoutimg)
		{
			$width = $swidth = imagesx($layoutimg);
			$height = $sheight = imagesy($layoutimg);
			if ($swidth > 70)
			{
				$height = intval($height * (80/$width));
				$width = 70;
			}
			if ($height > 70)
			{
				$width = intval($width * (80/$height));
				$height = 70;
			}
			$currx = 170-$width-1;
			$curry = 140-$height-1;
			imagecopyresampled($im, $layoutimg, $currx, $curry, 0, 0, $width, $height, $swidth, $sheight);
			$border_color = imagecolorallocate($im, 60, 60, 60);
			imagerectangle ($im, $currx, $curry, 170-1, 140-1, $border_color);
			imagerectangle ($im, $currx+1, $curry+1, 170-2, 140-2, $border_color);
			imagedestroy($layoutimg);
		}
				
		$filename = Playlist::ThumbnailUrl($this->GetId());
		imagejpeg($im, $filename);
		imagedestroy($im);
	}
}