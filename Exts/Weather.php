<?php
class Weather
{
	const GENERAL_SOURCE_URL = "http://m.weather.com.cn/data/{id}.html";
	const CURRENT_SOURCE_URL = "http://www.weather.com.cn/data/sk/{id}.html";
	const GENERAL_CACHE_TIME = 86400;
	const CURRENT_CACHE_TIME = 3600;
	private $_placeId = 0;
	private $_generalInfo = null;
	private $_currentInfo = null;
	
	public function __construct($placeid)
	{
		$this->_placeId = $placeid;
	}
	
	public function GetGeneral()
	{
		$cache = new WeatherCache($this->_placeId);
		$datestr = date("Y年n月j日");
		if ($cache->GetData() && $cache->generalinfo
			&& time()-$cache->generalupdatetime < self::GENERAL_CACHE_TIME
			&& $datestr == $cache->date_y)
		{
			$this->_generalInfo = unserialize($cache->generalinfo);
			$cache->generalquerytime = time();
			$cache->generalquerycount ++;
			$cache->SaveData();
			return $this->_generalInfo;
		}
		$this->UpdateGeneral();
		return $this->_generalInfo;
	}
	
	public function GetCurrent()
	{
		$cache = new WeatherCache($this->_placeId);
		
		if ($cache->GetData() && $cache->currentinfo
			&& time()-$cache->currentupdatetime<self::CURRENT_CACHE_TIME)
		{
			$this->_currentInfo = unserialize($cache->generalinfo);
			$cache->currentquerytime = time();
			$cache->currentquerycount ++;
			$cache->SaveData();
			return $this->_currentInfo;
		}
		$this->UpdateCurrent();
		return $this->_currentInfo;
	}
	
	public function GetAll()
	{
		$cache = new WeatherCache($this->_placeId);
		$datestr = date("Y年n月j日");
		if ($cache->GetData() && $cache->generalinfo
			&& time()-$cache->generalupdatetime<self::GENERAL_CACHE_TIME)
		{
			$this->_generalInfo = unserialize($cache->generalinfo);
			if($datestr == $this->_generalInfo['date_y'])
			{
				$cache->generalquerytime = time();
				$cache->generalquerycount ++;
				$cache->SaveData();
			}
			else
			{
				$this->UpdateGeneral();
			}
		}
		else 
		{
			$this->UpdateGeneral();
		}
		if ($cache->GetData() && $cache->currentinfo
			&& time()-$cache->currentupdatetime<self::CURRENT_CACHE_TIME)
		{
			$this->_currentInfo = unserialize($cache->generalinfo);
			$cache->currentquerytime = time();
			$cache->currentquerycount ++;
			$cache->SaveData();
		}
		else 
		{
			$this->UpdateCurrent();
		}
		return array_merge($this->_generalInfo, $this->_currentInfo);
	}
	
	public function UpdateGeneral()
	{
		$url = str_replace('{id}', $this->_placeId, self::GENERAL_SOURCE_URL);
		//echo $url.'<br>';
		$handle = fopen($url,'r');
		if($handle)
		{
			$content = stream_get_contents($handle);
			if($content)
			{
				$this->_generalInfo = $this->ParseWeather($content);
				$cache = new WeatherCache(intval($this->_generalInfo['cityid']));
				
				$cache->cityid = $this->_generalInfo['cityid'];
				$cache->city = $this->_generalInfo['city'];
				$cache->city_en = $this->_generalInfo['city_en'];
				$cache -> generalupdatetime = time();
				$cache -> generalquerycount = 0;
				$cache -> generalquerytime = 0;
				$cache -> generalinfo = serialize($this->_generalInfo);
				$cache -> SaveData();
			}
		}
		return $this->_generalInfo;
	}
	
