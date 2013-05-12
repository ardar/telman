<?php
class HtmlFormField extends HtmlElement
{
	protected $_value;
	protected $_type;
	protected $_options;
	protected $_optionfield;
	protected $_optiontext;
	protected $_nulltitle;
	protected $_ext;
	protected $_isReadOnly;
	
	public function __construct($id, $title, $value, $type='text', 
		$options=null, $optionfield='',$optiontext='',$nulltitle=null, $ext = null)
	{
		$this->_title = $title;
		$this->_id = $id;
		$this->_name = $id;
		
		$this->_value = $value;
		$this->_type = $type;
		$this->_options = $options;
		$this->_optionfield = $optionfield;
		$this->_optiontext = $optiontext;
		$this->_nulltitle = $nulltitle;
		$this->_ext = $ext;
	}
	
	public function GetType()
	{
		return $this->_type;
	}
	
	public function GetValue()
	{
		return $this->_value;
	}
	
	public function GetLabel()
	{
		return $this->_title;
	}
	
	public function SetReadOnly($isReadOnly=true)
	{
		$this->_isReadOnly = $isReadOnly;
	}
	
	public function GetBody()
	{
		$readonly = $this->_isReadOnly ? "ReadOnly=true" : '';
		switch ($this->_type)
		{
			case 'hidden':
				$val = "<input name='".$this->_id."' type='hidden' id='".$this->_id."' value='".$this->_value."' >";
				break;	
			case 'bool':
			    $val = $this->_value ? "√" : "×";
			    break;
			case 'password':
			case 'text_readonly':
			case 'text':
			case 'file':
			case 'imagefile':
			case 'requirefile':
				$val = HtmlHelper::Input($this->_id,$this->_value,$this->_type,' size=40 '.$this->_ext);
				break;
			case 'check':				
				$val = HtmlHelper::Input($this->_id,1,'checkbox',($this->_value?' checked':''));
				break;
			case 'radio':							
				$val = HtmlHelper::Input($this->_id,1,'radio',($this->_value?' checked':''));
				break;
			case 'select':
				$val = HtmlHelper::ListBox($this->_id,$this->_value,$this->_options
					,$this->_optionfield,$this->_optiontext,$this->_nulltitle, $this->_ext);
				break;
			case 'radiolist':
				$val = HtmlHelper::RadioList($this->_id,$this->_value,$this->_options
					,$this->_optionfield,$this->_optiontext,'<br/>', $this->_nulltitle);				
				break;
			case 'checklist':
				$val = HtmlHelper::CheckList($this->_id,$this->_value,$this->_options
					,$this->_optionfield,$this->_optiontext,'<br/>');
				break;
			case 'textarea':
				$val = "<TEXTAREA class=inputarea NAME='".$this->_id."' id='".$this->_id."' ROWS='5' COLS='40'>".$this->_value."</TEXTAREA>";
				break;
			case 'datetime':
				$val = HtmlHelper::FormatDateTime($this->_value);
				break;
			case 'date':
				$val = HtmlHelper::FormatDate($this->_value);
				break;
			case 'date_picker':
				$val = HtmlHelper::DatePicker($this->_id, $this->_id, $this->_value, $this->_options['default'], 'readonly '.$this->_ext);
				break;
			case 'time_picker':
				$val = HtmlHelper::TimePicker($this->_id, $this->_id, $this->_value, $this->_options['default'], 'readonly '.$this->_ext);
				break;
			case 'datetime_picker':
				$val = HtmlHelper::DatetimePicker($this->_id, $this->_id, $this->_value, $this->_options['default'], 'readonly '.$this->_ext);
				break;
			case 'datehour_picker':
				$val = HtmlHelper::DateHourPicker($this->_id, $this->_id, $this->_value, $this->_options['default'], 'readonly '.$this->_ext);
				break;
			case 'image':
				if($this->_value)
				{
				$target = $this->_options['target']===null ? '_blank' : $this->_options['target'];
				$val = HtmlHelper::Image($this->_value,$this->_options['link'],$target,
					$this->_options['width'],$this->_options['height'], $this->_id);
				}
				break;
			case 'confirmcode':
				$val = HtmlHelper::Input($this->_id,'','text').'<img src="'.urlhelper('misc','ConfirmCode').'">';
				break;
			case 'htmledit':
				$val = HtmlHelper::HtmlEditor($this->_id,$this->_value);
				break;
			default:$val="<!--$this->_id--> ".$this->_value;
		}
		return $val;
	}
}
