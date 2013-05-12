<?php
class HtmlHelper
{
	const InputText = 'text';
	const InputTextArea = 'textarea';
	const InputSelect = 'select';
	const InputRadioList = 'radiolist';
	const InputCheckBox = 'check';
	const InputCalendar = 'calendar';
	const InputImage = 'imagefile';
	
	public static $InputTypes = array(
		self::InputText =>'单行文本框',
		self::InputTextArea =>'多行文本框',
		self::InputCheckBox =>'勾选框',
		self::InputRadioList =>'单选按钮列表',
		self::InputSelect =>'多选按钮列表',
		self::InputImage =>'图片上传控件',
		//self::InputCalendar =>'日历选择',
		);
	
	public static function Link($text,$url,$target='',$class='',$ext='')
	{
		$url = $url ? $url : $text;
		return "<a href=\"$url\" target='$target' class=\"$class\" $ext>$text</a>";
	}
	
	public static function ImageLink($image_url, $text,$url,$target='',$class='',$ext='')
	{
		$image = self::Image($image_url);
		$url = $url ? $url : $text;
		return "<a href='$url' target='$target' class='$class' $ext>$image $text</a>";
	}

	public static function Button($text,$type='submit',$extval='',$class_param='')
	{
		$class = $class_param ? $class_param : 'button';
		$value = $text;
		if ($type=='submit') {
			//$extval = $extval?"onclick=\"if(!confirm('$extval'))return false;\"" :'';
			return "<input name='submit' type='submit' $extval class='$class' value='$text'>";
			//return "<a class='button' href='javascript:' onclick='this.form.submit()' $extval>$text</a>";
		}
		elseif ($type=='link')
		{			
			$extval = "onclick=\"window.location.href='$extval';\"" ;
			return "<input type='button' $extval class='$class' value='$text'>";
			//return "<a class='button' href='javascript:' $extval>$text</a>";
		}
		else 
		{
			//$extval = "onclick=\"$extval;\"" ;
			return "<input type='button' $extval class='$class' title='$text' value='$value'>";
			//return "<a class='button' href='javascript:' $extval>$text</a>";
		}
	}
	
	public static function Image($src,$link='',$target='',$maxwidth=0,$maxheight=0,$img_id='', $ext='')
	{
		if ($maxwidth || $maxheight) 
		{
			$chgwidth =  $maxwidth?"if(this.width>$maxwidth)this.width=$maxwidth;":'';
			$chgheight =  $maxheight?"if(this.height>$maxheight)this.height=$maxheight;":'';
			$onload =  "onload=\"$chgwidth $chgheight\"";
			$ext .= " width=$maxwidth";
		}
		if (!$src) {
			return ' ';
		}
		$img =  "<img border=0 id='$img_id' src='$src' $onload $ext>";
		if ($link) 
		{
			$img = HtmlHelper::Link($img,$link,$target);
		}
		return $img;
	}
	
	public static function Input($field,$value,$type='text',$extval='')
	{
	    return self::IdInput($field, $field, $value, $type,$extval);
	}
	
	public static function IdInput($id, $field,$value,$type='text',$extval='')
	{
		switch ($type)
		{
			case 'requirefile':
				if ($value!='') 
				{
					$ret = '原文件:'.HtmlHelper::Link($value,$value,'_blank')."<BR/>\n";
				}
				return $ret."<input name='".$field."' type='file' class='inputbox' id='".$id."' value='' $extval>\n";
				break;	
			case 'file':
				if ($value!='') 
				{
					$ret = '删除原文件?'.HtmlHelper::Link($value,$value,'_blank').' '.
					HtmlHelper::IdInput("delete$id","delete$field",'1','checkbox')." <BR>\n";
				}
				return $ret."<input name='".$field."' type='file' class='inputbox' id='".$id."' value='' $extval>\n";
				break;	
			case 'imagefile':
				$ret = '';
				if ($value!='') 
				{
					$delbtn = "<img src='images/delete.png' style='cursor:hand' title='删除原图 ' onclick=\"$field.value='';".$field."_div.style.display='none'\">";
					$img = "<img src='$value' id='".$id."_img' width=100%>";
					$imglink = HtmlHelper::Link($img,$value,'_blank');
					$imgdiv = "<span style='overflow:hidden;width:60px;height:60px;'>$imglink</span>";
					$hidden = "<input type=hidden name='$field' id='$id' value='$value'>";
					$div = "<div id='".$id."_div'>$imgdiv $delbtn</div>";
					$ret = $div.$hidden."\n";
				}
				return $ret."<input name='".$field."_upload' type='file' class='inputbox' id='".$id."_upload' value='' $extval>\n";
				break;
			case 'check':
			case 'checkbox':
				return "<input name='".$field."' type='$type' class='inputbox' id='".$id."' value='".$value."' $extval>\n";
				break;
			case 'text_readonly':
				return "<input name='".$field."' type='$type'  class='inputbox' id='".$id."' value='".$value."' $extval readonly>\n";
				break;
			case 'text':
			case 'password':
			case 'radio':
			case 'hidden':
			default:
				return "<input name='".$field."' type='$type' class='inputbox' id='".$id."' value='".$value."' $extval>\n";
				break;
		}
	}
	
