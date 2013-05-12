<?php
class AccountController extends ControllerBase
{	
	const ModuleRight='account';
	const ObjName = "用户";
		
	public function codeAction()
	{
		ob_clean();
		$string = OpenDev::GenConfirmCode(4, 30);
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-type: image/png");
		$im = @imagecreate(50, 22)
		    or die("Cannot Initialize new image stream");
		$background_color = imagecolorallocate($im, 255, 255, 255);
		$text_color = imagecolorallocate($im, 233, 14, 91);
		imagestring($im, 7, 7, 3,  $string, $text_color);
		imagepng($im);
		imagedestroy($im);
		$this->GetApp()->AppExit();
	}
	
	public function loginAction()
	{
		$confirmcodeurl = urlhelper('action', 'Account', 'code');
		$this->GetView()->Assign("confirmcode", $confirmcodeurl);
		$this->GetView()->Assign(FIELD_CONTROLLER, $this->GetControllerId());
		$this->GetView()->Assign(FIELD_ACTION, 'dologin');
		$this->GetView()->Display("login.htm");
		$this->GetApp()->AppExit();
	}
	
	public function dologinAction()
	{
		$inputUser = $this->GetRequest()->GetInput("username", "string", "用户名必须填写");
		$inputPass = $this->GetRequest()->GetInput("password", "string", "密码必须填写");
		$saveloginstate = $this->GetRequest()->GetInput('savesatete','int');
		
		if($this->GetApp()->GetSetting()->UseLoginConfirmCode)
		{
    		$inputcode = $this->GetRequest()->GetInput("confirmcode", "string", "验证码必须填写");
    		
    		if (!OpenDev::CheckConfirmCode($inputcode))
    		{
    			$this->Message("验证码填写不正确 ");
    		}
		}
		
		
		$acc = new Account();
		if($acc -> Login($inputUser, $inputPass, 'web',$saveloginstate))
		{
			FWLog::SecurityLog(FWLog::ActionLogin, true, $acc->GetData(), $acc->GetData());
			$this->Message("", LOCATION_UCP);
		}
		else
		{
			FWLog::SecurityLog(FWLog::ActionLogin, false, $acc->GetData(), $acc->GetData());
			$this->Message("登陆失败", LOCATION_BACK);
		}
	}
	
	public function logoutAction()
	{
	    $rs = $this->GetAccount()->GetData();
		$this->GetAccount()->Logout();
		FWLog::SecurityLog(FWLog::ActionLogout, true, $rs, $rs);
		$this->Message("注销成功", LOCATION_HOME);
	}
	
