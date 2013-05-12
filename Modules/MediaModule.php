<?php
class MediaModule implements IExtension
{
    public function GetName()
    {
        return "媒体管理";
    }
    
    public function Init($options)
    {
        
    }
    
    public function GetMenu()
    {
        return array('media.png','媒体管理','','media');
    }
    
    public function GetSubMenus()
    {
        return array(
            array('media.png', '公共媒体库', '?controller=Media', 'media'),
            //array('playlist.png', '节目包', '?controller=Playlist&action=list', 'playlist'),
            //array('template.png', '节目模板', '?controller=MediaTemplate&action=list', 'tempalte'),
            array('log.png', '媒体日志', '?controller=FWLog&action=medialist', 'log'),
        );
    }
    
    public function GetPriveleges()
    {
        return array(
    		'media_view' => '媒体库／查看',
    		'media_edit' => '媒体库／修改',
    		'media_audit' => '媒体库／审核',
        
    		//'playlist_view' => '节目包／查看',
    		//'playlist_edit' => '节目包／修改',
    	
    		//'template_view' => '节目模版／查看',
    		//'template_edit' => '节目模版／修改',
        );
    }
}