	public static function ListBox($field,$value,$options=array(),$optionfield='',$optiontext='',
		$ext_title=false, $ext = null)
	{
		$optionfield = $optionfield ? $optionfield : $field;
		$optiontext = $optiontext ? $optiontext : $optionfield;
		if (is_array($options) && !is_array(end($options))) 
		{
			foreach ($options as $key=>$optvalue)
			{
				$opts[] = array('key'=>$key,'value'=>$optvalue);
			}
			$options = $opts;
			$optionfield = 'key';
			$optiontext = 'value';
		}
		$val="<select name='".$field."' class='inputbox' id='".$field."' $ext>";
					if ($ext_title) 
					{
						if (is_array($ext_title))
						{
							foreach ($ext_title as $nullkey=>$nullname)
							{
								$val .="<option value='$nullkey'>$nullname</option>\n";
							}
						}
						else 
						{
							$val .="<option value=''>$ext_title</option>\n";
						}
					}
					if ($options)
					{
						foreach ($options as $option) 
						{
							$selected = '';
							if($option[$optionfield]==='' || $value==='') 
							{
								$selected = ($option[$optionfield] === $value) ? 'selected' : '';
							}
							else 
							{
								$selected = ($option[$optionfield]==$value) ? 'selected':'';
							}
							$disable_str = $option['disabled'] ? ' disabled=true ' : '';
							$val.="<option value='".$option[$optionfield]."' ".$disable_str
							.($selected).">".$option[$optiontext]."</option>\n";	
						}
					}
					$val.='</select>';
		return $val;
	}
	
	public static function RadioList($field,$value,&$options,$optionfield='',$optiontext='',$seperator=' ',$ext_title=null)
	{
		$optionfield = $optionfield ? $optionfield : $field;
		$optiontext = $optiontext ? $optiontext : $optionfield;
		if (is_array($options) && !is_array(end($options))) 
		{
			foreach ($options as $key=>$optvalue)
			{
				$opts[] = array('key'=>$key,'value'=>$optvalue);
			}
			$options = $opts;
			$optionfield = 'key';
			$optiontext = 'value';
		}
	    if ($ext_title) 
		{
			if (is_array($ext_title))
			{
				foreach ($ext_title as $nullkey=>$nullname)
				{
					$val.="<input name='".$field."' type='radio' id='".$field.$nullkey."' 
					value='".$nullkey."' ".( ($nullkey==$value) ? ' checked':'')."><label for='".$ext_title."'>".$nullname."</label>\n".$seperator;
		
				}
			}
			else 
			{
				$val.="<input name='".$field."' type='radio' id='".$field.$ext_title."' 
				value='' ".( ($ext_title==$value) ? ' checked':'').">
				<label for='".$ext_title."'>".$ext_title."</label>\n".$seperator;
		
			}
		}
		if ($options)
		{
			foreach ($options as $option) 
			{
			$val.="<input name='".$field."' type='radio' id='".$field.$option[$optionfield]."' value='".$option[$optionfield]."' ".( ($option[$optionfield]==$value) ? ' checked':'')."><label for='".$field.$option[$optionfield]."'>".$option[$optiontext]."</label>\n".$seperator;
			}
		}
		return $val;
	}
	
	public static function CheckList($field,&$values,&$options,$optionfield='',$optiontext='',$seperator=' ',$rowcount=-1)
	{
		$optionfield = $optionfield ? $optionfield : $field;
		$optiontext = $optiontext ? $optiontext : $optionfield; //print_r($value);var_dump($values);
		//echo count($values[0]);		
		//echo is_array($values);
		//echo $values[0];
		$valuearray = array();
		if (is_array($options)>0 && !is_array(end($options))) 
		{
			foreach ($options as $key=>$optvalue)
			{
				$opts[] = array('key'=>$key,'value'=>$optvalue);
			}
			$options = $opts;
			$optionfield = 'key';
			$optiontext = 'value';
		}
		if (!is_array($values) ) 
		{
			$valuearray[0] = $values;
		}
		elseif (is_array($values) && is_array(end($values)) )
		{
			foreach ($values as $value)
				$valuearray[] = $value[$field];
		}
		else $valuearray = $values;
		foreach ($options as $option) 
		{
		$val.="\n<label><input name='".$field."[]' type='checkbox' id='".$field."' value='".$option[$optionfield]."' ".( (in_array($option[$optionfield],$valuearray)) ? '  checked':'').">".$option[$optiontext]."</label>".$seperator;
		}
		return $val;
	}
	