	public function listAction()
	{
		$this->CheckPermission(self::ModuleRight);
		
		$totalcount = 0;
		$join_option[] = 
			array('table'=>'accountgroup', 'left_field'=>'groupid', 'right_field'=>'groupid');
		$filter_fields = array(
			'username' => '%like%', 
			'realname' => '%like%',
			'groupid' => '==',
			);
		$list = DBHelper::GetJoinRecordSet(Account::GetTable(), 
			$join_option, $this->GetFilterOption($filter_fields), $this->GetSortOption(),
			$this->_offset, $this->_perpage, $totalcount);
		$groups = DBHelper::GetRecords(AccountGroup::GetTable(), 'issystem', 0);
		
		$table = new HtmlTable("用户列表",'border');
		$table -> BindField('userid', "ID", '', 'list_pk', 'desc');
		$table -> BindField('username', "用户名", '', '', 'asc');
		$table -> BindField('realname', "姓名", '', '', 'asc');
		$table -> BindField('groupname', "用户组", '', '', 'asc');
		$table -> BindField('email', "邮箱");
		$table -> BindField('mobile', "手机号码");
		$table -> BindField('tel', "固定电话");
		$table -> BindField('status', "启用", 'bool', '', 'asc');
		$table -> BindField('cp', "操作", 'cp', '','', 
			array('设置'=>array('', 'edit','userid')));//,'删除'=>array('', 'delete','username')
		$table -> BindData($list);
		$table -> BindPager($totalcount, $this->_perpage, $this->_page);
		
		$table -> BindHeadbar(" ".HtmlHelper::Button('创建用户','link',urlhelper('action',$this->GetControllerId(),'add')));
		$table -> BindHeadbar(" 用户名".HtmlHelper::Input('username','','text'));
		$table -> BindHeadbar(" 姓名".HtmlHelper::Input('realname','','text'));
		$table -> BindHeadbar(" ".HtmlHelper::ListBox('groupid','',$groups,'groupid','groupname','按用户组'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_ACTION, $this->GetActionId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Input(FIELD_CONTROLLER, $this->GetControllerId(),'hidden'));
		$table -> BindHeadbar(HtmlHelper::Button('查找','submit'));
		
		$this->GetView()->Assign('table', $table);
		$this->GetView()->Display('_data_table.htm');
	}
	
	public function deleteAction()
	{
		$this->CheckPermission(self::ModuleRight);
		
		$username = $this->GetRequest()->GetInput('username','string','没有指定要删除的用户 ');
		if ($username==$this->GetAccount()->GetId())
		{
			$this->Message("不能删除当前登陆用户  ".$username." ", LOCATION_BACK);
		}
		$acc = new Account($username);
		$rs = $acc->GetData();
		if($rs && $acc -> Delete())
		{
			FWLog::SecurityLog(FWLog::ActionDelete, true, $rs, $this->GetAccount()->GetData());
			$this->Message("删除用户  ".$username." 成功", LOCATION_REFERER);
		}
		else
		{
			$this->Message("删除用户  ".$username." 失败", LOCATION_BACK);
		}
	}
	
	public function addAction()
	{
		$this->CheckPermission(self::ModuleRight);
		
		$rs = array();
		$form = new HtmlForm('创建用户');
		$form -> BindField('username', '用户名', $rs['username'],'text');
		$form -> BindField('password', '设置密码', '','password');
		$form -> BindField('password_confirm', '重复密码', '','password');
		$groups = DBHelper::GetRecords(AccountGroup::GetTable(), 'issystem', 0);
		$form -> BindField('groupid', '角色', $rs['groupid'],'select',$groups,'groupid','groupname');
		$form -> BindField('status', '启用', $rs['status'],'check');
		$form -> BindField('realname', '真实姓名', $rs['realname'],'text');;
		$form -> BindField('email', 'email', $rs['email'],'text');
		$form -> BindField('mobile', '手机号码', $rs['mobile'],'text');	
		$form -> BindField('tel', '电话', $rs['tel'],'text');	
		
		//$allrepeater[] = array('deviceid'=>'ALL','title'=>'全部设备');
		//$repeaters = Device::BuildRepeaterComboList(0);
		//$devicelist = array_merge($allrepeater, $repeaters);
		//$form -> BindField('deviceright', '设备监控权限', array(),'checklist',$devicelist,'deviceid','title');
		
		$form -> BindField('intro','备注',$rs['intro'],'textarea');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function profileAction($is_adding=false)
	{
		$this->CheckPermission();
		
		$acc = $this->GetAccount();
		$rs =$acc->GetData();
		if (!$rs)
		{
			$this->Message("没有找到当前用户  ", LOCATION_BACK);
		}
		if ($acc->issystem)
		{
			$this->Message("用户属于系统组,无法编辑 .", LOCATION_BACK);
		}
		$form = new HtmlForm('修改用户资料');
		$form -> BindField('userid', 'ID', $rs['userid'],'');
		$form -> BindField('username', '用户名', $rs['username'],'');
		$form -> BindField('password', '修改密码', '','password');
		$form -> BindField('password_confirm', '重复密码', '','password');
		
		$form -> BindField('groupid', '用户组', $acc->groupname,'');
		$form -> BindField('status', '启用', $rs['status'],'bool');
		$form -> BindField('realname', '真实姓名', $rs['realname'],'text');;
		$form -> BindField('email', 'email', $rs['email'],'text');
		$form -> BindField('mobile', '手机号码', $rs['mobile'],'');
		$form -> BindField('tel', '固定电话', $rs['tel'],'text');
		$form -> BindField('isloginsmson', '启用登录短信', $rs['isloginsmson'],'bool');
		$form -> BindField('userid','',$rs['userid'],'hidden');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}
	
	public function editAction($is_adding=false)
	{
		$this->CheckPermission(self::ModuleRight);
		
		$id = $this->GetRequest()->GetInput('userid', 'int', '没有指定要编辑的用户');
		$acc = new Account($id);
		$rs =$acc->GetData();
		if (!$rs)
		{
			$this->Message("没有找到要编辑的用户  ".$id, LOCATION_BACK);
		}
		if ($acc->issystem)
		{
			$this->Message("用户 " .$id." 属于系统组,无法编辑 .", LOCATION_BACK);
		}
		$form = new HtmlForm('编辑用户');
		$form -> BindField('userid', 'ID', $rs['userid'],'');
		$form -> BindField('username', '用户名', $rs['username'],'');
		$form -> BindField('password', '修改密码', '','password');
		$form -> BindField('password_confirm', '重复密码', '','password');
		$groups = DBHelper::GetRecords(AccountGroup::GetTable(), 'issystem', 0);
		$form -> BindField('groupid', '角色', $rs['groupid'],'select',$groups,'groupid','groupname');
		$form -> BindField('status', '启用', $rs['status'],'check');
		$form -> BindField('realname', '真实姓名', $rs['realname'],'text');;
		$form -> BindField('email', 'email', $rs['email'],'text');
		$form -> BindField('mobile', '手机号码', $rs['mobile'],'text');
		$form -> BindField('tel', '固定电话', $rs['tel'],'text');
		$form -> BindField('isloginsmson', '启用登录短信', $rs['isloginsmson'],'bool');
		
//		$currright = explode('|', $rs['deviceright']);
//		$allrepeater[] = array('deviceid'=>'ALL','title'=>'全部设备');
//		$repeaters = Device::BuildRepeaterComboList(0);
//		$devicelist = array_merge($allrepeater, $repeaters);
//		$form -> BindField('deviceright', '设备监控权限', $currright,'checklist',$devicelist,'deviceid','title');
		
		$currnewsright = explode('|', $rs['newsright']);
		$allcategory = array();
		$allcategory[] = array('categoryid'=>'ALL','title'=>'全部分类');
		$categories = NewsCategory::BuildComboList(0);
		$categorylist = array_merge($allcategory, $categories);
		$form -> BindField('newsright', '新闻分类权限', $currnewsright,'checklist',$categorylist,'categoryid','title');
		
		$currnewsright = explode('|', $rs['innernewsright']);
		$allcategory = array();
		$allcategory[] = array('categoryid'=>'ALL','title'=>'全部分类');
		$categories = InnerNewsCategory::BuildComboList(0);
		$categorylist = array_merge($allcategory, $categories);
		$form -> BindField('innernewsright', '内部分类权限', $currnewsright,'checklist',$categorylist,'categoryid','title');
		
		$form -> BindField('intro','备注',$rs['intro'],'textarea');
		//$form -> BindField('regip','注册IP',$rs['regip'],'');
		$form -> BindField('regtime','创建时间',$rs['regtime'],'datetime');
		$form -> BindField('userid','',$rs['userid'],'hidden');
		$form -> BindField(FIELD_CONTROLLER,'',$this->GetControllerId(),'hidden');
		$form -> BindField(FIELD_ACTION,'','do'.$this->GetActionId(),'hidden');
		$form -> BindField('referer','',$this->GetApp()->GetEnv('referer'),'hidden');
		$this->GetView()->Assign('form', $form);
		$this->GetView()->Display('_data_form.htm');
	}

	public function doaddAction()
	{
		$this->CheckPermission(self::ModuleRight);
		
		$input_username = $this->GetRequest()->GetInput('username','string','用户名必须填写');
		
		$count = DBHelper::GetSingleRecord(Account::GetTable(), 'username', $input_username);
		if ($count>0)
		{
			$this->Message("用户名  ".$input_username." 已存在", LOCATION_BACK);
		}
		
		$input = $this->_parseFormInput();
		$input['username'] = $input_username;
		
		$acc = new Account();
		
		$referer = $this->GetRequest()->GetInput('referer');
		
		if($acc->Insert($input))
		{
			FWLog::SecurityLog(FWLog::ActionAdd, true, $acc->GetData(), $this->GetAccount()->GetData());
			$this->Message("创建用户成功", $referer);
		}
		else
		{
			$this->Message("创建用户失败", LOCATION_BACK);
		}		
	}
	
	private function _parseFormInput()
	{
	    $password = $this->GetRequest()->GetInput('password');
		$userpass_confirm = $this->GetRequest()->GetInput('password_confirm');
		if ($password) 
		{
			if ($password!=$userpass_confirm)
			{
				$this->Massage('两次输入的密码不相同 ',LOCATION_BACK);
			}
			else 
			{
				$input['password'] = $password;
			}
		}
		if($input['password'])
		{
			$input['password'] = Account::EncryptPassword($input['password']);
		}
		
		$input['groupid'] = $this->GetRequest()->GetInput('groupid','int','所属角色必须选择');
		$input['realname'] = $this->GetRequest()->GetInput('realname','string','真实姓名必须填写');
		
		$input['email'] = $this->GetRequest()->GetInput('email');
		$input['mobile'] =$this->GetRequest()->GetInput('mobile');
		$input['tel'] =$this->GetRequest()->GetInput('tel');
		$input['qq'] = $this->GetRequest()->GetInput('qq');
		$input['intro'] = $this->GetRequest()->GetInput('intro');
		$input['status'] = $this->GetRequest()->GetInput('status','int');
		$input['isloginsmson'] = $this->GetRequest()->GetInput('isloginsmson','int');
		
//		$rightarray = $this->GetRequest()->GetInput('deviceright' , 'array');
//		$rightstring = '';
//		if($rightarray)
//		{
//			foreach ($rightarray as $val)
//			{
//				if($val=='ALL')
//				{
//					$rightstring = $val;
//					break;
//				}
//				$rightstring .= $rightstring?('|'.$val):$val;
//			}
//		}
//		$input['deviceright'] = $rightstring;
		
		$newsrightarray = $this->GetRequest()->GetInput('newsright' , 'array');
		$newsrightstring = '';
		if($newsrightarray)
		{
			foreach ($newsrightarray as $val)
			{
				if($val=='ALL')
				{
					$newsrightstring = $val;
					break;
				}
				$newsrightstring .= $newsrightstring?('|'.$val):$val;
			}
		}
		$input['newsright'] = $newsrightstring;
		
		$newsrightarray = $this->GetRequest()->GetInput('innernewsright' , 'array');
		$newsrightstring = '';
		if($newsrightarray)
		{
			foreach ($newsrightarray as $val)
			{
				if($val=='ALL')
				{
					$newsrightstring = $val;
					break;
				}
				$newsrightstring .= $newsrightstring?('|'.$val):$val;
			}
		}
		$input['innernewsright'] = $newsrightstring;
		return $input;
	}

	public function doeditAction()
	{
		$this->CheckPermission(self::ModuleRight);
		
		$userid = $this->GetRequest()->GetInput('userid', 'int', '没有指定要编辑的用户');
	    $acc = new Account($userid);
		if (!$acc->GetData())
		{
			$this->Message("没有找到要编辑的用户  ".$userid, LOCATION_BACK);
		}
		$input = $this->_parseFormInput();
	
		
		$referer = $this->GetRequest()->GetInput('referer');

		if($acc->Update($input))
		{
			FWLog::SecurityLog(FWLog::ActionUpdate, true, $acc->GetData(), $this->GetAccount()->GetData());
			$this->Message("修改用户资料成功", $referer);
		}
		else
		{
			$this->Message("修改用户失败", LOCATION_BACK);
		}
		
	}
		
	public function doprofileAction()
	{
		$this->CheckPermission();
		
		$password = $this->GetRequest()->GetInput('password');
		$userpass_confirm = $this->GetRequest()->GetInput('password_confirm');
		if ($password) 
		{
			if ($password!=$userpass_confirm)
			{
				$this->Massage('两次输入的密码不相同 ',LOCATION_BACK);
			}
			else 
			{
				$input['password'] = $password;
			}
		}
		$input['realname'] = $this->GetRequest()->GetInput('realname','string','真实姓名必须填写');
		$acc = $this->GetAccount();
		if (!$acc->GetData())
		{
			$this->Message("没有找到要编辑的用户  ", LOCATION_BACK);
		}
		if($input['password'])
		{
			$input['password'] = $acc->EncryptPassword($input['password']);
		}
		$input['email'] = $this->GetRequest()->GetInput('email');
		//$input['mobile'] =$this->GetRequest()->GetInput('mobile');
		$input['tel'] =$this->GetRequest()->GetInput('tel');
		$input['qq'] = $this->GetRequest()->GetInput('qq');
		
		if($acc->Update($input))
		{
			FWLog::SecurityLog(FWLog::ActionUpdate, true, $acc->GetData(), $acc->GetData());
			$this->Message("修改个人资料成功", LOCATION_REFERER);
		}
		else
		{
			$this->Message("修改个人资料失败", LOCATION_BACK);
		}
	}
}