<?php

class NativeView implements IView
{
	private $_subcaching = false;
	private $_html_editor_tpl='_html_editor.htm';
	private $_setting;
	private $_template_dir;
	private $_cache_dir;
	private $_is_html_cache = false;
	private $_html_cache_lifetime = 30;//in seconds
	private $_debug_on = false;
	private $_assignedVals = array();
	
	public function __construct()
	{
		
	}
	
	public function Init($setting)
	{
		$this->_setting = $setting;
		$this->_template_dir = $this->_setting['template_dir'];
		//$this->_smarty -> compile_dir = $this->_setting['compile_dir'];
		//$this->_smarty -> config_dir = $this->_setting['config_dir'];
		$this->_cache_dir = $this->_setting['cache_dir'];
		$this->_is_html_cache = $this->_setting['cache_html'];
		$this->_html_cache_lifetime = $this->_setting['cache_html_lifetime'];
		$this->_debug_on = $this->_setting['debug_on'];
		
//		$this->_smarty-> registerPlugin("modifier", "formatdate", array($this,"_formatdate"));
//		$this->_smarty-> registerPlugin("modifier", "formatdatetime", array($this,"_formatdatetime"));
//		
//		$this->_smarty-> registerPlugin("function", "time_now", array($this,"_time_now"));
//		$this->_smarty-> registerPlugin("function", "url_helper", array($this,"_urlhelper"));
//		$this->_smarty-> registerPlugin("function", "data_table", array($this,"_data_table"));
//		$this->_smarty-> registerPlugin("function", "data_form", array($this,"_data_form"));
//		$this->_smarty-> registerPlugin("function", "html_editor", array($this,"_html_editor"));
	}
	
	public function Assign($field, $value)
	{
		$this->_assignedVals[$field] = &$value;
	}
	
	public function Display($template, $cache_id=null, $params=null)
	{
		try 
		{
			extract($this->_assignedVals, EXTR_SKIP|EXTR_REFS);
			$tpl_file = $this->_template_dir.'/'.$template;
			require ($tpl_file);
		}
		catch (ErrorException $e)
		{
			throw new PCException("解析模板错误: ".$e->getMessage(), LOCATION_REFERER, 0, $e);
		}
	}
	
	public function GetOutput($template, $chacheid, $params)
	{
		return null;
	}
}