	public static function HtmlEditor($field,$value)
	{
		require_once (PUBLIC_DIR."fckeditor/fckeditor.php");
		$oFCKeditor = new FCKeditor($field) ;
		$oFCKeditor->BasePath	= APP_URL.'fckeditor/' ;
		$oFCKeditor->Value		= $value ;
		return $oFCKeditor->CreateHtml() ;
	}
	
	public static function Pager($totalcount,$per_page=10,$page=1,$pagekey="page", $table_style="",$font_style="")
	{
		global $_GET;
		$page=$page?$page:$_GET['page'];
	    $pagecount=$totalcount==0? 0 : intval($totalcount/$per_page+(intval($totalcount%$per_page==0)?0:1));
	    $action = $_SERVER['PHP_SELF'];
		$url = "?";
		foreach ($_GET as $key => $value) {
			if (strtolower($key) =="page") continue;
			if (isset($url))
				$url.= "&".$key."=".$value;
			else
				$url=$action."?".$key."=".$value;
		}
	    $r.="<table " .$table_style . ">";        
	    $r.="<form method=get onsubmit=\"document.location = '" . $url . "&page='+ this.page.value;return false;\"><TR>\n";
	    $r.="<TD align=right>\n";
	    $r.=$font_style;    
	        
	    if ($page<=1 ){
	        $r.= " 首页 \n";       
	        $r.= " 上一页 \n";
		}
	    else {       
	        $r.=" <A HREF=\"". $url . "&page=1\">首页</A> \n";
	        $r.=" <A HREF=\"". $url ."&page=". ($page-1) ."\">上一页</A> \n";
	    }
	
	    if ($page>=$pagecount){
	        $r.= " 下一页 \n";
	        $r.= " 末页 \n"; 
			$page=$pagecount;
		}
	    else {
	        $r.= " <A HREF=\"". $url . "&page=". ($page+1) ."\">下一页</A> \n";
	        $r.= " <A HREF=\"". $url . "&page=". $pagecount ."\">末页</A> \n";            
	    }
	
	    $r.= " 第" . $page . "/" . $pagecount . "页\n";
	    $r.= " 共有" . intval($totalcount) . "条记录\n";
	    $r.= " 跳到第" . "<INPUT TYEP=TEXT NAME=page SIZE=1 Maxlength=5 VALUE=" . $page . ">" . "页 \n <INPUT type=submit style=\"font-size: 9pt\" value=GO >";
	    $r.= "</TD>";              
	    $r.= "</TR></form>";        
	    $r.= "</table>";
		return $r;
	}
	
	public static function FormatDateTime($timestamp)
	{
		if(!$timestamp)return '';
		return date(AppHost::GetApp()->GetWebSite()->dateformat.' '.AppHost::GetApp()->GetWebSite()->timeformat, intval($timestamp));
	}
	
	public static function FormatDate($timestamp)
	{
		if(!$timestamp)return '';
		return date(AppHost::GetApp()->GetWebSite()->dateformat,intval($timestamp));
	}
	
