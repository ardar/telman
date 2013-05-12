<?php

require_once(FW_DIR."Libs/Smarty/Smarty.class.php");

class SmartyView implements IView
{
	private $_subcaching = false;
	private $_html_editor_tpl='_html_editor.htm';
	private $_setting;
	/**
	 * Smarty engine instance
	 * @var Smarty
	 */
	private $_smarty;
	/**
     * The class constructor.
     * Init a Smarty Template Class
     * Set Smarty Init value
     * set some user define function for smarty
     * @return void
     */
	public function __construct()
	{
		$this->_smarty = new Smarty();
	}
	
	public function Init($setting)
	{
		$this->_setting = $setting;
		$this->_smarty -> setTemplateDir( $this->_setting['template_dir']);
		$this->_smarty -> compile_dir = $this->_setting['compile_dir'];
		$this->_smarty -> config_dir = $this->_setting['config_dir'];
		$this->_smarty -> cache_dir = $this->_setting['cache_dir'];
		$this->_smarty -> left_delimiter = '<%';
		$this->_smarty -> right_delimiter = '%>';
		$this->_smarty -> compile_check = true;
		$this->_smarty -> _subcaching = ($this->_setting['cache_html']==1)? true:false;
		$this->_smarty -> caching = false;
		$this->_smarty -> cache_lifetime = intval($this->_setting['cache_html_lifetime']);
		$this->_smarty -> debugging = ($this->_setting['debug_on']>2) ? true : false; 
		$this->_smarty -> php_handling = 1;
		//SMARTY_PHP_PASSTHRU | SMARTY_PHP_QUOTE | SMARTY_PHP_REMOVE | SMARTY_PHP_ALLOW
		$this->_smarty -> security = false;
		$this->_smarty -> secure_dir = array ();
		
		$this->_smarty-> registerPlugin("modifier", "formatdate", array($this,"_formatdate"));
		$this->_smarty-> registerPlugin("modifier", "formatdatetime", array($this,"_formatdatetime"));
		
		$this->_smarty-> registerPlugin("function", "time_now", array($this,"_time_now"));
		$this->_smarty-> registerPlugin("function", "url_helper", array($this,"_urlhelper"));
		$this->_smarty-> registerPlugin("function", "data_table", array($this,"_data_table"));
		$this->_smarty-> registerPlugin("function", "data_form", array($this,"_data_form"));
		$this->_smarty-> registerPlugin("function", "html_editor", array($this,"_html_editor"));
	}
	
	public function Assign($field, $value)
	{
		$this->_smarty->assignByRef($field, $value);
	}
	
	public function Display($template, $cache_id=null, $params=null)
	{
		if($cache_id!=null && $this->_smarty->_subcaching)
			$this->_smarty->caching = true;
		return $this->_smarty->display($template,$cache_id,$cache_id);
	}
	
	public function GetOutput($template, $chacheid=null, $params=null)
	{
		return $this->_smarty->fetch($template,$chacheid, $chacheid, null, false);
	}
	
	public function CacheKey($key='')
	{
		global $_GET,$account;
		if ($key=='') {
			$key = serialize($_GET);
		}
		return md5($key);
	}
	
	public function TryCache($template,$key='')
	{
		if (!$this->_subcaching) {
			return false;
		}
		$this->caching=true;
		if ($key=='') {
			$key = $this->CacheKey();
		}
		if ($this->is_cached($template,$key)) {
			$this->Display($template,$key);
			return true;
		}
		else return false;
	}
	
	public function _formatdate($timestamp)
	{
		if(!$timestamp)return ;
		$format = AppHost::GetApp()->GetWebSite()->dateformat;
		$format = $format ? $format : 'Y年m月d日';
		return date($format,intval($timestamp));
	}
	
	public function _urlhelper($params)
	{
		return urlhelper($params['type'],$params['param1'],$params['param2'],$params['param3'], $params['param4'],$params['param5']);
	}
	
