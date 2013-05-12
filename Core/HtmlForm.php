<?php

class HtmlForm extends HtmlElement
{
	private $_controller;
	private $_action;
	private $_postUrl;
	private $_fields;
	private $_buttons;
	private $_params;
	private static $_form_count = 0;
	private $_isReadOnly = false;
	
	public function __construct($form_title, $form_id=null, $params=array())
	{
		$this->_title = $form_title;
		$this->_name = $this->_id = $form_id ? $form_id : ('fwform_'.(++self::$_form_count));
		$this->_params = $params;
	}
	
	public function SetReadOnly( $isReadOnly= true)
	{
		$this->_isReadOnly = $isReadOnly;	
	}
	
	public function GetBody()
	{
		
	}
	
	public function GetPostUrl()
	{
		
	}
	
	public function BindParams($params)
	{
		$this->_params = array_merge($this->_params, $params);
		return $this;
	}
	
	public function BindParam($field, $value)
	{
		$this->_params[$field] = $value;
		return $this;
	}
	
	public function BindButton($button)
	{
		$this->_buttons .= $button;
		return $this;
	}
	
	public function GetButtons()
	{
		return $this->_buttons;
	}
	
	public function GetFields()
	{
		return $this->_fields;
	}
	
	public function BindField($id, $title, $value, $type='text', 
		$options=null, $optionfield='',$optiontext='',$nulltitle=null, $ext=null)
	{
		$this->_fields[] = new HtmlFormField($id, $title, $value, $type, 
			$options, $optionfield,$optiontext,$nulltitle,$ext);
		return $this;
	}
	
	public function ParseTplValue()
	{
	    $form = array();
		$form['_class'] = $this->_params['class']?$this->_params['class']:'data_form';
		$form['_ext'] = $this->_params['ext'];
		$form['_width'] = $this->_params['width'] ? $this->_params['width'] : '100%';
		$form['_method'] = $this->_params['method']?$this->_params['method']:'post';
		$form['_bgcolor'] = $this->_params['bgcolor'] ? $this->_params['bgcolor'] : '#e7e7e7';
		$form['_rowcolor1'] = $this->_params['rowcolor1'] ? $this->_params['rowcolor1'] : '';
		$form['_rowcolor2'] = $this->_params['rowcolor2'] ? $this->_params['rowcolor2'] : '#efefef';
		
		$form['_title'] = $this->GetTitle();
		$form['_id'] = $this->GetId();
		$form['_name'] = $this->GetName();
		$form['_fields'] = array();
		$form['_isreadonly'] = $this->_isReadOnly;
		foreach ($this->GetFields() as $fieldkey=>$field) 
		{
			if ($field->GetType()=='hidden')
			{
				$hidden.="<input name='".$field->GetId()."' type='hidden' id='".$field->GetId()."' value='".$field->GetValue()."' >\n";
			}
			else 
			{
    			$form['_fields'][$fieldkey]['type'] = $field->GetType();
    			$form['_fields'][$fieldkey]['title'] = $field->GetTitle();
    			$form['_fields'][$fieldkey]['body'] = $field->GetBody();
			}
		}
		$form['_hidden'] = $hidden;
		$form['_buttons'] = $this->GetButtons();
		
		return $form;
	}
	
	public function __toString()
	{
		AppHost::GetApp()->GetView()->Assign('form', $this->ParseTplValue());
		return AppHost::GetApp()->GetView()->GetOutput('_form.htm');
		
		//echo 'tostring';
		//print_r($this->_params);
		$class = $this->_params['class']?$this->_params['class']:'data_form';
		$style = $this->_params['style'];
		$width = $this->_params['width'] ? $this->_params['width'] : '100%';
		$method = $this->_params['method']?$this->_params['method']:'post';
		$bgcolor = $this->_params['bgcolor'] ? $this->_params['bgcolor'] : '#e7e7e7';
		$rowcolor1 = $this->_params['rowcolor1'] ? $this->_params['rowcolor1'] : '';
		$rowcolor2 = $this->_params['rowcolor2'] ? $this->_params['rowcolor2'] : '#efefef';
		$result = "<table width='$width'  border=0 cellpadding=2 cellspacing=1 bgcolor='$bgcolor' class='$class'>\n";
		$result .= "<form id='".$this->_id."' name='".$this->_name."' action='' method='$method'  enctype='multipart/form-data'>\n";
		$result .= "<TR ><TD colspan=2 class=tabletitle><B>".$this->GetTitle()."</B></TD></TR>\n";
		
		$fields = $this->GetFields();
		foreach ($fields as $field) {
			if ($field->GetType()=='hidden')
			{
				$hidden.="<input name='".$field->GetId()."' type='hidden' id='".$field->GetId()."' value='".$field->GetValue()."' >\n";
			}
			elseif ($field->GetType()=='html')
			{
				$result .= $field->GetBody();
			}
			else
			{
				$readonly = ($this->_isReadOnly) ? "Disabled=true" : '';
				$result .= "<tr class=fieldtr $readonly><td class=fieldtd>".$field->GetTitle()."</td>\n<td class=valuetd>".$field->GetBody()."</td></tr>\n";				
			}
		}
		$result .= "<tr ><td height=30> </td><td>$hidden ";
		if($this->GetButtons()){
			$result .= $this->GetButtons();
		}
		else{
			if($this->_isReadOnly)
			{
			$result .= "
			<input name='back' type='button' class='button' value='返回' onclick=\"window.history.back()\">\n";
			
			}
			else 
			{
			$result .= "
			<input name='Submit' type='submit' class='button' value='提交' onclick=\"this.form.submit();this.disabled=true;this.value='正在提交...'\">
			<input name='reset' type='reset' class='button' value='重填'>
			<input name='back' type='button' class='button' value='取消' onclick=\"window.history.back()\">\n";
			}
		}
		$result .= "</td></tr>\n";
		$result .= "</form>";
		$result .= "</table>\n";
		return $result;
	}
	
}