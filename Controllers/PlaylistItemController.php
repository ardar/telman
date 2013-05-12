<?php
class PlaylistItemController extends ControllerBase
{
	const ModuleRightView='playlist_view';
	const ModuleRightEdit='playlist_edit';
	const ObjName = PlaylistItem::ObjName;
	
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		
		$this->CheckPermission(array(self::ModuleRightView,self::ModuleRightEdit));
	}
		
	public function addtozoneAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$zoneid = $this->GetRequest()->GetInput('zoneid', 'int','请选择要加入的区域');
		$mediaid = $this->GetRequest()->GetInput('mediaid', 'int','请选择要加入的媒体文件');
		$zone = new MediaZone($zoneid);
		if (!$zone->GetData()) 
		{
			$this->Message('没有找到要加入的区域'.$zoneid);
		}
		$media = new Media($mediaid);
		if (!$media->GetData()) 
		{
			if($zone->type=='text')
			{
				$mediaid = 0;
			}
			else
			{
				$this->Message('没有找到要加入的媒体'.$mediaid);
			}
		}
		else 
		{
			if($zone->type=='text')
			{
				
			}
		}
		$subquery = " where zoneid='$zoneid' order by playorder desc limit 1";
		$lastmedia = DBHelper::GetRecord(PlaylistItem::GetTable(), $subquery);
		
		$maxorder = $lastmedia ? $lastmedia['playorder']: 0;
		
		$item = new PlaylistItem();
		$item->mediaid = $mediaid;
		$item->zoneid = $zoneid;
		$item->playlistid = $zone->playlistid;
		$item->playorder = $maxorder+1;
		if(!$item->SaveData())
		{
			$this->Message('加入媒体文件失败');
		}
		else
		{
			$playlist = new Playlist($zone->playlistid);
			$playlist->UpdateDuration();
			//$url = urlhelper('action',$this->GetControllerId(),'editzone','&zoneid='.$zoneid);
			//$this->Redirect(urlencode($this->GetApp()->GetEnv('referer')));
			$this->Message(null,LOCATION_REFERER);
		}
	}

	public function editzoneAction()
	{
		$zoneid = $this->GetRequest()->GetInput('zoneid', 'int');
		$totalcount = 0;
		$join_options[] = 
			array('table'=>'media', 'left_field'=>'mediaid',
			'right_field'=>'mediaid','select'=>'*');
		$filter_options[] = array('field'=>'zoneid', 'value'=>$zoneid, 'op'=>'=');
		$list = DBHelper::GetJoinRecordSet(PlaylistItem::GetTable(), 
			$join_options, $filter_options, 
			array('playorder asc'),
			0, 9999, $totalcount);
		$zone = new MediaZone($zoneid);
		$zoners = $zone->GetData();
		$bgmedia = new Media($zone->bgmediaid);
		if ($bgmedia->GetData())
		{
			$zoners['bgmedianame'] = $bgmedia->medianame;
			$zoners['bgmediapath'] = $bgmedia->mediapath;
		}
		$zoners['totaltime'] = 0;
		$canedit = $this->CheckPermission(self::ModuleRightEdit,false);
		foreach ($list as $key => $rs)
		{
			$list[$key]['duration'] = OpenDev::GetDurationDesc($rs['duration']);
			$url = urlhelper('action','Media','edit','&mediaid='.$rs['mediaid']);
			$list[$key]['medianame'] = HtmlHelper::Link($rs['medianame'], $url,'_blank');
			
			$list[$key]['preview'] = Media::MediaThumbnailUrl($rs);
				
			$list[$key]['filesize'] = OpenDev::GetFileSizeDesc($rs['filesize']);
			if($canedit)
			{
			$list[$key]['cp'] .= HtmlHelper::Link('上移', 
				urlhelper('action',$this->GetControllerId(),'moveup','&playlistitemid='.$rs['playlistitemid']));
			$list[$key]['cp'] .= ' '.HtmlHelper::Link('下移', 
				urlhelper('action',$this->GetControllerId(),'movedown','&playlistitemid='.$rs['playlistitemid']));
			$list[$key]['cp'] .= ' '.HtmlHelper::Link('删除', 
				urlhelper('action',$this->GetControllerId(),'delete','&playlistitemid='.$rs['playlistitemid']));
			}
			$zoners['totalduration'] += $rs['duration'];
		}
		$zoners['totalduration'] = OpenDev::GetDurationDesc($zoners['totalduration']);
		$this->GetView()->Assign('canedit', $canedit);
		$this->GetView()->Assign('zone', $zoners);
		$this->GetView()->Assign('list', $list);
		$this->GetView()->Display('playlist_zone.htm');
		$this->GetApp()->AppExit();
	}
	
	public function doeditzoneAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$zoneid = $this->GetRequest()->GetInput('zoneid','int');
		if(!$zoneid)
		{
			$this->Message('没有找到要编辑的区域');
		}
		$zone = new MediaZone($zoneid);
		if (!$zone->GetData())
		{
			$this->Message("没有找到要修改的区域".$zoneid);
		}
		// Edit the zone properties
		$looptimes = $this->GetRequest()->GetInput('looptimes','int');
		if($zone->type==MediaZone::ZoneTypeVideo)
		{
			$zone->looptimes = $looptimes==0 ? -1 : $looptimes;
		}
		else
		{
			if ($looptimes<=0)
			{
				$this->Message("播放时间必须大于0秒");
			}
			$zone->looptimes = $looptimes;
		}
		$zone->bgmediaid = $this->GetRequest()->GetInput('bgmediaid','int');
		$zone->bgcolor = $this->GetRequest()->GetInput('bgcolor','string');
		$zone->template = $this->GetRequest()->GetInput('template','string');
		$zone->templateparam = $this->GetRequest()->GetInput('templateparam','string');
		$zone->fontsize = $this->GetRequest()->GetInput('fontsize','int');
		$zone->fontcolor = $this->GetRequest()->GetInput('fontcolor','string');
		if(!$zone->SaveData())
		{
			$this->Message("修改区域设置失败");
		}
		$playlist = new Playlist($zone->playlistid);
		$playlist->UpdateDuration();
		// edit the playlist items.
		$mediaids = $this->GetRequest()->GetInput('mediaid','array');
		$texts = $this->GetRequest()->GetInput('text','array');
		if($mediaids && is_array($mediaids))
		{
			foreach ($mediaids as $itemid=>$mediaid)
			{
				$obj = new PlaylistItem($itemid);
				if (!$obj->GetData())
				{
					$this->Message("没有找到要修改的".self::ObjName." ".$itemid, LOCATION_BACK);
				}
				$obj->mediaid = intval($mediaid);
				$obj->text = $texts[$itemid];
				if(!$obj -> SaveData())
				{
					$this->Message("修改 ".self::ObjName."失败", LOCATION_REFERER);
				}
			}
		}
		$this->Message("修改区域设置成功", LOCATION_REFERER);
	}
	
	public function deleteAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$id = $this->GetRequest()->GetInput('playlistitemid','int','没有指定要删除的'.self::ObjName);
		
		$obj = new PlaylistItem($id);
		if (!$obj->GetData())
		{
			$this->Message("没有找到要删除的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$playlistid = $obj->playlistid;
		$zoneid = $obj->zoneid;
		if($obj -> Delete())
		{
			$playlist = new Playlist($playlistid);
			$playlist->UpdateDuration();
			$this->Redirect(urlhelper('action',$this->GetControllerId(),'editzone','&zoneid='.$zoneid));
		}
		else
		{
			$this->Message("删除 ".self::ObjName."失败", LOCATION_BACK);
		}
	}
	
	public function moveupAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$itemid = $this->GetRequest()->GetInput('playlistitemid', 'int','请选择要移动的媒体文件');
		$item = new PlaylistItem($itemid);
		if(!$item->GetData())
		{
			$this->Message('没有找到媒体'.$item);
		}
		$zone = new MediaZone($item->zoneid);
		if (!$zone->GetData()) 
		{
			$this->Message('没有找到区域'.$item->zoneid);
		}
		$medias = $zone->GetItems();
		$last = null;
		foreach ($medias as $rs)
		{
			if($rs['playlistitemid']==$itemid)
			{
				if($last)
				{
					$thisorder = $rs['playorder'];
					$item->playorder = $last['playorder'];
					$item->SaveData();
					$lastmedia = new PlaylistItem($last['playlistitemid']);
					$lastmedia->playorder = $thisorder;
					$lastmedia->SaveData();
				}
				break;
			}
			else
			{
				$last = $rs;
			}
		}
		$this->Redirect(urlhelper('action',$this->GetControllerId(),'editzone','&zoneid='.$zone->GetId()));
	}
	
	public function movedownAction()
	{
		$this->CheckPermission(self::ModuleRightEdit);
		
		$itemid = $this->GetRequest()->GetInput('playlistitemid', 'int','请选择要移动的媒体文件');
		$item = new PlaylistItem($itemid);
		if(!$item->GetData())
		{
			$this->Message('没有找到媒体'.$item);
		}
		$zone = new MediaZone($item->zoneid);
		if (!$zone->GetData()) 
		{
			$this->Message('没有找到区域'.$item->zoneid);
		}
		$medias = $zone->GetItems();
		$last = null;
		for($i=count($medias);$i>=0;$i--)
		{
			$rs = $medias[$i];
			if($rs['playlistitemid']==$itemid)
			{
				if($last)
				{
					$thisorder = $rs['playorder'];
					$item->playorder = $last['playorder'];
					$item->SaveData();
					$lastmedia = new PlaylistItem($last['playlistitemid']);
					$lastmedia->playorder = $thisorder;
					$lastmedia->SaveData();
				}
				break;
			}
			else
			{
				$last = $rs;
			}
		}
		$this->Redirect(urlhelper('action',$this->GetControllerId(),'editzone','&zoneid='.$zone->GetId()));
	}
}