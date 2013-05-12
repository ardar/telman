<?php
class WebSiteController extends ControllerBase
{
	const ModuleRight='system';
	
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		$this->CheckPermission(self::ModuleRight);
	}
	
	public function listAction()
	{
		$totalcount = 0;
		$list = DBHelper::GetJoinRecordSet(WebSite::GetTable(), 
			null, null, null,
			$this->_offset, $this->_perpage, $totalcount);
		foreach ($list as $key=>$rs)
		{
			$list[$key]['cp'] = HtmlHelper::Button('修改站点设置','link',
				urlhelper('action',$this->GetControllerId(),'edit','&websiteid=1'));
			$list[$key]['cp'] .= " ".HtmlHelper::Button('修改全局播放设置','link',
				urlhelper('action',$this->GetControllerId(),'playconfig'));
		}
		
		$table = new HtmlTable("系统设置",'border');
		$table -> BindField('websitename', "站点标题", '');
		$table -> BindField('websiteisactive', "是否启用",'bool');
		$table -> BindField('gzipenabled', "开启GZip",'bool');
		$table -> BindField('lang', "语言",'');
		$table -> BindField('cp',"管理任务",'','','','width=400');
		$table -> BindData($list);
		//$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
//		$table -> BindHeadbar(HtmlHelper::Button('修改站点设置','link',
//			urlhelper('action',$this->GetControllerId(),'edit','&websiteid=1')));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function editAction()
	{
		$id = 1;
			
		$website = new WebSite($id);
		$rs = $website->GetData();
		
		$form = new HtmlForm('修改站点设置');
		
		$form -> BindField('websitename', '站点名称', $rs['websitename'],'text');
		$form -> BindField('websiteisactive', '是否启用', $rs['websitename'],'check');
		$form -> BindField('gzipenabled', '开启GZip', $rs['gzipenabled'],'check');
		$form -> BindField('debugenabled', '开启调试信息', $rs['debugenabled'],'check');
		//$form -> BindField('datacacheon', '开启数据缓存 ', $rs['datacacheon'],'check');
		//$form -> BindField('datacachetime', '数据缓存时间(秒)', $rs['datacachetime'],'text');
		//$form -> BindField('htmlcacheon', '开启HTML缓存 ', $rs['htmlcacheon'],'check');
		//$form -> BindField('htmlcachetime', 'HTML缓存时间(秒)', $rs['htmlcachetime'],'text');
		$form -> BindField('dateformat', '时间显示格式', $rs['dateformat'],'text');
		$form -> BindField('timeformat', '日期显示格式', $rs['timeformat'],'text');
		$form -> BindField('lang', '语言', $rs['lang'],'text');
		$form -> BindField('reservename', '保留用户名(使用|分隔 )', $rs['reservename'],'text');
		$form -> BindField('masteremail', '管理员Email', $rs['masteremail'],'text');
		$form -> BindField('metakeywords', '站点关键字', $rs['metakeywords'],'text');
		$form -> BindField('metadescription', '站点Description', $rs['metadescription'],'textarea');
		$form -> BindField('weatherad', '触摸屏天气广告', $rs['weatherad'],'text');
		$form -> BindField('weathercityid', '触摸屏天气城市ID', $rs['weathercityid'],'text');
		
		$form -> BindField('websiteid','',$website->GetId(),'hidden');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	private function _parseFormInput()
	{
		$input = array();
		$input['websitename'] = $this->GetRequest()->GetInput('websitename','string','站点名必须填写');
		$input['websiteisactive'] = $this->GetRequest()->GetInput('websiteisactive','int');
		$input['gzipenabled'] = $this->GetRequest()->GetInput('gzipenabled','int');
		$input['debugenabled'] = $this->GetRequest()->GetInput('debugenabled','int');
		$input['datacacheon'] = $this->GetRequest()->GetInput('datacacheon','int');
		$input['datacachetime'] = $this->GetRequest()->GetInput('datacachetime','int');
		$input['htmlcacheon'] = $this->GetRequest()->GetInput('htmlcacheon','int');
		$input['htmlcachetime'] = $this->GetRequest()->GetInput('htmlcachetime','int');
		$input['dateformat'] = $this->GetRequest()->GetInput('dateformat','string');
		$input['timeformat'] = $this->GetRequest()->GetInput('timeformat','string');
		$input['lang'] = $this->GetRequest()->GetInput('lang','string');
		$input['reservename'] = $this->GetRequest()->GetInput('reservename','string');
		$input['masteremail'] = $this->GetRequest()->GetInput('masteremail','string');
		$input['metakeywords'] = $this->GetRequest()->GetInput('metakeywords','string');
		$input['metadescription'] = $this->GetRequest()->GetInput('metadescription','string');
		$input['weatherad'] = $this->GetRequest()->GetInput('weatherad','string');
		$input['weathercityid'] = $this->GetRequest()->GetInput('weathercityid','string');
	
		return $input;
	}
	
	
	public function doeditAction()
	{
		$id = 1;//$this->GetRequest()->GetInput('websiteid', 'int', '没有指定要修改的站点');
		
		$referer = $this->GetRequest()->GetInput('referer');
		$referer = $referer?$referer:$this->GetApp()->GetEnv('referer');
		
		$input = $this->_parseFormInput();
		
		$object = new WebSite($id);
		if (!$object->GetData())
		{
			$this->Message("没有找到要修改的站点".$id, LOCATION_BACK);
		}
		if($object->Update($input))
		{
			$this->Message("修改站点设置成功", $referer);
		}
		else
		{
			$this->Message("修改站点设置失败", LOCATION_BACK);
		}
	}
		
	public function playconfigAction()
	{
		$form = new HtmlForm('修改全局播放设置');
		
		$config = PlayerConfig::GetConfig(0, 0);
		$crs = $config->GetData();
		$keepdays = array(1,2,3,4,5,6,7,15,30,45,60,90,180);
		$keepdays = array_combine($keepdays,$keepdays);
		$posset = array('top'=>'顶部','bottom'=>'底部 ','left'=>'左侧','right'=>'右侧');
		$beginctrl = HtmlHelper::HourPicker('playtimebegin', $crs['playtimebegin']).'点 ';
		$endctrl = HtmlHelper::HourPicker('playtimeend', $crs['playtimeend']).'点 ';
		$downctrl = HtmlHelper::HourPicker('downloadtime', $crs['downloadtime']).'点 ';
		
		$form -> BindField('playtimebegin', '开始播放时间', $beginctrl,'');
		$form -> BindField('playtimeend', '结束播放时间', $endctrl,'');
		$form -> BindField('dowloadtime', '节目下载时间', $downctrl,'');
		$form -> BindField('reskeepdays', '媒体保存天数', $crs['reskeepdays'],'select',$keepdays);
		$form -> BindField('sidebarpos', '浮动菜单位置', $crs['sidebarpos'],'select',$posset,'','','');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function doplayconfigAction()
	{
		$configrs = array();
		$configrs['playtimebegin'] = $this->GetRequest()->GetInput('playtimebegin','int');
		$configrs['playtimeend'] = $this->GetRequest()->GetInput('playtimeend','int');
		$configrs['downloadtime'] = $this->GetRequest()->GetInput('downloadtime','int');
		$configrs['sidebarpos'] = $this->GetRequest()->GetInput('sidebarpos','varchar');
		$configrs['reskeepdays'] = $this->GetRequest()->GetInput('reskeepdays','int');
		$referer = $this->GetRequest()->GetInput('referer');
		$referer = $referer?$referer:$this->GetApp()->GetEnv('referer');
		
		$config = new PlayerConfig(PlayerConfig::TargetGlobal, 0);
		$config -> playtimebegin = $configrs['playtimebegin'];
		$config -> playtimeend = $configrs['playtimeend'];
		$config -> downloadtime = $configrs['downloadtime'];
		$config -> reskeepdays = $configrs['reskeepdays'];
		$config -> sidebarpos = $configrs['sidebarpos'];
				
		if($config->SaveData())
		{
			$config->SaveXml();
			$this->Message("修改全局播放设置成功", $referer);
		}
		else
		{
			$this->Message("修改全局播放设置失败", LOCATION_BACK);
		}
	}
	
}