	public function UpdateCurrent()
	{
		$url = str_replace('{id}', $this->_placeId, self::CURRENT_SOURCE_URL);
		//echo $url.'<br>';
		$handle = fopen($url,'r');
		$this->_currentInfo = null;
		if($handle)
		{
			$content = stream_get_contents($handle);
			if($content)
			{
				$this->_currentInfo = $this->ParseWeather($content);
				$cache = new WeatherCache(intval($this->_currentInfo['cityid']));
				$cache -> cityid = intval($this->_currentInfo['cityid']);
				$cache -> currentupdatetime = time();
				$cache -> currentquerycount = 0;
				$cache -> currentquerytime = 0;
				$cache -> currentinfo = serialize($this->_currentInfo);
				$cache -> SaveData();
			}
		}
		return $this->_currentInfo;
	}
	
	public function ParseWeather($content)
	{
		$ret = array();
		$content = str_replace('{"weatherinfo":{', '', $content);
		$content = str_replace('}}', '', $content);
		$arr = explode(',', $content);
		foreach ($arr as $ar)
		{
			if(!trim($ar))
			{
				continue;
			}
			$items = explode(':',$ar);
			$key = str_replace('"','',$items[0]);
			$val = str_replace('"','',$items[1]);
			$ret[$key] = trim($val);
		}
		if($ret['date_y'])
		{
			$date = str_replace('年', '-', $ret['date_y']);
			$date = str_replace('月', '-', $date);
			$date = str_replace('日', '', $date);
			$arr = explode('-', $date);
			$year = $arr[0];
			$month = $arr[1];
			$day = $arr[2];
			$time = mktime(1,1,1, $month , $day, $year);
			$ret['date_2'] = date('n月j日', $time+86400*1);
			$ret['date_3'] = date('n月j日', $time+86400*2);
			$ret['date_4'] = date('n月j日', $time+86400*3);
			$ret['date_5'] = date('n月j日', $time+86400*4);
			
			$ret['week_2'] = "星期".self::$WeekStr[date('w', $time+86400*1)];
			$ret['week_3'] = "星期".self::$WeekStr[date('w', $time+86400*2)];
			$ret['week_4'] = "星期".self::$WeekStr[date('w', $time+86400*3)];
			$ret['week_5'] = "星期".self::$WeekStr[date('w', $time+86400*4)];
			$ret['date_lunar'] = self::GetLunar($arr[0],$arr[1],$arr[2]);
		}
		return $ret;
	}
	private static $WeekStr = array("日","一","二","三","四","五","六");
	private static $lnlunarcalendar = array
	(
		'tiangan'=>array("未知","甲","乙","丙","丁","戊","己","庚","辛","壬","癸"),
		'dizhi'=>array("未知","子年（鼠）","丑年（牛）","寅年（虎）","卯年（兔）","辰年（龙）",
		"巳年（蛇）","午年（马）","未年（羊）","申年（猴）","酉年（鸡）","戌年（狗）","亥年（猪）"),
		'month'=>array("闰","正","二","三","四","五","六",
		"七","八","九","十","十一","十二","月"),
		'day'=>array("未知","初一","初二","初三","初四","初五","初六","初七","初八","初九","初十",
		"十一","十二","十三","十四","十五","十六","十七","十八","十九","二十",
		"廿一","廿二","廿三","廿四","廿五","廿六","廿七","廿八","廿九","三十")
	);
	//农历每月的天数。每个元素为一年。每个元素中的数据为：[0]是闰月在哪个月，0为无闰月；[1]到[13]是每年12或13个月的每月天数；[14]是当年的天干次序，[15]是当年的地支次序
	private static $everymonth=array( 
		0=>array(8,0,0,0,0,0,0,0,0,0,0,0,29,30,7,1), 
		1=>array(0,29,30,29,29,30,29,30,29,30,30,30,29,0,8,2), 
		2=>array(0,30,29,30,29,29,30,29,30,29,30,30,30,0,9,3), 
		3=>array(5,29,30,29,30,29,29,30,29,29,30,30,29,30,10,4), 
		4=>array(0,30,30,29,30,29,29,30,29,29,30,30,29,0,1,5), 
		5=>array(0,30,30,29,30,30,29,29,30,29,30,29,30,0,2,6), 
		6=>array(4,29,30,30,29,30,29,30,29,30,29,30,29,30,3,7), 
		7=>array(0,29,30,29,30,29,30,30,29,30,29,30,29,0,4,8), 
		8=>array(0,30,29,29,30,30,29,30,29,30,30,29,30,0,5,9), 
		9=>array(2,29,30,29,29,30,29,30,29,30,30,30,29,30,6,10), 
		10=>array(0,29,30,29,29,30,29,30,29,30,30,30,29,0,7,11), 
		11=>array(6,30,29,30,29,29,30,29,29,30,30,29,30,30,8,12), 
		12=>array(0,30,29,30,29,29,30,29,29,30,30,29,30,0,9,1), 
		13=>array(0,30,30,29,30,29,29,30,29,29,30,29,30,0,10,2), 
		14=>array(5,30,30,29,30,29,30,29,30,29,30,29,29,30,1,3), 
		15=>array(0,30,29,30,30,29,30,29,30,29,30,29,30,0,2,4), 
		16=>array(0,29,30,29,30,29,30,30,29,30,29,30,29,0,3,5), 
		17=>array(2,30,29,29,30,29,30,30,29,30,30,29,30,29,4,6), 
		18=>array(0,30,29,29,30,29,30,29,30,30,29,30,30,0,5,7), 
		19=>array(7,29,30,29,29,30,29,29,30,30,29,30,30,30,6,8), 
		20=>array(0,29,30,29,29,30,29,29,30,30,29,30,30,0,7,9), 
		21=>array(0,30,29,30,29,29,30,29,29,30,29,30,30,0,8,10), 
		22=>array(5,30,29,30,30,29,29,30,29,29,30,29,30,30,9,11), 
		23=>array(0,29,30,30,29,30,29,30,29,29,30,29,30,0,10,12), 
		24=>array(0,29,30,30,29,30,30,29,30,29,30,29,29,0,1,1), 
		25=>array(4,30,29,30,29,30,30,29,30,30,29,30,29,30,2,2), 
		26=>array(0,29,29,30,29,30,29,30,30,29,30,30,29,0,3,3), 
		27=>array(0,30,29,29,30,29,30,29,30,29,30,30,30,0,4,4), 
		28=>array(2,29,30,29,29,30,29,29,30,29,30,30,30,30,5,5), 
		29=>array(0,29,30,29,29,30,29,29,30,29,30,30,30,0,6,6), 
		30=>array(6,29,30,30,29,29,30,29,29,30,29,30,30,29,7,7), 
		31=>array(0,30,30,29,30,29,30,29,29,30,29,30,29,0,8,8), 
		32=>array(0,30,30,30,29,30,29,30,29,29,30,29,30,0,9,9), 
		33=>array(5,29,30,30,29,30,30,29,30,29,30,29,29,30,10,10), 
		34=>array(0,29,30,29,30,30,29,30,29,30,30,29,30,0,1,11), 
		35=>array(0,29,29,30,29,30,29,30,30,29,30,30,29,0,2,12), 
		36=>array(3,30,29,29,30,29,29,30,30,29,30,30,30,29,3,1), 
		37=>array(0,30,29,29,30,29,29,30,29,30,30,30,29,0,4,2), 
		38=>array(7,30,30,29,29,30,29,29,30,29,30,30,29,30,5,3), 
		39=>array(0,30,30,29,29,30,29,29,30,29,30,29,30,0,6,4), 
		40=>array(0,30,30,29,30,29,30,29,29,30,29,30,29,0,7,5), 
		41=>array(6,30,30,29,30,30,29,30,29,29,30,29,30,29,8,6), 
		42=>array(0,30,29,30,30,29,30,29,30,29,30,29,30,0,9,7), 
		43=>array(0,29,30,29,30,29,30,30,29,30,29,30,29,0,10,8), 
		44=>array(4,30,29,30,29,30,29,30,29,30,30,29,30,30,1,9), 
		45=>array(0,29,29,30,29,29,30,29,30,30,30,29,30,0,2,10), 
		46=>array(0,30,29,29,30,29,29,30,29,30,30,29,30,0,3,11), 
		47=>array(2,30,30,29,29,30,29,29,30,29,30,29,30,30,4,12), 
		48=>array(0,30,29,30,29,30,29,29,30,29,30,29,30,0,5,1), 
		49=>array(7,30,29,30,30,29,30,29,29,30,29,30,29,30,6,2), 
		50=>array(0,29,30,30,29,30,30,29,29,30,29,30,29,0,7,3), 
		51=>array(0,30,29,30,30,29,30,29,30,29,30,29,30,0,8,4), 
		52=>array(5,29,30,29,30,29,30,29,30,30,29,30,29,30,9,5), 
		53=>array(0,29,30,29,29,30,30,29,30,30,29,30,29,0,10,6), 
		54=>array(0,30,29,30,29,29,30,29,30,30,29,30,30,0,1,7), 
		55=>array(3,29,30,29,30,29,29,30,29,30,29,30,30,30,2,8), 
		56=>array(0,29,30,29,30,29,29,30,29,30,29,30,30,0,3,9), 
		57=>array(8,30,29,30,29,30,29,29,30,29,30,29,30,29,4,10), 
		58=>array(0,30,30,30,29,30,29,29,30,29,30,29,30,0,5,11), 
		59=>array(0,29,30,30,29,30,29,30,29,30,29,30,29,0,6,12), 
		60=>array(6,30,29,30,29,30,30,29,30,29,30,29,30,29,7,1), 
		61=>array(0,30,29,30,29,30,29,30,30,29,30,29,30,0,8,2), 
		62=>array(0,29,30,29,29,30,29,30,30,29,30,30,29,0,9,3), 
		63=>array(4,30,29,30,29,29,30,29,30,29,30,30,30,29,10,4), 
		64=>array(0,30,29,30,29,29,30,29,30,29,30,30,30,0,1,5), 
		65=>array(0,29,30,29,30,29,29,30,29,29,30,30,29,0,2,6), 
		66=>array(3,30,30,30,29,30,29,29,30,29,29,30,30,29,3,7), 
		67=>array(0,30,30,29,30,30,29,29,30,29,30,29,30,0,4,8), 
		68=>array(7,29,30,29,30,30,29,30,29,30,29,30,29,30,5,9), 
		69=>array(0,29,30,29,30,29,30,30,29,30,29,30,29,0,6,10), 
		70=>array(0,30,29,29,30,29,30,30,29,30,30,29,30,0,7,11), 
		71=>array(5,29,30,29,29,30,29,30,29,30,30,30,29,30,8,12), 
		72=>array(0,29,30,29,29,30,29,30,29,30,30,29,30,0,9,1), 
		73=>array(0,30,29,30,29,29,30,29,29,30,30,29,30,0,10,2), 
		74=>array(4,30,30,29,30,29,29,30,29,29,30,30,29,30,1,3), 
		75=>array(0,30,30,29,30,29,29,30,29,29,30,29,30,0,2,4), 
		76=>array(8,30,30,29,30,29,30,29,30,29,29,30,29,30,3,5), 
		77=>array(0,30,29,30,30,29,30,29,30,29,30,29,29,0,4,6), 
		78=>array(0,30,29,30,30,29,30,30,29,30,29,30,29,0,5,7), 
		79=>array(6,30,29,29,30,29,30,30,29,30,30,29,30,29,6,8), 
		80=>array(0,30,29,29,30,29,30,29,30,30,29,30,30,0,7,9), 
		81=>array(0,29,30,29,29,30,29,29,30,30,29,30,30,0,8,10), 
		82=>array(4,30,29,30,29,29,30,29,29,30,29,30,30,30,9,11), 
		83=>array(0,30,29,30,29,29,30,29,29,30,29,30,30,0,10,12), 
		84=>array(10,30,29,30,30,29,29,30,29,29,30,29,30,30,1,1), 
		85=>array(0,29,30,30,29,30,29,30,29,29,30,29,30,0,2,2), 
		86=>array(0,29,30,30,29,30,30,29,30,29,30,29,29,0,3,3), 
		87=>array(6,30,29,30,29,30,30,29,30,30,29,30,29,29,4,4), 
		88=>array(0,30,29,30,29,30,29,30,30,29,30,30,29,0,5,5), 
		89=>array(0,30,29,29,30,29,29,30,30,29,30,30,30,0,6,6), 
		90=>array(5,29,30,29,29,30,29,29,30,29,30,30,30,30,7,7), 
		91=>array(0,29,30,29,29,30,29,29,30,29,30,30,30,0,8,8), 
		92=>array(0,29,30,30,29,29,30,29,29,30,29,30,30,0,9,9), 
		93=>array(3,29,30,30,29,30,29,30,29,29,30,29,30,29,10,10), 
		94=>array(0,30,30,30,29,30,29,30,29,29,30,29,30,0,1,11), 
		95=>array(8,29,30,30,29,30,29,30,30,29,29,30,29,30,2,12), 
		96=>array(0,29,30,29,30,30,29,30,29,30,30,29,29,0,3,1), 
		97=>array(0,30,29,30,29,30,29,30,30,29,30,30,29,0,4,2), 
		98=>array(5,30,29,29,30,29,29,30,30,29,30,30,29,30,5,3), 
		99=>array(0,30,29,29,30,29,29,30,29,30,30,30,29,0,6,4), 
		100=>array(0,30,30,29,29,30,29,29,30,29,30,30,29,0,7,5), 
		101=>array(4,30,30,29,30,29,30,29,29,30,29,30,29,30,8,6), 
		102=>array(0,30,30,29,30,29,30,29,29,30,29,30,29,0,9,7), 
		103=>array(0,30,30,29,30,30,29,30,29,29,30,29,30,0,10,8), 
		104=>array(2,29,30,29,30,30,29,30,29,30,29,30,29,30,1,9), 
		105=>array(0,29,30,29,30,29,30,30,29,30,29,30,29,0,2,10), 
		106=>array(7,30,29,30,29,30,29,30,29,30,30,29,30,30,3,11), 
		107=>array(0,29,29,30,29,29,30,29,30,30,30,29,30,0,4,12), 
		108=>array(0,30,29,29,30,29,29,30,29,30,30,29,30,0,5,1), 
		109=>array(5,30,30,29,29,30,29,29,30,29,30,29,30,30,6,2), 
		110=>array(0,30,29,30,29,30,29,29,30,29,30,29,30,0,7,3), 
		111=>array(0,30,29,30,30,29,30,29,29,30,29,30,29,0,8,4), 
		112=>array(4,30,29,30,30,29,30,29,30,29,30,29,30,29,9,5), 
		113=>array(0,30,29,30,29,30,30,29,30,29,30,29,30,0,10,6), 
		114=>array(9,29,30,29,30,29,30,29,30,30,29,30,29,30,1,7), 
		115=>array(0,29,30,29,29,30,29,30,30,30,29,30,29,0,2,8), 
		116=>array(0,30,29,30,29,29,30,29,30,30,29,30,30,0,3,9), 
		117=>array(6,29,30,29,30,29,29,30,29,30,29,30,30,30,4,10), 
		118=>array(0,29,30,29,30,29,29,30,29,30,29,30,30,0,5,11), 
		119=>array(0,30,29,30,29,30,29,29,30,29,29,30,30,0,6,12), 
		120=>array(4,29,30,30,30,29,30,29,29,30,29,30,29,30,7,1)
		);
	
