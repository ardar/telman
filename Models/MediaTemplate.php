<?php

class MediaTemplate extends DataObject
{	
	public static function GetTable()
	{
		return 'template';
	}
	
	public static function GetPK()
	{
		return 'templateid';
	}
	
	const ObjName = '节目模板';
	
	private $_zones = null;
	
	public function __construct($id_or_array=null)
	{
		parent::__construct($id_or_array);
	}
	
	public function GetData($refresh=false)
	{
		if ($refresh || !$this->_data)
		{
			$join_options[] = array('table'=>Media::GetTable(), 
				'left_field'=>'bgmediaid','right_field'=>'mediaid',
				'select'=>'medianame as bgmedianame,savefilepath as bgmediapath');
			$filter_options[] = array('field'=>self::GetPK(), 'value'=>$this->GetId(), 'op'=>'=');
			$this->_data = DBHelper::GetJoinRecord(self::GetTable(), $join_options,$filter_options,null);
		}
		return $this->_data;
	}
	
	public function GetZones($refresh=false)
	{
		if($refresh || !$this->_zones)
		{
			$join_options[] = array('table'=>Media::GetTable(), 
				'left_field'=>'bgmediaid','right_field'=>'mediaid',
				'select'=>'medianame as bgmedianame,savefilepath as bgmediapath');
			$filter_options[] = array('field'=>'templateid', 'value'=>$this->GetId(), 'op'=>'=');
			$sort_options[] = "zindex asc";
			$icount = 0;
			$this->_zones = DBHelper::GetJoinRecordSet(MediaZone::GetTable(), 
				$join_options, $filter_options, $sort_options, 0, 1000, $icount);
		}
		return $this->_zones;
	}

	public static function GetZoneStr($zones)
	{
		$str = '';
		if($zones)
		{
			foreach ($zones as $rs)
			{
				$id = 'zone_'.$rs['zoneid'];
				$name = $rs['zonename'];
				$left = $rs['left'];
				$top = $rs['top'];
				$width = $rs['width'];
				$height = $rs['height'];
				$zindex = $rs['zindex'];
				$type = $rs['type'];
				$bgcolor = $rs['bgcolor'];
				$bgmediaid = $rs['bgmediaid'];
				$bgmedia = $rs['bgmedianame'];
				$bgmediapath = $rs['bgmediapath'];
				$line = "$id,$name,$left,$top,$width,$height,$zindex,$type,"
					."$bgcolor,$bgmediaid,$bgmedia,$bgmediapath";
				$str .= $str ? ('*'.$line) : $line;
			}
		}
		return $str;
	}
	
	public function SaveZonesInput($zone_str)
	{
		$zones = array();
		$lines = explode('*', trim($zone_str));
		$zoneids = array();
		foreach($lines as $line)
		{
			//echo "zoneline:$line \n<BR>---------------<BR>\n";
			$arr = explode(',', trim($line));
			if(trim($line)=='' || count($arr)!=12)
			{
				continue;
			}
			//print_r($arr);
			$id = $arr[0];
			$name = $arr[1];
			$left = intval($arr[2]);
			$top = intval($arr[3]);
			$width = intval($arr[4]);
			$height = intval($arr[5]);
			$zindex = intval($arr[6]);
			$type = $arr[7];
			$bgcolor = $arr[8];
			$bgmediaid = intval($arr[9]);
			$bgmedia = $arr[10];
			$bgmediapath = $arr[11];
			
			if (strpos($id, 'newzone_')!==false)
			{
				$id = 0;
			}
			elseif(strpos($id, 'zone_')==0)
			{
				$id = str_replace('zone_', '', $id);
			}
			else
			{
				echo "数据不合法:$line <br>\n";
				continue;
			}
			$zone = new MediaZone($id);
			if ($name=='/deleted/')
			{
				//echo "delete $id<BR>\n";
				$zone->Delete();
			}
			else 
			{
				if($id==0)
				{
					if($type=='video')
					{
						$zone->looptimes = -1;
						$zone->speed=0;
					}
					else
					{
						$zone->looptimes = 30;
						$zone->speed = 'slow';
					}
				}
				//echo "save $id $name<BR>\n";
				$needClearList = false;
				if ($id && $zone->GetData() && $zone->type!=$type)
				{
					$zone->DeleteItems();
				}
				$zone->zonename = $name;
				$zone->type = $type;
				$zone->left = intval($left);
				$zone->top = intval($top);
				$zone->width = intval($width);
				$zone->height = intval($height);
				$zone->zindex = intval($zindex);
				$zone->bgcolor = $bgcolor;
				$zone->bgmediaid = intval($bgmediaid);
				$zone->templateid = $this->GetId();
				$zone->playlistid = $this->playlistid;
				$zone->SaveData();
				$zones[] = $zone;
				$zoneids[] = $zone->GetId();
			}
		}		
		return $zones;
	}
	
