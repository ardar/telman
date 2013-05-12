<?php
class TelManModule implements IExtension
{
    public function GetName()
    {
        return "电话管理";
    }
    
    public function Init($options)
    {
        
    }
    
    public function GetMenu()
    {
        return array('system.png','电话管理','','telman');
    }
    
    public function GetSubMenus()
    {
        return array(
            array('system.png', '奶粉品牌', '?controller=TelManBrand&action=list', 'telmanbrand'),
            array('usergroup.png', '手机号码', '?controller=TelManTel&action=list', 'telmantel_view'),
            array('usergroup.png', '编名字', '?controller=TelManTel&action=presubmit', 'telmantel_presubmit'),
            array('usergroup.png', '上报号码', '?controller=TelManReport', 'telmantel_report'),
            array('usergroup.png', '录入结果', '?controller=TelManTel&action=result', 'telmantel_result'),
            //array('user.png', '宝宝管理', '?controller=TelManBaby&action=list', 'telmantel'),
            //array('usergroup.png', '上报号码', '?controller=TelManTel&action=submit', 'telmantel_submit'),
            array('usergroup.png', '上报汇总', '?controller=TelManTel&action=sheet', 'telmantel_sheet'),
            array('usergroup.png', '结果汇总', '?controller=TelManTel&action=resultsheet', 'telmantel_rsheet'),
        );
    }
    
    public function GetPriveleges()
    {
        return array(
        	'telmanbrand_view' => '品牌查看',
        	'telmanbrand_edit' => '品牌设置',
    		'telmantel_admin' => '号码全局管理',
    		'telmantel_view' => '查看个人号码',
    		'telmantel_edit' => '修改个人号码',
    		'telmantel_presubmit' => '编名字',
    		'telmantel_report' => '上报号码',
    		'telmantel_result' => '录入结果',
    		'telmantel_sheet' => '查看上报汇总',
    		'telmantel_rsheet' => '查看结果汇总',
        );
    }
}