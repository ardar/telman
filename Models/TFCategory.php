<?php
class TFCategory extends DataObject
{	
	const TFCateIconDir = "img/";
	const TFCateDefaultIcon = "default.png";
	const TFCateIconWidth = 100;
	const TFCateIconHeight = 100;
	
	const TFCateNone = 'none';
		
	public static function GetTable()
	{
		return 'tf_category';
	}
	
	public static function GetPK()
	{
		return 'categoryid';
	}
	
	public static function GetObjName()
	{
		return "信息分类";
	}
	
	public function GetFieldList()
	{
		$filter_options[] = DBHelper::FilterOption(null, 'formatid', $this->formatid, '=');
		$filter_options[] = DBHelper::FilterOption(null, 'isshowinlist', 1, '=');
		$icount = 0;
		return DBHelper::GetJoinRecordSet(TFField::GetTable(), null, 
			$filter_options, array("fieldorder asc"),0,1000,$icount);
	}
	
	public static function IconUrl($categoryicon)
	{
		if (!$categoryicon)
		{
			return self::TFCateIconDir . self::TFCateDefaultIcon;
		}
		if (is_numeric($categoryicon))
		{
			$tf = new TFFile(intval($categoryicon));
			return $tf->GetFileUrl();
		}
		else
		{
			return self::TFCateIconDir.$categoryicon;
		}
	}
	
	public static function UploadIcon($input_field, Account $account)
	{
		$uploadfile = new UploadFile($input_field);
		if(!$uploadfile->HasValue())
		{
			return null;
		}
		if(!$uploadfile->Validate(UploadFile::CheckAllow, 'jpg|jpeg|gif|png', 0, 10240, true))
		{
			return null;
		}
		$object = new TFFile();
		$object->GetUpload($uploadfile);
		$object->uploaderid = $account->GetId();
		$object->uploadtime = time();
		$object->SaveData();
		return $object->GetId();
	}
	
	public static function BuildComboList($rootid=0)
	{
		$rss = DBHelper::GetRecords(TFCategory::GetTable(),'categoryisactive',1,'parentid,categoryorder');
		$list = array();
		self::_getListByParent($rootid, $rss, $list, "|");
		//print_r($list);
		return $list;
	}
	
	private static function _getListByParent($parentid, &$rss, &$list, $prefix)
	{
		foreach ($rss as $rs)
		{
			if ($rs['parentid']==$parentid)
			{
				if($rs['parentid']==0)
				{
					$rs['disabled'] = true;
				}
				$rs['title'] = $prefix.'－'.$rs['categoryname'];
				$list[] = $rs;
				self::_getListByParent($rs['categoryid'], $rss, $list, $prefix."　|");
			}
		}
	}
	
	public static function GetCategoryListJS($parentid)
	{
		$filter_options[] = DBHelper::FilterOption(null, 'parentid', $parentid, '=');
		$filter_options[] = DBHelper::FilterOption(null, 'categoryisactive', 1, '=');
		$icount = 0;
		$cates = DBHelper::GetJoinRecordSet(TFCategory::GetTable(), null, 
			$filter_options, array("categoryorder asc"),0,100,$icount);
		$content = "InitDesktopTable();\n";
		foreach ($cates as $rs)
		{
			$rs['categoryicon'] = TFCategory::IconUrl($rs['categoryicon']);
			if ($rs['parentid']==0)
			{
				$url = "list.html?desktop.php?id=".$rs['categoryid'];
			}
			else 
			{
				$url = "list.html?list.php?id=".$rs['categoryid'];
			}
			$content.="AddIcon(\"".$rs['categoryicon']."\",\"".$rs['categoryname']."\",\"$url\");\n";
		}
		return $content;
	}
	
	public static function ParseJSValue($value)
	{
		$value = str_replace('|', '｜', $value);
		$value = str_replace('"', '\"', $value);
		$value = str_replace("\r\n", ' ', $value);
		$value = str_replace("\r", ' ', $value);
		$value = str_replace("\n", ' ', $value);
		return $value;
	}
	
	public static function GetEntityListJS($categoryid,$offset,$perpage,&$totalcount)
	{
		$category = new TFCategory($categoryid);
		$fieldimgstr = '';
		$fieldstr = "str";
		$fieldlist = array();
		if ($category->GetData())
		{
			$fieldrss = $category->GetFieldList();
			foreach ($fieldrss as $field)
			{
				$fieldlist[$field['fieldid']] = $field['fieldtype'];
				$jsfieldtype = $field['fieldtype']=='imagefile' ? 'img' : 'str';
				if ($jsfieldtype=='img')
				{
					$fieldimgstr .= "img|";
				}
				else 
				{
					$fieldstr .= $fieldstr? ("|".$jsfieldtype) : $jsfieldtype.$field['fieldid'];
				}
			}
		}
		else
		{
			$fieldlist['desc'] = 'string';
			$fieldstr .= "|str";
		}
		$fieldstr = $fieldimgstr . $fieldstr;
		$content =  "InitTab(\"$fieldstr\");\n";
		
		$filter_options[] = DBHelper::FilterOption(null, 'categoryid', $categoryid, '=');
		$filter_options[] = DBHelper::FilterOption(null, 'entityisactive', 1, '=');
		$entitylist = DBHelper::GetJoinRecordSet(TFEntity::GetTable(), null, 
			$filter_options, array("toprange desc","entityid desc"),$offset,$perpage,$totalcount);
		$entityidlist = '-1';
		foreach ($entitylist as $rs)
		{
			$entityidlist .= $entityidlist ? (",".$rs['entityid']) : $rs['entityid'];
		}
		
		$sql = "select d.*,f.fieldname from ".TFFieldData::GetTable()." d 
			left join ".TFField::GetTable()." f on d.fieldid=f.fieldid
			where entityid in ($entityidlist) order by entityid, fieldorder";
		$fielddatas = DBHelper::GetQueryRecords($sql);
		$exrss = array();
		foreach ($fielddatas as $fielddata)
		{
			$exrss[$fielddata['entityid']][('field_'+$fielddata['fieldid'])] =
				array('name'=>$fielddata['fieldname'],'value'=> $fielddata['fieldvalue']);
		}
		
		foreach ($entitylist as $rs)
		{
			$rs['desc'] = OpenDev::sub_title(strip_tags($rs['content']), 100, 'utf-8', '..');
			$rs['desc'] = self::ParseJSValue($rs['desc']);
			$fieldvals = self::ParseJSValue($rs['entityname']);
			$fieldimgs = '';
			foreach ($fieldlist as $fieldid=>$fieldtype)
			{
				$fieldkey = "field_"+$fieldid;
				$value = self::ParseJSValue($exrss[$rs['entityid']][$fieldkey]['value']);
				if($fieldtype=='imagefile')
				{
					if($value)
					{
						$value = str_replace('action=getfile', 'action=thumbnail',$value);
					}
					else
					{
						$value = 'images/image.png';
					}
					$fieldimgs .= AppHost::GetAppSetting()->TouchWebServerAddr.$value.'|';
				}
				else
				{
					$name = $exrss[$rs['entityid']][$fieldkey]['name'];
					$value = $name.":".$value;
					$fieldvals .= $fieldvals ? '|'.$value : $value;
				}
				//echo "<BR> $fieldid . $fieldvals \n";
			}
			$fieldvals = $fieldimgs.$fieldvals;
			$content.="AddCell(\"".$fieldvals."\",\"info.html?info.php?id=".$rs['entityid']."\");\n";
		}
		return $content;
	}
}