	public function _formatdatetime($timestamp)
	{
		if(!$timestamp)return ;
		$dateformat = AppHost::GetApp()->GetWebSite()->dateformat;
		$dateformat = $dateformat ? $dateformat : 'Y年m月d日';
		$timeformat = AppHost::GetApp()->GetWebSite()->timeformat;
		$timeformat = $timeformat ? $timeformat : 'H时i分s秒';
		return date("$dateformat $timeformat", intval($timestamp));
	}
	
	public function _time_now($params)
	{
		global $sys;
		$timestamp = $sys->RunTime['timestamp'];
		return date($sys->Config['dateformat'].' '.$sys->Config['timeformat'], intval($timestamp));
	}
	
	public function _data_form($params)
	{
		$form = $params['form'];
		$class = $params['class'];
		$style = $params['style'];
		$width = $params['width'] ? $params['width'] : '100%';
		$method = $params['method']?$params['method']:'post';
		$bgcolor = $params['bgcolor'] ? $params['bgcolor'] : '#e7e7e7';
		$rowcolor1 = $params['rowcolor1'] ? $params['rowcolor1'] : '';
		$rowcolor2 = $params['rowcolor2'] ? $params['rowcolor2'] : '#efefef';
		$result = "<table width='$width'  border=0 cellpadding=2 cellspacing=1 bgcolor='$bgcolor' class='$class'>";
		$result .= "<form id=myform action='' method='$method'  enctype='multipart/form-data'>\n";
		$result .= "<TR class=title ><TD class=tdbg colspan=2><B>".$form->GetTitle()."</B></TD></TR>";
		
		$fields = $form->GetFields();
		foreach ($fields as $field) {
			if ($field->GetType()=='hidden')
			{
				$hidden.="<input name='".$field->GetId()."' type='hidden' id='".$field->GetId()."' value='".$field->GetValue()."' >";
			}
			else
			{
				$result .= "<tr bgcolor=#ffffff><td width=20%>".$field->GetTitle()."</td><td>".$field->GetBody()."</td></tr>\n";				
			}
		}
		$result .= "<tr ><td height=30> </td><td>$hidden ";
		if($form->GetButtons()){
			$result .= $form->GetButtons();
		}
		else{
			$result .= "<input name='Submit' type='submit' class='border' value='提交' onclick=\"myform.submit();this.disabled=true;\">
			<input name='reset' type='reset' class='border' value='重填'>
			<input name='back' type='button' class='border' value='取消' onclick=\"window.history.back()\">";
		}
		$result .= "</td></tr>\n";
		$result .= "</form>";
		$result .= "</table>\n";
		return $result;
	}
	
	public function FormField 			
	(&$valiable,$field,$text='',$value='',$type='',$options=null,$optionfield='',$optiontext='',$nulltitle)
	{
		$valiable[] = array('text'=>$text,'field'=>$field,'value'=>$value,'type'=>$type,
		'options'=>$options,'optionfield'=>$optionfield,'optiontext'=>$optiontext,'nulltitle'=>$nulltitle);
	}
	
