<?php

class MsgController extends ControllerBase
{
	const ModuleRight='msg';
	const ObjName = "短消息";

	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		$this->CheckPermission(self::ModuleRight);
	}
	
	private function _checkMsgOwner(Msg $msg)
	{
	    $msg->GetData();
	    if ($msg->ownerid!=$this->GetAccount()->GetId())
	    {
	        $this->Message('没有权限操作该消息'.$msg->ownerid);
	    }
	}
	
	public function listAction()
	{
		$totalcount = 0;
		$isreaded = $this->GetRequest()->GetInput('isreaded');
		$box = $this->GetRequest()->GetInput('box');
		$box = $box ? $box : Msg::MsgInBox;
		$boxname = Msg::$Boxies[$box];
		
		$option_set = array(
			'title' => '%like%', 
			'to' => '%like%',
			'from' => '%like%',
		);
		$filter_options = $this->GetFilterOption($option_set);
		
		$filter_options[] = DBHelper::FilterOption(null, 'ownerid', $this->GetAccount()->GetId(), '=');
		$filter_options[] = DBHelper::FilterOption(null, 'box', $box, '=');		
		if($isreaded!=='')
		{
		    $filter_options[] = DBHelper::FilterOption(null, 'isreaded', $isreaded, '=');
		}
		$list = DBHelper::GetJoinRecordSet(Msg::GetTable(), null, 
		    $filter_options, $this->GetSortOption('msgid','desc'), 
		    $this->_offset, $this->_perpage, $totalcount);
		
		foreach ($list as $key=>$rs)
		{
		    if(!$rs['isreaded'] && $rs['isemergency'])
		    {
		        $list[$key]['title'] = "<font color=red>".$list[$key]['title']."</font>";
		    }
		    if(!$rs['isreaded'])
		    {
		        $list[$key]['title'] = "<b>".$list[$key]['title']."</b>";
		    }
		    $list[$key]['title'] = HtmlHelper::Link( $list[$key]['title'], 
		        urlhelper('action',$this->GetControllerId(),'view',"&msgid=".$rs['msgid']),
		        '','','style="display:block"');
		}
		
		$table = new HtmlTable(self::ObjName."列表 - ".$boxname,'border');
		$table -> BindField('msgid', "ID", 'checkbox', '', 'desc');
		$table -> BindField('msgid', "ID", '', '', 'desc');
		$table -> BindField('title', "标题", '', '', 'asc','','left');
		if($box==Msg::MsgInBox)
		{
		$table -> BindField('from', "发件人", '', '', 'asc');
		}
		if($box==Msg::MsgOutBox)
		{
		$table -> BindField('to', "收件人",'', '', 'asc');
		}
		$table -> BindField('isreaded', "已读",'bool');
		$table -> BindField('isemergency', "紧急",'bool');
		$table -> BindField('sendtime', "发送时间",'datetime', '', 'asc');
		$table -> BindField('cp', "操作", 'cp', '','', 
			array(
			'查看'=>array('', 'view','msgid'),
			'回复'=>array('', 'reply','msgid'),
			'转发'=>array('', 'forward','msgid'),
			'删除'=>array('', 'delete','msgid')));
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		$table -> BindHeadbar(" ".HtmlHelper::Button('发送'.self::ObjName,'link',
		    urlhelper('action',$this->GetControllerId(),'send')));
//		foreach(Msg::$Boxies as $boxkey=>$boxname)
//		{
//		$table -> BindToolbar(" ".HtmlHelper::Button($boxname,'link',
//		    urlhelper('action',$this->GetControllerId(),'list',"&box=$boxkey")));
//		}
		
		$readstatus = array('0'=>'未读','1'=>'已读');
		$table -> BindHeadbar(" | 按标题".HtmlHelper::Input('title','','text'));
		if($box==Msg::MsgInBox)
		{
		$table -> BindHeadbar(" 按发件人".HtmlHelper::Input('from','','text'));
		}
		if($box==Msg::MsgOutBox)
		{
		$table -> BindHeadbar(" 按收件人".HtmlHelper::Input('from','','text'));
		}
		//$table -> BindHeadbar(" ".HtmlHelper::ListBox('box',$box,Msg::$Boxies,'',''));
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('isreaded',$isreaded,$readstatus,'','','全部状态'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		    
		$table -> BindFootbar(" | 将选中项:");
		$table -> BindFootbar(HtmlHelper::Input(FIELD_ACTION, 'none','hidden'));
		$table -> BindFootbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindFootbar(HtmlHelper::Button('删除', 'button',
			"onclick=\"if(!confirm('是否确认删除选中的项目'))return false;form.action.value='delete';form.submit();\""));
		$table -> BindFootbar(HtmlHelper::Button('设为已读', 'button',
			"onclick=\"if(!confirm('是否将选中的项目设为已读'))return false;form.action.value='setread';form.submit();\""));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function deleteAction()
	{
		$ids = $this->GetRequest()->GetInput('msgid', DT_ARRAY);
		if (!$ids)
		{
			$ids[] = $this->GetRequest()->GetInput('msgid','int','没有指定要删除的'.self::ObjName);
		}
		
		foreach ($ids as $id)
		{
			$obj = new Msg($id);
			if (!$obj->GetData())
			{
				$this->Message("没有找到要删除的".self::ObjName." ".$id, LOCATION_REFERER);
			}
			$this->_checkMsgOwner($obj);
			if(!$obj -> Delete())
			{
				$this->Message("删除 ".self::ObjName."失败", LOCATION_REFERER);
			}
		}
		$this->Message("删除".self::ObjName."成功", LOCATION_REFERER);
	}
	
	public function setreadAction()
	{
		$ids = $this->GetRequest()->GetInput('msgid', DT_ARRAY);
		if (!$ids)
		{
			$ids[] = $this->GetRequest()->GetInput('msgid','int','没有指定要设置已读的'.self::ObjName);
		}
		
		foreach ($ids as $id)
		{
			$obj = new Msg($id);
			if (!$obj->GetData())
			{
				$this->Message("没有找到要设置已读的".self::ObjName." ".$id, LOCATION_REFERER);
			}
			$this->_checkMsgOwner($obj);
			if(!$obj -> Read(true))
			{
				$this->Message("设置 ".self::ObjName."为已读失败", LOCATION_REFERER);
			}
		}
		$this->Message("设置".self::ObjName."为已读成功", LOCATION_REFERER);
	}

	public function viewAction()
	{
		$id = $this->GetRequest()->GetInput('msgid', 'int', '没有指定要修改的'.self::ObjName);
		$obj = new Msg($id);
		$rs = $obj->GetData();
		if (!$rs)
		{
			$this->Message("没有找到要查看的".self::ObjName." ".$id, LOCATION_BACK);
		}
		$this -> _checkMsgOwner($obj);
		
		$obj->Read(true);
		
		$form = new HtmlForm('查看'.self::ObjName);
		$form -> BindField('msgid', 'ID', $rs['msgid'],'');
		$form -> BindField('from', '发件人', $rs['from'], '');
		$form -> BindField('to', '收件人', $rs['to'], '');
		$form -> BindField('title', '标题', "<b>".$rs['title']."</b>", '');
		$form -> BindField('content', '内容', "<pre>".$rs['content']."</pre>", '');
		//$form -> BindField('isimportant', '是否重要', $rs['isimportant'],'bool');
		$form -> BindField('isemergency', '是否紧急', $rs['isemergency'],'bool');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
		
		$form -> BindButton(HtmlHelper::Button('回复','button',"onclick=\"window.location.href='".
		    urlhelper('action',$this->GetControllerId(),'reply',"&msgid=".$rs['msgid'])."'\""));
		$form -> BindButton(HtmlHelper::Button('转发','button',"onclick=\"window.location.href='".
		    urlhelper('action',$this->GetControllerId(),'forward',"&msgid=".$rs['msgid'])."'\""));
		$form -> BindButton(HtmlHelper::Button('返回','button',"onclick=\"window.history.back()\""));
		
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function forwardAction()
	{
	    $msgid = $this->GetRequest()->GetInput('msgid','int');
	    $msg = new Msg($msgid);
	    $rs = $msg -> GetData();
		$this -> _checkMsgOwner($msg);
	    
		$form = new HtmlForm('转发'.self::ObjName);
		$form -> BindField('from', '发件人', $this->GetAccount()->GetName(), '');
		$form -> BindField('to', '收件人', '', 'text');
		$form -> BindField('title', '标题', "转发: ".$rs['title'], 'text');
		$form -> BindField('content', '内容', "原文来自 ".$rs['from'].": \n".$rs['content'], 'textarea');
		//$form -> BindField('isimportant', '是否重要', $rs['isimportant'],'check');
		$form -> BindField('isemergency', '是否紧急', $rs['isemergency'],'check');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','dosend','hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function replyAction()
	{
	    $msgid = $this->GetRequest()->GetInput('msgid','int');
	    $msg = new Msg($msgid);
	    $rs = $msg -> GetData();
		$this -> _checkMsgOwner($msg);
	    
		$form = new HtmlForm('回复'.self::ObjName);
		$form -> BindField('from', '发件人', $this->GetAccount()->GetName(), '');
		$form -> BindField('to', '收件人', $rs['from'], 'text');
		$form -> BindField('title', '标题', "回复: ".$rs['title'], 'text');
		$form -> BindField('content', '内容', '', 'textarea');
		//$form -> BindField('isimportant', '是否重要', $rs['isimportant'],'check');
		$form -> BindField('isemergency', '是否紧急', $rs['isemergency'],'check');
		$form -> BindField('', '邮件原文', "<pre>".$rs['content']."</pre>", '');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','dosend','hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function sendAction()
	{
	    $to = $this->GetRequest()->GetInput('to');
	    $title = $this->GetRequest()->GetInput('title');
		$rs = array();
		$form = new HtmlForm('发送'.self::ObjName);
		$form -> BindField('from', '发件人', $this->GetAccount()->GetName(), '');
		$form -> BindField('to', '收件人', $to, 'text');
		$form -> BindField('title', '标题', $title, 'text');
		$form -> BindField('content', '内容', $rs['content'], 'textarea');
		//$form -> BindField('isimportant', '是否重要', $rs['isimportant'],'check');
		$form -> BindField('isemergency', '是否紧急', $rs['isemergency'],'check');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function dosendAction()
	{		
		$to = $this->GetRequest()->GetInput('to','string','收件人必须填写');
		$title = $this->GetRequest()->GetInput('title','string');
		$content = $this->GetRequest()->GetInput('content','string');
		$isimportant = $this->GetRequest()->GetInput('isimportant','int');
		$isemergency = $this->GetRequest()->GetInput('isemergency','int');
		$referer = $this->GetRequest()->GetInput('referer');
		$title = $title ? $title : OpenDev::sub_title($content, 20, 'utf-8');
		
		$result = Msg::SendMsgByName($this->GetAccount()->GetName(), $to, 
		    $title, $content, $isimportant,$isemergency);
		
		if($result)
		{
			$this->Message("发送".self::ObjName."成功", 
			    urlhelper('action',$this->GetControllerId(),'list'));
		}
		else
		{
			$this->Message("发送".self::ObjName."失败", LOCATION_BACK);
		}
	}
}