	public static function GetLunar($year,$month,$day) 
	{		
		if ($year=="" || $month=="" || ($year< 1970 || $year > 2020))
		{
			return "未知农历:$year-$month-$day"; //超出这个范围不计算
		}
		
		//农历天干
		$mten = self::$lnlunarcalendar ['tiangan'];
		//农历地支
		$mtwelve = self::$lnlunarcalendar ['dizhi'];
		//农历月份
		$mmonth = self::$lnlunarcalendar ['month'];
		//农历日
		$mday = self::$lnlunarcalendar ['day'];
		//阳历总天数 至1900年12月21日
		$total = 69 * 365 + 17 + 11; //1970年1月1日前的就不算了
		//计算到所求日期阳历的总天数-自1900年12月21日始
		//先算年的和
		for ($y=1970; $y< $year;$y++)
		{
			$total += 365;
			if ($y % 4 == 0)
				$total ++;
		}
		//再加当年的几个月
		$total += gmdate ( "z", gmmktime ( 0, 0, 0, $month, 1, $year ) );
		//用农历的天数累加来判断是否超过阳历的天数
		$flag1 = 0; //判断跳出循环的条件
		$lcj = 0;
		while ($lcj<=120)
		{
			$lci = 1;
			while ($lci<=13)
			{
				$mtotal += self::$everymonth [$lcj] [$lci];
				if ($mtotal>=$total)
				{
					$flag1 = 1;
					break;
				}
				$lci ++;
			}
			if ($flag1 == 1)
				break;
			$lcj ++;
		}
		//由上，得到的 $lci 为当前农历月， $lcj 为当前农历年
		//计算所求月份1号的农历日期
		$fisrtdaylunar = self::$everymonth [$lcj] [$lci] - ($mtotal - $total);
		$results ['year'] = $mten [self::$everymonth [$lcj] [14]] . $mtwelve [self::$everymonth [$lcj] [15]]; //当前是什么年
		$daysthismonth = gmdate ( "t", gmmktime ( 0, 0, 0, $month, 1, $year ) ); //当前月共几天
		$op = 1;
		for ($i=1; $i<= $daysthismonth; $i++) 
		{
			$possiblelunarday = $fisrtdaylunar + $op - 1; //理论上叠加后的农历日
			if ($possiblelunarday<=self::$everymonth[$lcj][$lci]) 
			{
				//在本月的天数范畴内
				$results [$i] = $mday [$possiblelunarday];
				$op += 1;
			}
			else 
			{ //不在本月的天数范畴内
				$results [$i] = $mday [1]; //退回到1日
				$fisrtdaylunar = 1;
				$op = 2;
				$curmonthnum = (self::$everymonth [$lcj] [0] != 0) ? 13 : 12; //当年有几个月
				if ($lci+1>$curmonthnum) 
				{ //第13/14个月了，转到下一年
					$lci = 1;
					$lcj = $lcj + 1;
					//换年头了，把新一年的天干地支也写上
					$results ['year'] .= '/' . $mten [self::$everymonth [$lcj] [14]] . $mtwelve [self::$everymonth [$lcj] [15]];
				} 
				else 
				{ //还在这年里
					$lci = $lci + 1;
					$lcj = $lcj;
				}
			}
			//if ($results [$i] == $mday [1]) 
			{
				//每月的初一应该显示当月是什么月
				if (self::$everymonth [$lcj] [0] != 0) 
				{
					//有闰月的年
					$monthss=($lci>self::$everymonth[$lcj][0]) ? ($lci-1) : 
					$lci; //闰月后的月数-1
					if ($lci == self::$everymonth [$lcj] [0] + 1) { //这个月正好是闰月
						$monthssshow = $mmonth [0] . $mmonth [$monthss]; //前面加个闰字
						$runyue = 1;
					} 
					else 
					{
						$monthssshow = $mmonth [$monthss];
					}
				} 
				else 
				{
					$monthss = $lci;
					$monthssshow = $mmonth [$monthss];
				}
				//if ($monthss<= 10 && $runyue!=1) 
					$monthssshow .= $mmonth [13]; //只有1个字的月加上‘月’字
				$results [$i] = $monthssshow.$results [$i];
			}
		}
		return $results['year'].$results[$day];
	}
}