	public function _data_table($params)
	{
		$fieldcount = count($params['fields']);
		$class = $params['class'];
		$style = $params['style'];
		$width = $params['width'] ? $params['width'] : '100%';
		$bgcolor = $params['bgcolor'] ? $params['bgcolor'] : '#e7e7e7';
		$rowcolor1 = $params['rowcolor1'] ? $params['rowcolor1'] : '';
		$rowcolor2 = $params['rowcolor2'] ? $params['rowcolor2'] : '#efefef';
		$result = "<table width='$width'  border=0 cellpadding=2 cellspacing=1 bgcolor='$bgcolor' class='$class' $style>\n";
		$result .= "<TR><TD bgcolor=#ffffff class=ftitle colspan=$fieldcount>";
		$result .= "<table><form id=navform action='' method=GET><TR><TD align=left>";
		$result .= "<B>".$params['title']."</B>";
		$result .= "</TD><TD><div align=right>";
		$result .= $params['navbar'];
		$result .= "</div></TD></TR></form></table>";
		$result .= "</TD></TR>\n";
		$result .= "<form id=dataform name=dataform method=Post>\n";//print_r($params['data']);
		if ($params['toolbar']) {
			$result .= "<tr><td bgcolor=#ffffff colspan=$fieldcount>".$params['toolbar']."</td></tr>";
		}
		$result .= "<tr class=tdbg>\n";
		foreach ($params['fields'] as $field) 
		{
			switch ($field['type'])
			{
					case 'checkbox':
					$result .= "<td><div align='center'><INPUT id=chkAll onclick=CheckAll(this.form) 
type=checkbox value=checkbox name=chkAll> </div></td>\n";
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
					$result .= "<td width='".$field['width']."'><div align='center'>".$field['title']."</div></td>\n";
			}
		}
		$result .= "</tr>\n";
		foreach ($params['data'] as $rs) 
		{
			/*if ($i++%2==0) {
				$trbgcolor=$rowcolor1;
			}
			else $trbgcolor = $rowcolor2;*/
			$trbgcolor = '';
			$result .= "<tr bgcolor=#ffffff  onclick=\"if(this.style.background!='#efefef')this.style.background='#efefef';else {this.style.background='#ffffff';}\" onmouseover=\"if(this.style.background!='#efefef')this.style.background='#f7f7f7';\" onmouseout=\"if(this.style.background=='#f7f7f7') {this.style.background='#ffffff';}\" class=content bgcolor='$trbgcolor'>\n";
			foreach ($params['fields'] as $field)
			{				
				switch ($field['type'])
				{
					case 'checkbox':
					$val = "<input id=checkid onClick=unselectall() type=checkbox value='".$rs[$field['field']]."' name='".$field['field']."[]'>\n";
					$tdwidth =" width=30";
					break;
					case 'link':
					$val = "<a href='".$rs[$field['field']]."' >".$field['field']."</a>\n";
					break;
					case 'datetime':
					$val = $this->_formatdatetime($rs[$field['field']]);
					break;
					case 'date':
					$val = $this->_formatdate($rs[$field['field']]);
					break;
					case 'text':
					$val = HtmlHelper::Input($field['field'].'[]',$rs[$field['field']],'text',$field['width']);
					break;
					case 'select':
					$val = HtmlHelper::ListBox($field['field'].'[]',$rs[$field['field']],$field['ext']['options'],$field['ext']['optionfield'],$field['ext']['optiontext'],$field['ext']['nulltitle']);
					break;
					case 'hidden':						
					$hiddenval .= HtmlHelper::Input($field['field'].'[]',$rs[$field['field']],'hidden');
					//$fieldcount--;
					continue;
					break;
					default:
					$val = $rs[$field['field']];
				}
				$result .= $field['type']!='hidden' ? "<td bgcolor='".$field['bgcolor']."'><div align='".$field['align']."' ".$field['ext'].">$val</div></td>\n" :'';
			}
			$result .= "</tr>\n";
		}
		if ($params['footbar']) {
			$footbar = "<table align=left><tr bgcolor=#ffffff><td align=left>" .$params['footbar']."</TD></tr></table>";
		}
		$result .= "<TR bgcolor=#ffffff>"; 
		$result .="<TD colspan=".($fieldcount).">$footbar  $hiddenval</form>".$params['pager']."</TD></TR>";
		$result .= "";
		$result .= "</table>\n";
		return $result;
	}
	
	public function DataField(&$valiable,$field,$title='',$type='',$bgcolor='',$sorttype='',$ext='',$align='center')
	{
		$valiable[] = array('title'=>$title,'field'=>$field,'type'=>$type,'bgcolor'=>$bgcolor,'sorttype'=>$sorttype,'ext'=>$ext,'align'=>$align);
	}
	
	public function _html_editor($params)
	{
		return HtmlHelper::HtmlEditor($params['field'],$params['value']);
	}
}