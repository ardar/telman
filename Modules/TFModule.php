<?php
class TFModule implements IExtension
{
    public function GetName()
    {
        return "信息管理";
    }
    
    public function Init($options)
    {
        
    }
    
    public function GetMenu()
    {
        return array('touch.png','信息管理','','tf');
    }
    
    public function GetSubMenus()
    {
        return array(
            array('tfcategory.png', '分类管理', '?controller=TFCategory&action=list', 'tfcategory'),
            array('tfformat.png', '格式定义', '?controller=TFFormat&action=list', 'tf_setting'),
            array('entity.png', '信息管理', '?controller=TFEntity', 'tf_entity'),
            array('entity_add.png', '添加信息', '?controller=TFEntity&action=preadd', 'tf_entity'),
            array('tffile.png', '附件管理', '?controller=TFFile&action=list', 'tf_file'),
        );
    }
    
    public function GetPriveleges()
    {
        return array(
		'tf_setting' => '信息管理／参数设置',
	
		'tf_category_view' => '信息管理／分类查看',
		'tf_category_edit' => '信息管理／分类设置',
	
		'tf_entity_view' => '信息管理／信息查看',
		'tf_entity_edit' => '信息管理／信息管理',
		'tf_file' => '信息管理／附件上传',
        );
    }
}