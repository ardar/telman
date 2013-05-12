<?php

class UIUtilityController extends ControllerBase
{
	const ModuleRight='';
	
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
		
		$this->CheckPermission(self::ModuleRight);
	}
	
	public function calendarAction()
	{
	    $cookie_value = $this->GetRequest()->GetInputFrom(INPUT_COOKIE,'fwui_calendar_time','string');
	    $cookie_time = strtotime($cookie_value);
		$ori_value = $this->GetRequest()->GetInput('ori_value','string');
		$temp_time = strtotime($ori_value);
		$temp_time = $temp_time ? $temp_time : ($cookie_time?$cookie_time:time());
		$init_value = date('Y-m-d', $temp_time);
		$init_year = date('Y', $temp_time);
		$init_month = date('m', $temp_time);
		$init_day = date('d', $temp_time);
		$curr_year = date('Y');
		$curr_month = date('m');
		$curr_day = date('d');
		$this->GetView()->Assign('curr_year',$curr_year);
		$this->GetView()->Assign('curr_month',$curr_month);
		$this->GetView()->Assign('curr_day',$curr_day);
		$this->GetView()->Assign('init_value',$init_value);
		$this->GetView()->Assign('init_year',$init_year);
		$this->GetView()->Assign('init_month',$init_month);
		$this->GetView()->Assign('init_day',$init_day);
		$this->GetView()->Display('_ui_calendar.htm');
		$this->GetApp()->AppExit();
	}
}