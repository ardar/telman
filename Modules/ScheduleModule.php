<?php
class ScheduleModule implements IExtension
{
    public function GetName()
    {
        return "节目发布";
    }
    
    public function Init($options)
    {
        
    }
    
    public function GetMenu()
    {
        return array('schedule.png','节目发布','','schedule');
    }
    
    public function GetSubMenus()
    {
        return array(
            array('schedule.png', '常规节目表', '?controller=Schedule', 'schedule'),
            array('emschedule.png', '插播节目表', '?controller=EmSchedule&action=list', 'emschedule'),
            array('log.png', '节目表日志', '?controller=FWLog&action=publishlist', 'log'),
        );
    }
    
    public function GetPriveleges()
    {
        return array(
		'schedule_view' => '节目时间表／查看',
		'schedule_edit' => '节目时间表／编排',
		'schedule_audit' => '节目时间表／审核',
	
		'emschedule_view' => '插播节目／查看',
		'emschedule_edit' => '插播节目／发布',
        );
    }
}