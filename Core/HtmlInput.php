<?php

class HtmlInput extends HtmlElement
{
	const Text = "text";
	const TextReadonly = "text";
	const Password = "password";
	const File = "file";
	const CheckBox ="checkbox";
	const Radio = "radio";
	const Hidden = "hidden";
	
	protected $_type;
	protected $_value;
	protected $_params;
	protected $_style;
	protected $_extval;
	
	public function __construct($id, $title, $value, $type, $style='', $extval='')
	{
		
	}
	
	public function GetBody()
	{
		switch ($this->_type)
		{
			case self::File:
				if ($this->_value!='') 
				{
					$ret = HtmlHelper::Link($this->_value,$this->_value,'_blank').' '.
					(new HtmlInput("delete$this->_field",'1','checkbox'))." <BR>\n";
				}
				return $ret."<input name='".$this->_field."' type='file' class='border' id='".$this->_field."' value='' $this->_extval>";
				break;	
			case self::TextReadonly:
				return "<input name='".$this->_field."' type='$this->_type'  class='border' id='".$this->_field."' value='".$this->_value."' $this->_extval readonly>";
				break;
			case self::Text:
			case self::Password:
			case self::CheckBox:
			case self::Radio:
			case self::Hidden:
			default:
				return "<input name='".$this->_field."' type='$this->_type' class='border' id='".$this->_field."' value='".$this->_value."' $this->_extval>";
				break;
		}
	}
	
	public function __toString()
	{
		return $this->GetTitle()." ". $this->GetValue();
	}
}