<?php
class DeviceModule implements IExtension
{
    public function GetName()
    {
        return "设备管理";
    }
    
    public function Init($options)
    {
        
    }
    
    public function GetMenu()
    {
        return array('device.png','设备管理','','device');
    }
    
    public function GetSubMenus()
    {
        return array(
            array('device.png', '设备管理', '?controller=Device', 'device'),
            array('group.png', '逻辑分组', '?controller=LogicalGroup&action=list', 'logicalgroup'),
            array('monitor.png', '设备监控', '?controller=DeviceMonitor', 'device_monitor'),
            array('faildevice.png', '异常设备', '?controller=DeviceMonitor&action=faillist', 'device_monitor'),
            array('log.png', '设备日志', '?controller=FWLog&action=devicelist', 'log'),
        );
    }
    
    public function GetPriveleges()
    {
        return array(	
		'device_view' => '设备查看',
		'device_edit' => '设备修改',
		'device_monitor' => '设备监控',
		'device_operate' => '设备操控',
	
		'logicalgroup_view' => '逻辑组／查看',
		'logicalgroup_edit' => '逻辑组／修改',
        );
    }
}