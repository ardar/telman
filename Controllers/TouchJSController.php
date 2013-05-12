<?php
class TouchJSController extends ControllerBase
{
	public function Init($app, $request, $view)
	{
		parent::Init($app, $request, $view);
	}
	
	public function Message($message)
	{
		echo $message;
		$this->GetApp()->AppExit();
	}
		
	public function desktopAction()
	{
		$totalcount = 0;
		$parentid = $this->GetRequest()->GetInput('id','int');
		echo TFCategory::GetCategoryListJS($parentid);
	}
		
	public function listAction()
	{
		$totalcount = 0;
		$categoryid = $this->GetRequest()->GetInput('id','int');
		echo TFCategory::GetEntityListJS($categoryid, $this->_offset, $this->_perpage, $totalcount);
	}
		
	public function infoAction()
	{
		$totalcount = 0;
		$entityid = $this->GetRequest()->GetInput('id','int');
		$entity = new TFEntity($entityid);
		if(!$entity->GetData())
		{
			$this->Message('没有找到信息');
		}
		$entity->AddHitCount();
		$content = "";
		$fieldrss = $entity->GetFieldList();
		foreach ($fieldrss as $field)
		{
			switch ($field['fieldtype'])
			{
				case 'image':
				case 'imagefile':
					$url = TFCategory::WebServerUrl.$field['fieldvalue'];
					$content.= "AddImg(\"".$url."\");\n";
					break;
				case 'tffile':
					$url = TFFile::FileUrl($field['fieldvalue']);
					$url = TFCategory::WebServerUrl.$url;
					$content.= "AddImg(\"".$url."\");\n";
					break;
				case 'string':
				case 'int':
				case 'number':
					$content.= "AddParam(\"".$field['fieldname']."\",\"".$field['fieldvalue']."\");\n";
			}
		}
		$entitycontent = str_replace('"', '\"', $entity->content); 
		//$content .= "alert('SetInfoContext');";
		
		$entity->content = str_replace("\r\n", ' ', $entity->content);
		$entity->content = str_replace("\r", ' ', $entity->content);
		$entity->content = str_replace("\n", ' ', $entity->content);
			
		$content.= "SetInfoContext(\"".$entity->content."\");";
//AddLeftList("价格","info.html?getinfo.php?id=123")
//AddLeftList("价格","info.html?getinfo.php?id=123")
//AddLeftList("价格","info.html?getinfo.php?id=123")
//SetLeftTitle("asdfas硪硪硪硪")
		$sql = "select * from tf_entity 
			where categoryid='".$entity->categoryid."' 
			and entityid<>'$entityid'
			order by toprange,entityid desc 
			limit 5";
		$rss = DBHelper::GetQueryRecords($sql);
		$content .= "SetLeftTitle(\"推荐信息\");\n";
		foreach ($rss as $rs)
		{
			$content .= "AddLeftList(\"".$rs['entityname']."\",\"info.html?info.php?id=".$rs['entityid']."\");\n";
		}
		echo $content;
	}
	
	public function weatherAction()
	{
		//101050501
		$placeid = AppHost::GetApp()->GetWebSite()->weathercityid;//$this->GetRequest()->GetInput('id','int','必须指定城市ID');
		$AdTitle = AppHost::GetApp()->GetWebSite()->weatherad;
		$weather = new Weather($placeid);
		$rs = $weather->GetAll();
		$hour =  intval(date('H'));
		$rs['img_now'] = ($$hour<18) ? 'd'.$rs['img1'] : 'n'.$rs['img2'];
		$this->GetView()->Assign('ad',$AdTitle);
		$this->GetView()->Assign('rs',$rs);
		$this->GetView()->Display('touch/weather.js.tpl');
	}
}
