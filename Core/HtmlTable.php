<?php
class HtmlTable extends HtmlElement
{
	private $_fields;
	private $_navbar;
	private $_headbar;
	private $_toolbar;
	private $_footer;
	private $_totalrowcount;
	private $_rows;
	private $_pager;
	private $_class;
	private $_params;
	private static $_table_count = 0;
	
	public function __construct($title, $id=null, $class='', $params=array())
	{
		$this->_title = $title;
		$this->_class = $class;
		$this->_id = $this->_name = $id ? $id : 'fwtable_'.(++self::$_table_count);
	}
	
	public function GetFields()
	{
		return $this->_fields;
	}
	
	public function GetData()
	{
		return $this->_rows;
	}
	
	public function GetNavbar()
	{
		return $this->_navbar;
	}
	
	public function GetToolbar()
	{
		return $this->_toolbar;
	}
	
	public function GetFooter()
	{
		return $this->_footer;
	}
	
	public function GetBody()
	{
		return $this->__toString();
	}
	
	public function BindToolbar($item)
	{
		$this->_toolbar .= $item;
		return $this;
	}
	
	public function BindHeadbar($item)
	{
		$this->_headbar .= $item;
		return $this;
	}
	
	public function BindNavbar($item)
	{
		$this->_navbar .= $item;
		return $this;
	}
	
	public function BindFootbar($item)
	{
		$this->_footer .= $item;
		return $this;
	}
	
	public function BindField($field,$title='',$type='',$style='',$sorttype='', $ext='',$align='center')
	{
		$this->_fields[] = array('title'=>$title,'field'=>$field,'type'=>$type,'style'=>$style,'sorttype'=>$sorttype,'align'=>$align,'ext'=>$ext);
		return $this;
	}
	