	static $HOURLIST = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23);
	
	public static function HourPicker($id, $field, $value, $default_value='', $input_ext=null)
	{
		if (!$value)
		{
			$value = $default_value;
		}
		
		return self::ListBox($id, $value, self::$HOURLIST,'','','',$input_ext);
	}
	
	public static function DatePicker($id,$fieldname, $value, $default_value='', $input_ext=null, $btn_ext=null)
	{
		if (!$value)
		{
			$value = $default_value;
		}
		if ($value)
		{
			if (is_numeric($value))
			{
				$time = $value;
			}
			else
			{
				$time = strtotime($value);
			}
			if($time>0)
			{
			    $value = date("Y-m-d", $time);
			}
			else 
			{
			    $value = '';
			}
		}
		
		$input = self::IdInput($id, $fieldname, $value, 'text', $input_ext);
		$btn = "<a style='cursor:hand;' onclick=\"CalendarDialog('$id','$value');$id.onchange();\" $btn_ext
		><img src='images/calendar.png' border=0 style='vertical-align:text-bottom' title='选择日期'></a>\n";
		return "<table cellpadding=0 cellspacing=0><tr><td>$input</td><td style='padding-left:2px;'>$btn</td></tr></table>";
		//return "<div nowrap>$input $btn</div>";
	}
	
	public function TimePicker($id, $fieldname, $value, $default_value='', $input_ext=null, $btn_ext=null,$showmin=true)
	{
		$hour = '';
		$min = '';
		if (!$value)
		{
			$value = $default_value;
		}
		if ($value)
		{
			if (is_numeric($value))
			{
				$time = $value;
			}
			else
			{
				$time = strtotime($value);
			}
			$hour = date("H", $time);
			$min = date("i", $time);
			$value = $hour.":".$min;
		}
		$houroptions = array();
		for ($i=0;$i<24;$i++)
		{
			$houroptions[$i] = $i;
		}
		$minoptions = array();
		for ($i=0;$i<60;$i++)
		{
			$minoptions[$i] = $i;
		}
		$hour_field_id = $id."_pickhour";
		$min_field_id = $id."_pickmin";
		$hour_field = "pickhour_".$fieldname;
		$min_field = "pickmin_".$fieldname;
		$onchange = " onchange=\"$id.value=($hour_field_id.value+':'+$min_field_id.value);$id.onchange();\"\n";
		$input = self::IdInput($id, $fieldname, $value, 'hidden', $input_ext);
		$input .= HtmlHelper::ListBox($hour_field_id, $hour, $houroptions,'','','',$onchange)."时";
		if ($showmin)
		{
			$input .= HtmlHelper::ListBox($min_field_id, $min, $minoptions,'','','',$onchange)."分";
		}
		else 
		{
			$input .= HtmlHelper::Input($min_field_id, $min, 'hidden');
		}
		return $input;
	}
	
	public static function DatetimePicker($id, $fieldname, $value, $default_value='', $input_ext=null, $btn_ext=null, $inputreadonly=true)
	{
		if (!$value)
		{
			$value = $default_value;
		}
		if ($value)
		{
			if (is_numeric($value))
			{
				$time = $value;
			}
			else
			{
				$time = strtotime($value);
			}
			if($time>0)
			{
			    $value = date("Y-m-d H:i", $time);
			}
			else
			{
			    $value='';
			}
		}
		
		$date_field_id = $id."_datepicker";
		$time_field_id = $id."_timepicker";
		$date_field = "datepicker_".$fieldname;
		$time_field = "timepicker_".$fieldname;
		$readonly = $inputreadonly ? "readonly":'';
		$onchg = "$readonly onchange=\"$id.value=($date_field_id.value+' '+$time_field_id.value);\"\n";
		
		$input = self::IdInput($id, $fieldname, $value, 'hidden', $input_ext);
		
		$datectrl = self::DatePicker($date_field_id, $date_field, $value, $default_value, $onchg, $btn_ext);
		$timectrl = self::TimePicker($time_field_id, $time_field, $value,$default_value, $onchg, $btn_ext);
		return "<table cellpadding=0 cellspacing=0><tr><td>$datectrl $input</td>
			<td style='padding-left:2px;'>$timectrl</td>
			</tr></table>";
		//return "<div nowrap>$input.$datectrl $timectrl</div>";
	}
	
	public static function DateHourPicker($id, $field, $value, $default_value='', $input_ext=null, $btn_ext=null, $inputreadonly=true)
	{
		if (!$value)
		{
			$value = $default_value;
		}
		if ($value)
		{
			if (is_numeric($value))
			{
				$time = $value;
			}
			else
			{
				$time = strtotime($value);
			}
			$value = date("Y-m-d H:i", $time);
		}
		
		$date_field_id = $id."_datepicker";
		$time_field_id = $id."_timepicker";
		$date_field = "datepicker_".$field;
		$time_field = "timepicker_".$field;
		$readonly = $inputreadonly ? "readonly":'';
		$onchg = "$readonly onchange=\"$id.value=($date_field_id.value+' '+$time_field_id.value);\"\n";
		
		$input = self::IdInput($id, $field, $value, 'hidden', $input_ext);
		
		$datectrl = self::DatePicker($date_field_id, $date_field, $value,$default_value, $onchg, $btn_ext);
		$timectrl = self::TimePicker($time_field_id, $time_field, $value,$default_value, $onchg, $btn_ext, false);
		return "<table cellpadding=0 cellspacing=0><tr><td>$datectrl $input</td>
			<td style='padding-left:2px;'>$timectrl</td>
			</tr></table>";
		//return "<div nowrap>$input.$datectrl $timectrl</div>";
	}
}