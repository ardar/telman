<?php

class FrameController extends ControllerBase
{	
	public function indexAction()
	{
		$isLogin = $this->CheckPermission("", UnForceDown);
		if (!$isLogin)
		{
			$this->Redirect(urlhelper('login'));
		}
		$main_url = urlhelper('action', 'Msg','');
		$this->GetView()->Assign("mainurl", '');
		$this->GetView()->Display("_frame.htm");
		$this->GetApp()->AppExit();
	}
	
	public function headAction()
	{
		$this->GetView()->Assign('groupname', $this->GetAccount()->groupname);
		$this->GetView()->Assign('username', $this->GetAccount()->username);
		$this->GetView()->Display("_frame_head.htm");
		$this->GetApp()->AppExit();
	}
	
	private function _hasSubPrivilege($module)
	{
	    if(in_array('ALL', $this->GetAccount()->GetPrivileges()))
	    {
	        return true;
	    }
	    foreach ($this->GetAccount()->GetPrivileges() as $right)
	    {
	        $pos = strpos($right, $module);
	        if($pos!==false)
	        {
	            //TRACE("check $module pos:$pos, right:$right");
	            return true;
	        }
	    }
	    return false;
	}
	
	public function menuAction()
	{
		$this->CheckPermission();
		$rootlist = array();
		$totalcount = 0;
		//$filter_options[] = array('field'=>'hidden','value'=>'0','op'=>'=');
		$roots = $this->GetApp()->GetExtMenus();
		$acc = $this->GetAccount();
		
		if ($roots)
		{
		    $id = 0;
			foreach ($roots as $item)
			{
			    $root = array();
			    $root['menuid'] = $id++;
			    $root['image'] = $item['menu'][0];
			    $root['title'] = $item['menu'][1];
			    $root['url'] = $item['menu'][2];
			    $root['module'] = $item['menu'][3];
			    $root['showflag'] = 1;
				if ( $root['module'] && !$this->_hasSubPrivilege($root['module']))
				{
					continue;
				}
				$sublist = null;
				foreach ($item['sub'] as $subitem)
				{
				    $sub = array();
			        $sub['menuid'] = $id++;
    			    $sub['image'] = $subitem[0];
    			    $sub['title'] = $subitem[1];
    			    $sub['url'] = $subitem[2];
    			    $sub['module'] = $subitem[3];
			        $sub['showflag'] = 1;
					
					if ( $sub['module'] && !$this->_hasSubPrivilege($sub['module']) )
						continue;
					$sublist[] = $sub;
				}
				if (count($sublist)==0) {
					//continue;
				}
				$root['subs'] = $sublist;
				$rootlist[] = $root;
			}
		}
		$this->GetView()->Assign('groupname', $this->GetAccount()->groupname);
		$this->GetView()->Assign('username', $this->GetAccount()->username);
		$this->GetView()->Assign('roots', $rootlist);
		$this->GetView()->Display("_frame_menu.htm");
		$this->GetApp()->AppExit();
	}
		
	public function headbarAction()
	{
		$this->CheckPermission();
		$this->GetView()->Display("_frame_headbar.htm");
		$this->GetApp()->AppExit();
	}
}