	public function BindData(&$rows)
	{
		$this->_rows = $rows ? $rows : array();
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
	
	public function BindPager($totalcount, $perpage=10, $currpage)
	{
		$this->_totalrowcount = $totalcount;
		$this->_pager = HtmlHelper::Pager($totalcount, $perpage, $currpage);
		return $this;
	}
	
	public function ParseTplValue()
	{
	    $table = array();
		$table['_class'] = $this->_params['class'] ? $this->_params['class'] : 'data_table';
		$table['_width'] = $this->_params['width'] ? $this->_params['width'] : '100%';
		$table['_bgcolor'] = $this->_params['bgcolor'] ? $this->_params['bgcolor'] : '#e7e7e7';
		$table['_rowcolor1'] = $this->_params['rowcolor1'] ? $this->_params['rowcolor1'] : '';
		$table['_rowcolor2'] = $this->_params['rowcolor2'] ? $this->_params['rowcolor2'] : '#efefef';
		
		$fieldcount = 0;
		$table['_fields'] = array();
		foreach ($this->_fields as $fieldkey=>$field) 
		{
			switch ($field['type'])
			{
			    case 'checkbool':
			    $field['title'] = "<label><INPUT id='chkAll_".$field['field']."' onclick=\"CheckAllBool(this.form,'".$field['field']."[]')\" type=checkbox value=".$field['field']." name='chkAll_".$field['field']."[]'>"
				. $field['title'] ."</label>" ;
				break;
				case 'checkbox':
				$field['title'] = "<INPUT id='chkAll_".$field['field']."' onclick=\"CheckAll(this.form,'".$field['field']."[]')\" type=checkbox value=".$field['field']." name='chkAll_".$field['field']."[]'>";
				break;
				case 'hidden':
					continue;
				default:
				if ($field['sorttype']=='asc'||$field['sorttype']=='desc') 
				{
					$sortfield='';
					foreach ($_GET as $key=>$val)
					{
						if ($key!='sortfield'&&$key!='sorttype') 
						{
							$sortfield .= "&$key=$val";
						}
						elseif ($key=='sorttype')
						{
						}
					}
					if ($_GET['sortfield']==$field['field']) 
					{							
						$field['sorttype'] = ($_GET['sorttype']=='desc')?'asc':'desc';
					}
					$sortfield = '?sortfield='.$field['field'].'&sorttype='
						.$field['sorttype'] .$sortfield;
					$field['title'] = HtmlHelper::Link($field['title'],$sortfield);
				}
			}
			$fieldcount ++;
			$table['_fields'][$fieldkey] = $field;
		}
		$hiddenval = '';
		foreach ($this->_rows as $rowkey=>$rs) 
		{
		    $table['_rows'][$rowkey]=array();
			foreach ($this->_fields as $fieldkey=>$field)
			{
				switch ($field['type'])
				{
					case 'hidden':
					$hiddenval .= HtmlHelper::IdInput($field['field'].$rowkey, $field['field'].'[]',$rs[$field['field']],'hidden');
					continue;
					case 'checkbox':
					$val = "<input id='check_".$field['field']."' 
    					onClick=\"unselectall(this.form,'".$field['field']."[]')\" 
    					type='checkbox' value='".$rs[$field['field']]."' 
    					name='".$field['field']."[]'>\n";
					break;
					case 'checkbool':
					$checkid = "checkhidden_".$field['field']."_".$rowkey;
					$val = "<input id='".$field['field']."' 
    					onClick=\"unselectall(this.form,'".$field['field']."[]');if(this.checked)$checkid.value=1;else $checkid.value=0;\" 
    					type='checkbox' value='1' ".($rs[$field['field']]?"checked":'')." 
    					name='checkbool_".$field['field']."[]'>\n";
				    $val .="<input type=hidden id='".$checkid."' 
				    	name='".$field['field']."[]' value='".($rs[$field['field']]?"1":'0')."'>\n";
					break;
					case 'link':
					$val = "<a href='".$rs[$field['field']]."' >".$field['field']."</a>\n";
					break;
					case 'datetime':
					$val = HtmlHelper::FormatDateTime($rs[$field['field']]);
					break;
					case 'duration':
					$val = OpenDev::GetDurationDesc($rs[$field['field']]);
					break;
					case 'date':
					$val = HtmlHelper::FormatDate($rs[$field['field']]);
					break;
        			case 'date_picker':
    				$val = HtmlHelper::DatePicker($field['field']."_".$rowkey, $field['field'].'[]', $rs[$field['field']], '', ' '.$field['ext']);
    				break;
        			case 'time_picker':
    				$val = HtmlHelper::TimePicker($field['field']."_".$rowkey,$field['field'].'[]', $rs[$field['field']], '', ' '.$field['ext']);
    				break;
        			case 'datetime_picker':
    				$val = HtmlHelper::DatetimePicker($field['field']."_".$rowkey,$field['field'].'[]', $rs[$field['field']], '', ' '.$field['ext']);
    				break;
        			case 'datehour_picker':
    				$val = HtmlHelper::DateHourPicker($field['field']."_".$rowkey,$field['field'].'[]', $rs[$field['field']], '', ' '.$field['ext']);
    				break;
					case 'text':
					$val = HtmlHelper::Input($field['field'].'[]',$rs[$field['field']],'text',$field['ext']);
					break;
					case 'select':
					$val = HtmlHelper::ListBox($field['field'].'[]',$rs[$field['field']],$field['ext']['options'],$field['ext']['optionfield'],$field['ext']['optiontext'],$field['ext']['nulltitle']);
					break;
					case 'image':
					$val = HtmlHelper::Image($rs[$field['field']],$field['ext']['link'],$field['ext']['target'],$field['ext']['width'],$field['ext']['height']);
					break;
					case 'bool':
					$val = $rs[$field['field']] ? "√" : "×";
					break;
					case 'cp':
						$val = "";
						if ($field['ext'])
						{
							foreach ($field['ext'] as $btn=>$params)
							{
								$controller = $params[0] ? $params[0] : $_REQUEST[FIELD_CONTROLLER];
								$url = urlhelper('action', $controller, $params[1], "&".$params[2]."=".$rs[$params[2]]);
								$val .= " ".HtmlHelper::Link($btn, $url, $params[3],'', $params[4]);
							}
						}
					break;
					default:
					$val = $rs[$field['field']];
					break;
				}
				$table['_rows'][$rowkey][$fieldkey] = $val;
			}
		}
		$table['_hidden'] = $hiddenval;
		$table['_fieldcount'] = $fieldcount;
		$table['_id'] = $this->_id;
		$table['_title'] = $this->_title;
		$table['_ext'] = $this->_ext;
		$table['_pager'] = $this->_pager;
		$table['_toolbar'] = $this->_toolbar;
		$table['_navbar'] = $this->_navbar;
		$table['_headbar'] = $this->_headbar;
		$table['_footer'] = $this->_footer;
		return $table;
	}
	
	public function __toString()
	{
		AppHost::GetApp()->GetView()->Assign('table', $this->ParseTplValue());
		return AppHost::GetApp()->GetView()->GetOutput('_table.htm');
		
	}
}