	public function SaveThumbnail()
	{
		$arr = explode(':', $this->screenratio);
		$widthRatio = $arr[0]>0 ? $arr[0] : 1;
		$heightRatio = $arr[1]>0 ? $arr[1] : 1;
		$width = 80;
		if(intval($width)<$width) $width = intval($width)+1;
		$height = 80*$heightRatio/$widthRatio;
		$im = @imagecreatetruecolor($width, $height)
		    or die("Cannot Initialize new image stream");
		$border_color = imagecolorallocate($im, 0, 0, 0);
		$bgcolor = $this->bgcolor ? $this->bgcolor : '#042A63';
		$bgr = hexdec(substr($bgcolor,1,2));
		$bgg = hexdec(substr($bgcolor,3,2));
		$bgb = hexdec(substr($bgcolor,5,2));
		$background_color = imagecolorallocate($im, $bgr, $bgg, $bgb);
		imagerectangle ($im, 0, 0, $width-1,$height-1, $border_color);
		imagefill($im, 1, 1, $background_color);
		//$text_color = imagecolorallocate($im, 255-$bgr, 255-$bgg, 255-$bgb);
		//imagestring($im, 5, 5, 5,  $string, $text_color);
		$zones = $this->GetZones();
		foreach ($zones as $zone)
		{
			$bgcolor = $zone['bgcolor']?$zone['bgcolor']: '#cccccc';//"#6699FF";
			
			$bgr = hexdec(substr($bgcolor,1,2));
			$bgg = hexdec(substr($bgcolor,3,2));
			$bgb = hexdec(substr($bgcolor,5,2));
			$background_color = imagecolorallocate($im, $bgr, $bgg, $bgb);
			$zleft = intval($zone['left']*$width/100);
			$ztop = intval($zone['top']*$height/100);
			$zwidth = intval(($zone['width']+$zone['left'])*$width/100)-1;
			$zheight = intval(($zone['top']+$zone['height'])*$height/100)-1;
			imagerectangle ($im, $zleft, $ztop,$zwidth,$zheight,$border_color);
			imagefill($im, $zleft+1, $ztop+1, $background_color);
		}
		$file_name = AppHost::GetAppSetting()->ThumbnailDir."templates/";
		$file_name .= 'tpl_'.$this->GetId().'.png';
		imagepng($im, $file_name);
		imagedestroy($im);
		return $file_name;
	}
	
	public function Delete()
	{
		$this->ClearZones();
		return parent::Delete();
	}
	
	public function ClearZones()
	{
		$zones = $this->GetZones();
		foreach ($zones as $zone)
		{
			$zone = new MediaZone($zone['zoneid']);
			$zone->Delete();
		}
	}
	
	public function CopyFrom($template)
	{
		$this->ClearZones();
		$template->GetData();
		$this->screenratio = $template->screenratio;
		$this->bgmediaid = $template->bgmediaid;
		$this->bgcolor = $template->bgcolor;
		$this->layoutxml = $template->layoutxml;
		$zonelist = $template->GetZones();
		foreach ($zonelist as $rs)
		{
			$zone = new MediaZone();
			$zone->zonename = $rs['zonename'];
			$zone->left = intval($rs['left']);
			$zone->top = intval($rs['top']);
			$zone->width = intval($rs['width']);
			$zone->height = intval($rs['height']);
			$zone->zindex = intval($rs['zindex']);
			$zone->type = $rs['type'];
			$zone->bgcolor = $rs['bgcolor'];
			$zone->bgmediaid = intval($rs['bgmediaid']);
			$zone->looptimes = intval($rs['looptimes']);
			$zone->template = $rs['template'];
			$zone->templateparam = $rs['templateparam'];
			$zone->speed = $rs['speed'];
			$zone->fontsize = intval($rs['fontsize']);
			$zone->fontcolor = $rs['fontcolor'];
			$zone->templateid = $this->GetId();
			$zone->playlistid = intval($this->playlistid);
			$zone->SaveData();
		}
		
		$this ->layoutpreview = $this -> SaveThumbnail();
		$this->SaveData();
		return $this;
	}
}