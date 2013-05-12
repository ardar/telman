<?php
function TRACE($val)
{
	echo "\n<pre>\n";
	print_r($val);
	echo "\n</pre>\n";
}
class OpenDev 
{
	public static function create_thumbnail($imgbuf, $savefilename, $maxwidth, $maxheight=0)
	{
		$im = imagecreatefromstring($imgbuf);
		if ($im === false) 
		{
		    echo 'cannot create image from buffer.';
		    return false;
		}
		$width = imagesx($im);
		$height = imagesy($im);
		$newwidth = $width > $maxwidth ? $maxwidth : $width;
		$ratio = $newwidth / $width;
		$newheight = $height * $ratio;
		if ($maxheight>0 && $newheight> $maxheight)
		{
			$newheight = $maxheight;
			$ratio = $newheight / $height;
			$newwidth = $width * $ratio;
		}
		
		$newim = imagecreatetruecolor($newwidth, $newheight);
		
		imagecopyresized($newim,$im,0,0,0,0,$newwidth,$newheight,$width,$height);
		imagejpeg($newim, $savefilename);
		imagedestroy($newim);
		imagedestroy($im);
		return true;
	}
	
	public static function output_thumbnail($imgbuf, $maxwidth, $maxheight=0)
	{
		$im = imagecreatefromstring($imgbuf);
		if ($im === false) 
		{
		    echo 'cannot create image from buffer.';
		    return false;
		}
		header("Content-type: image/jpeg");
		$width = imagesx($im);
		$height = imagesy($im);
		$newwidth = $width > $maxwidth ? $maxwidth : $width;
		$ratio = $newwidth / $width;
		$newheight = $height * $ratio;
		if ($maxheight>0 && $newheight> $maxheight)
		{
			$newheight = $maxheight;
			$ratio = $newheight / $height;
			$newwidth = $width * $ratio;
		}
		
		$newim = imagecreatetruecolor($newwidth, $newheight);
		
		imagecopyresized($newim,$im,0,0,0,0,$newwidth,$newheight,$width,$height);
		imagejpeg($newim);
		imagedestroy($newim);
		imagedestroy($im);
		return true;
	}
	
	public static function thumbnail_old($filename,$thumbnailfile,$small_width=100,$small_height=0){
		if(!file_exists($filename))return false;
		
		if(file_exists($thumbnailfile)){
			header("location:$thumbnailfile");
		}
		else{
			$img=getimagesize($filename);
			if($img[2]==1)$image_big=imagecreatefromgif($filename);
			elseif($img[2]==2)$image_big=imagecreatefromjpeg($filename);
			elseif($img[2]==3)$image_big=imagecreatefrompng($filename);
			else header("location:$filename");
			$small_height=$small_height?$small_height:($small_width*($img[1]/$img[0]));
			$image_small=imagecreatetruecolor($small_width,$small_height);
			if(!$image_small) return false;
			imagecopyresized($image_small,$image_big,0,0,0,0,$small_width,$small_height,$img[0]-1,$img[1]-1);
			header("Content-type: image/png");
			imagepng($image_small,$thumbnailfile);
			imagepng($image_small);
			imagedestroy($image_small);
			imagedestroy($image_big);
		}
		return true;
	}
	
	public static function fetch_array_value($array,$fetchfield='',$fetchkey='')
	{
		if ($fetchkey) {
			
		}
		foreach ($array as $key => $val)
		{
			if ($fetchfield)
				$item = $val[$fetchfield];
			else 
				$item = $val;
			if ($fetchkey)
				$list[$val[$fetchkey]] = $item;
			else 
				$list[] = $item;
		}
		return $list;
	}
	
	public static function MacToBin($macstr)
	{
		return str_replace('-','',$macstr)."0000";
	}
	
	public static function BinToMac($h16binary)
	{
		$maclen =  strlen($h16binary);
		for ($i=0;$i<12;$i+=2)
		{
			$sub = substr($h16binary, $i, 2);
			$mac .= $mac ? ('-'.$sub) : $sub;
		}
		return strtoupper($mac);
	}
	
	public static function StrToTime($strtime)
	{
	    $datearr = explode('-', $strtime);
	    if(count($datearr)==2 && $datearr[0]<12 && $datearr[1]<31)
	    {
	        $datestr = date("Y")."-".$strtime;
	        return strtotime($datestr);
	    }
	    return strtotime($strtime);
	}
	
	public static function WebRGB($r, $g, $b)
	{
	    $rhex = dechex($r);
	    if(strlen($rhex)<2) $rhex = '0'.$rhex;
	    $ghex = dechex($g);
	    if(strlen($ghex)<2) $ghex = '0'.$ghex;
	    $bhex = dechex($b);
	    if(strlen($bhex)<2) $bhex = '0'.$bhex;
	    return '#'.$rhex.$ghex.$bhex; 
	}
	
	public static function checkdns($addr){
		if(!ereg ( "^[a-z0-9]+[a-z0-9\\.\\-]{2,30}[a-z0-9]+$", $addr ))
			return false;
		else return true;
	}
	
	public static function checkip($theip,$checkexcepted=true){
		global $Enum_Except_IPs;
		//echo 'ip:'.$theip;
		if(strpos($theip,',')>0){
			$iparray = explode(',',$theip);
			foreach ($iparray as $ip){
				$ip = OpenDev::checkip( trim($ip) );
				if ($ip) {
					return $ip;
				}
			}
			return $ip;
		}
		else $ip = trim($theip);
		return true;//TODO
		if (!ereg('^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$',$ip)) {
			return false;
		}
		$ips = explode('.',$ip);//print_r($ips);echo('--'.$ip.'--');
		if (!is_array($ips) || count($ips)!=4 )return false;
		if ( $ips[0]==='' || !$ips[1]==='' || !$ips[2]==='' || !$ips[3]==='')
			return false;
		for ($i=0;$i<4;$i++){
			if($i==1 || $i==2)
				$min = 0;
			else $min = 1;
			if (intval($ips[$i]) >255 || intval($ips[$i])<$min) {
				return false;
			}
		}
		if ($checkexcepted && is_array($Enum_Except_IPs)) {
			foreach ($Enum_Except_IPs as $exceptip){
				if ($exceptip!='' && ereg($exceptip,$ip)) {
					return false;
				}
			}
		}
		return $ip;
	}
	
	public static function checkipnet($theip,$checkexcepted=true){
		//echo 'ip:'.$theip;
		$ip = trim($theip);
		if (!ereg('^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$',$ip)) {
			return false;
		}
		$ips = explode('.',$ip);//print_r($ips);echo('--'.$ip.'--');
		if (!is_array($ips) || count($ips)!=4 )return false;
		if ( $ips[0]==='' || !$ips[1]==='' || !$ips[2]==='' || !$ips[3]==='')
			return false;
		for ($i=0;$i<4;$i++){
			if (intval($ips[$i]) >255 || intval($ips[$i])<0) {
				return false;
			}
		}
		return $ip;
	}
	
	public static function ip2long($ip)
	{
		$ips = explode('.',$ip);//print_r($ips);
		//if (!is_array($ips) || count($ips)!=4 )return false;
		$int = intval($ips[0])*16777216 
			+ intval($ips[1])*65536 
			+ intval($ips[2])*256 
			+ intval($ips[3]);
		return $int;
	}
	
	public static function long2ip($i,$padding=true){
		$ips = array();
		$ips[0] = intval($i/16777216);
		$i = $i-$ips[0]*16777216;
		$ips[1] = intval($i/65536);
		$i = $i-$ips[1]*65536;
		$ips[2] = intval($i/256);
		$i = $i-$ips[2]*256;
		$ips[3] = intval($i);
		if ($padding) {
			$ips[0] = str_pad($ips[0],3,'0',STR_PAD_LEFT);
			$ips[1] = str_pad($ips[1],3,'0',STR_PAD_LEFT);
			$ips[2] = str_pad($ips[2],3,'0',STR_PAD_LEFT);
			$ips[3] = str_pad($ips[3],3,'0',STR_PAD_LEFT);
		}
		return $ips[0].'.'.$ips[1].'.'.$ips[2].'.'.$ips[3];
	}
	
	public static function getRemoteIp($checkexcepted=true){
		global $HTTP_SERVER_VARS;
		if(getenv('HTTP_CLIENT_IP')&& OpenDev::checkip(getenv('HTTP_CLIENT_IP'),$checkexcepted) ) {
			return OpenDev::checkip(getenv('HTTP_CLIENT_IP'),$checkexcepted);
		} elseif(getenv('HTTP_X_FORWARDED_FOR') && OpenDev::checkip(getenv('HTTP_X_FORWARDED_FOR'),$checkexcepted)) {
			return OpenDev::checkip(getenv('HTTP_X_FORWARDED_FOR'),$checkexcepted);
		} elseif(getenv('REMOTE_ADDR') && OpenDev::checkip(getenv('REMOTE_ADDR'),$checkexcepted)) {
			return OpenDev::checkip(getenv('REMOTE_ADDR'),$checkexcepted);
		} elseif( OpenDev::checkip($HTTP_SERVER_VARS['REMOTE_ADDR'],$checkexcepted) ) {
			return OpenDev::checkip($HTTP_SERVER_VARS['REMOTE_ADDR'],$checkexcepted);
		} else {
			return $HTTP_SERVER_VARS['REMOTE_ADDR'];
		}
	}
	
	public static function checkdatestring($string)
	{
		return ereg('^[1-9]{1}[0-9]{3}-[0-9]{1,2}-[0-9]{1,2}$',$string);
	}
	
	public static function sendmail($to, $subject, $message, $from = '') 
	{
			extract($GLOBALS, EXTR_SKIP);
			error_reporting(E_CORE_ERROR);
	
			$subject = str_replace("\r", '', str_replace("\n", '', $subject));
			$message = str_replace("\r\n.", " \r\n..", str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $message)))));
	
	
			mail($to, $subject, $message, "From: $from");
	
			error_reporting(E_ERROR | E_WARNING | E_PARSE);
	}
	
	public static function iconv($in_charset='gb2312', $out_charset='utf8', $str='') {
		if(function_exists("iconv")) {
			if(is_array($str)) {
				foreach($str as $key => $val) {
					$str[$key] = OpenDev::iconv($in_charset, $out_charset, $val);
				}
			} else {
				$str = iconv($in_charset, $out_charset, $str);
			}
		}
		return $str;
	}
	
	
	public static function daddslashes($string, $force = 0) {
		if(!$GLOBALS['magic_quotes_gpc'] || $force) {
			if(is_array($string)) {
				foreach($string as $key => $val) {
					$string[$key] = OpenDev::daddslashes($val, $force);
				}
			} else {
				$string = addslashes($string);
			}
		}
		return $string;
	}
	
	public static function dstripslashes($string, $force = 0) {
		if(!$GLOBALS['magic_quotes_gpc'] || $force) {
			if(is_array($string)) {
				foreach($string as $key => $val) {
					$string[$key] = OpenDev::dstripslashes($val, $force);
				}
			} else {
				$string = stripslashes($string);
			}
		}
		return $string;
	}
	
	public static function txt_htmlspecialchars($t="")
	{
			// Use forward look up to only convert & not &#123;
			$t = preg_replace("/&(?!#[0-9]+;)/s", '&amp;', $t );
			$t = str_replace( "<", "&lt;"  , $t );
			$t = str_replace( ">", "&gt;"  , $t );
			$t = str_replace( '"', "&quot;", $t );
			
			return $t; // A nice cup of?
	}
	
	public static function ehtmlspecialchars($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = htmlspecialchars($val);
			}
		} else {
			//$string = str_replace("&lt;", "&amp;lt;", $string);
			//$string = str_replace("&gt;", "&amp;gt;", $string);
			$t = preg_replace("/&(?!#[0-9]+;)/s", '&amp;', $t );
			$string = str_replace('"', '&quot;', $string);
			$string = str_replace('<', '&lt;', $string);
			$string = str_replace('>', '&gt;', $string);
		}
		return $string;
	}
		
	public static function namequalified ($d) {
		$d=trim($d);
		$tmp=0;
		if(!ereg ( "^[A-Za-z]+[A-Za-z0-9]{2,20}$", $d ))
			return false;
		else return true;
	
		for ($asc=1;$asc<=47;$asc++){ 
			if (strrpos($d,chr($asc))!==false) $tmp=1; 
		}
		for ($asc=58;$asc<=64;$asc++){ 
			if (strrpos($d,chr($asc))!==false) $tmp=1; 
		}
		for ($asc=91;$asc<=94;$asc++){ 
			if (strrpos($d,chr($asc))!==false) $tmp=1; 
		}
		for ($asc=96;$asc<=96;$asc++){ 
			if (strrpos($d,chr($asc))!==false) $tmp=1; 
		}
		for ($asc=123;$asc<=127;$asc++){ 
			if (strrpos($d,chr($asc))!==false) $tmp=1; 
		}
		if ((strlen($d)>20) ||  (strlen($d)<3) ) $tmp=1; 
		if ($tmp==0) return 1;
		else return 0;
	}
	
	public static function pswqualified ($d) {
		$d=trim($d);
		$tmp=0;
		if (strrpos($d,"|")!==false || strrpos($d,"<")!==false || strrpos($d,">")!==false) $tmp=1;
		if ((strlen($d)>20)) $tmp=1; 
		if ($tmp==0) return 1;
		else return 0;
	}
	
	public static function check_email($email) {
	  return (preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $email)) ? 1 : 0;
	}
	
	public static function random($length,$casesence=0) {
		$hash = '';
		$chars = $casesence?'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789':'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$max = strlen($chars) - 1;
		mt_srand((double)microtime() * 1000000);
		for($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
		return $hash;
	}
	
	public static function script($common,$shutdown=1){
		echo  "<script language=javascript>" . $common . "</script>";	
		if ($shutdown) exit;
	}
		
	public static function short_date($ldate){
		$arr=explode("-",$ldate);
		$sdate=$arr[1]."/".$arr[2];
		return $sdate;
	
	}
	public static function substr($string, $length, $encoding  = 'utf-8') 
	{     
		$string = trim($string);       
		if($length && strlen($string) > $length) 
		{
			//截断字符         
			$wordscut = '';         
			if(strtolower($encoding) == 'utf-8') 
			{             
				//utf8编码             
				$n = 0;             
				$tn = 0;             
				$noc = 0;             
				while ($n < strlen($string)) 
				{                 
					$t = ord($string[$n]);                 
					if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) 
					{                     
						$tn = 1;                     
						$n++;                     
						$noc++;                 
					} 
					elseif(194 <= $t && $t <= 223) 
					{                     
						$tn = 2;                     
						$n += 2;                     
						$noc += 2;                 
					} 
					elseif(224 <= $t && $t < 239) 
					{                     
						$tn = 3;                     
						$n += 3;                     
						$noc += 2;                 
					} 
					elseif(240 <= $t && $t <= 247) 
					{                     
						$tn = 4;                     
						$n += 4;                     
						$noc += 2;                 
					} 
					elseif(248 <= $t && $t <= 251) 
					{                     
						$tn = 5;                     
						$n += 5;                     
						$noc += 2;                 
					} 
					elseif($t == 252 || $t == 253) 
					{                     
						$tn = 6;                     
						$n += 6;                     
						$noc += 2;                 
					} 
					else 
					{                     
						$n++;                 
					}                 
					if ($noc >= $length) 
					{                     
						break;                 
					}             
				}             
				if ($noc > $length) 
				{                 
					$n -= $tn;             
				}             
				$wordscut = substr($string, 0, $n);         
			} 
			else 
			{             
				for($i = 0; $i < $length - 1; $i++) 
				{                 
					if(ord($string[$i]) > 127) 
					{                     
						$wordscut .= $string[$i].$string[$i + 1];                     
						$i++;                 
					}
					else 
					{                     
						$wordscut .= $string[$i];                 
					}             
				}         
			}         
			$string = $wordscut;     
		}
		return trim($string); 
	} 
	
	public static function sub_title($str,$length=0,$encoding='gb2312',$sufix='..')
	{
		$len=strlen($str);
		if($length>0)
		{
			$sub = self::substr($str, $length, $encoding);
			if(strlen($sub)<strlen($str))
			{
				$str = $sub.$sufix;
			}
		}
		return $str;
	}
	
	public static function swap_title($str,$num){
					$len=strlen($str);
					//if($len>($num*2)) $right="..";
					$i=0;
					for($n=0;$i<$len;$i++){
						$_int=ord(substr($str,$i,1));
						if($_int>127) $i++;
						if (($i!=0)&&(($i%$num)==0)) {$str= substr($str,0,$i)."-".substr($str,$i,$len);$len++;$i++;}
					}
					return $str;
	
	}
	
	public static function GetWeekday($year, $month, $day)
	{
		$time = mktime(12,0,0, $month, $day, $year);
		return date('w',$time);
	}
	
	public static function GetWeekday2($date_str)
	{
		$time = strtotime($date_str);
		return date('w',$time);
	}
	
	public static function GetDaysOfMonth($year, $month)
	{
		$time = mktime(12,0,0, $month, 1, $year);
		return date('t',$time);
	}
	
	public static function GetDurationDesc($durationtime)
	{
		$lefttimes = $durationtime;
		$result = "";
		if ($lefttimes>=3600)
		{
			$num = intval($lefttimes / 3600);
			$lefttimes = $lefttimes - 3600*$num;
			$result .= $num."小时";
		}
		if ($lefttimes>=60)
		{
			$num = intval($lefttimes / 60);
			$lefttimes = $lefttimes - 60*$num;
			$result .= $num."分";
		}
		$num = intval($lefttimes);
		$result .= $num."秒";
		return $result;
	}
		
	public static function GetFullFileSizeDesc($size_in_byte, $min_unit='b')
	{
		$isNegative = false;
		if ($size_in_byte < 0) 
		{
			$isNegative = true;
			$size_in_byte = - $size_in_byte;
		}
		$remainbyte = $size_in_byte;
		if (bccomp ( $remainbyte, 1024*1024*1024 ) >= 0) 
		{
			$result .= bcdiv ( $remainbyte, 1024*1024*1024, 0 ) . 'G';
			$remainbyte = bcmod ( $remainbyte, 1024*1024*1024 );
		}
		if (bccomp ( $remainbyte, 1024*1024 ) >= 0) 
		{
			$result .= bcdiv ( $remainbyte, 1024*1024, 0 ) . 'M';
			$remainbyte = bcmod ( $remainbyte, 1024*1024 );
		}
		if (bccomp ( $remainbyte, 1024 ) >= 0) 
		{
			$result .= bcdiv ( $remainbyte, 1024, 0 ) . 'k';
			$remainbyte = bcmod ( $remainbyte, 1024 );
		}
		if ($min_unit=='b' && bccomp ( $remainbyte, 1 ) >= 0) 
		{
			$result .= $remainbyte . 'b';
		}
		if ($isNegative) {
			$result = "-" . $result;
		}
		return $result;
	}
		
	public static function GetFileSizeDesc($size_in_byte, $min_unit='m')
	{
		$min_unit = strtolower($min_unit);
		$isNegative = false;
		if ($size_in_byte < 0) 
		{
			$isNegative = true;
			$size_in_byte = - $size_in_byte;
		}
		$remainbyte = $size_in_byte;
		if (bccomp ( $remainbyte, 1024*1024*1024 ) >= 0) 
		{
			$result .= bcdiv ( $remainbyte, 1024*1024*1024, 0 ) . 'G';
			$remainbyte = bcmod ( $remainbyte, 1024*1024*1024 );
		}
		if (bccomp ( $remainbyte, 1024*1024 ) >= 0) 
		{
			if($min_unit=='m')
			{
				$result .= bcdiv ( $remainbyte, 1024*1024, 3 ) . 'M';
				$remainbyte = 0;
			}
			else
			{
				$result .= bcdiv ( $remainbyte, 1024*1024, 0 ) . 'M';
				$remainbyte = bcmod ( $remainbyte, 1024*1024 );
			}
		}
		if (bccomp ( $remainbyte, 1024 ) >= 0) 
		{
			if($min_unit=='k' || $min_unit=='m')
			{
				$result .= bcdiv ( $remainbyte, 1024, 1 ) . 'k';
				$remainbyte = 0;
			}
			else
			{
				$result .= bcdiv ( $remainbyte, 1024, 0 ) . 'k';
				$remainbyte = bcmod ( $remainbyte, 1024 );
			}
		}
		elseif ($min_unit=='b' && bccomp ( $remainbyte, 1 ) >= 0) 
		{
			$result .= $remainbyte . 'b';
		}
		if ($isNegative) {
			$result = "-" . $result;
		}
		return $result;
	}
	
	public static function OutputImage($mime_type, $image)
	{
		$mime_type = $mime_type ? $mime_type : 'image/bmp';
		ob_clean();
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-type: $mime_type");
		echo $image;
		exit();
	}
	
	public static function uploadfile($up_file,$dpath,$rename_file){
				$savefile=($dpath.$rename_file);
				if (function_exists('move_uploaded_file'))
					$saved=@move_uploaded_file($up_file, $savefile);
				else{
					if(is_uploaded_file($up_file))
						$saved=@copy($up_file, $savefile);
					else
						$saved=false;
				}
	}
	
	public static function deletefile($path)
	{
		if (file_exists($path))
		{
			return unlink($path);
		}
	}
	
	public static function deletedir($path,$delchild=false,$delthis=false)
	{
		if (!is_dir($path) ) {
			return false;
		}
		$d = dir($path);
		$entry = @$d->read();
		while ($entry) 
		{
			if ($entry=='.' || $entry=='..') {
				continue;
			}
			if (is_file($path.'/'.$entry)) {
				@unlink($path.'/'.$entry);
			}
			elseif(is_dir($path.'/'.$entry) && $delchild){
				OpenDev::deletedir($path.'/'.$entry,$delchild);
				@rmdir($path.'/'.$entry);
			}
			$entry=@$d->read();
		}
		if ($delthis) {
			@rmdir($path);
		}
	}
	
	public static function readfromfile($file_name) {
		if (file_exists($file_name)) {
			$filenum=fopen($file_name,"r");
			//@flock($filenum,LOCK_SH);
			$file_data=fread($filenum,@filesize($file_name));
			fclose($filenum);
			$GLOBALS['readfilenum']++;
			return $file_data;
		}
		else return false;
	}
	
	public static function writetofile($file_name,$data,$len=null,$method="w+") {
		$handle=fopen($file_name,$method);
		flock($handle, LOCK_EX);
		if ($len)
			$file_data=fwrite($handle, $data);
		else 
			$file_data =fwrite($handle, $data);
		fclose($handle);
		return $file_data;
	}

	public static function GenConfirmCode($b=4,$minite=30)
	{
		$cookietime=time()+60*$minite;//验证码有效时间
		$b = 4;
		for($base=1;$b>1;$b--)
			$base=$base*10;
		$code=mt_rand($base,$base*10-1);
		$md5code=md5($code);//print_r($sys->Config);
		$prefix = "_PERCON_";
		setcookie($prefix."_CONFIRM", $md5code, $cookietime, APP_URL, '');
		return $code;
	}
	
	public static function CheckConfirmCode($inputcode)
	{
		$prefix = "_PERCON_";
		$cookiecode = $_COOKIE[$prefix."_CONFIRM"];
		if (md5($inputcode)==$cookiecode)
		{
			return true;
		}
		return false;
	}

	public static function GetPinyinByChar($num)
	{  
		$ddddd=array(    
		array("a",-20319),    
		array("ai",-20317),    
		array("an",-20304),    
		array("ang",-20295),    
		array("ao",-20292),    
		array("ba",-20283),    
		array("bai",-20265),    
		array("ban",-20257),    
		array("bang",-20242),    
		array("bao",-20230),    
		array("bei",-20051),    
		array("ben",-20036),    
		array("beng",-20032),    
		array("bi",-20026),    
		array("bian",-20002),    
		array("biao",-19990),    
		array("bie",-19986),    
		array("bin",-19982),    
		array("bing",-19976),    
		array("bo",-19805),    
		array("bu",-19784),    
		array("ca",-19775),    
		array("cai",-19774),    
		array("can",-19763),    
		array("cang",-19756),    
		array("cao",-19751),    
		array("ce",-19746),    
		array("ceng",-19741),    
		array("cha",-19739),    
		array("chai",-19728),    
		array("chan",-19725),    
		array("chang",-19715),    
		array("chao",-19540),    
		array("che",-19531),    
		array("chen",-19525),    
		array("cheng",-19515),    
		array("chi",-19500),    
		array("chong",-19484),    
		array("chou",-19479),    
		array("chu",-19467),    
		array("chuai",-19289),    
		array("chuan",-19288),    
		array("chuang",-19281),    
		array("chui",-19275),    
		array("chun",-19270),    
		array("chuo",-19263),    
		array("ci",-19261),    
		array("cong",-19249),    
		array("cou",-19243),    
		array("cu",-19242),    
		array("cuan",-19238),    
		array("cui",-19235),    
		array("cun",-19227),    
		array("cuo",-19224),    
		array("da",-19218),    
		array("dai",-19212),    
		array("dan",-19038),    
		array("dang",-19023),    
		array("dao",-19018),    
		array("de",-19006),    
		array("deng",-19003),    
		array("di",-18996),    
		array("dian",-18977),    
		array("diao",-18961),    
		array("die",-18952),    
		array("ding",-18783),    
		array("diu",-18774),    
		array("dong",-18773),    
		array("dou",-18763),    
		array("du",-18756),    
		array("duan",-18741),    
		array("dui",-18735),    
		array("dun",-18731),    
		array("duo",-18722),    
		array("e",-18710),    
		array("en",-18697),    
		array("er",-18696),    
		array("fa",-18526),    
		array("fan",-18518),    
		array("fang",-18501),    
		array("fei",-18490),    
		array("fen",-18478),    
		array("feng",-18463),    
		array("fo",-18448),    
		array("fou",-18447),    
		array("fu",-18446),    
		array("ga",-18239),    
		array("gai",-18237),    
		array("gan",-18231),    
		array("gang",-18220),    
		array("gao",-18211),    
		array("ge",-18201),    
		array("gei",-18184),    
		array("gen",-18183),    
		array("geng",-18181),    
		array("gong",-18012),    
		array("gou",-17997),    
		array("gu",-17988),    
		array("gua",-17970),    
		array("guai",-17964),    
		array("guan",-17961),    
		array("guang",-17950),    
		array("gui",-17947),    
		array("gun",-17931),    
		array("guo",-17928),    
		array("ha",-17922),    
		array("hai",-17759),    
		array("han",-17752),    
		array("hang",-17733),    
		array("hao",-17730),    
		array("he",-17721),    
		array("hei",-17703),    
		array("hen",-17701),    
		array("heng",-17697),    
		array("hong",-17692),    
		array("hou",-17683),    
		array("hu",-17676),    
		array("hua",-17496),    
		array("huai",-17487),    
		array("huan",-17482),    
		array("huang",-17468),    
		array("hui",-17454),    
		array("hun",-17433),    
		array("huo",-17427),    
		array("ji",-17417),    
		array("jia",-17202),    
		array("jian",-17185),    
		array("jiang",-16983),    
		array("jiao",-16970),    
		array("jie",-16942),    
		array("jin",-16915),    
		array("jing",-16733),    
		array("jiong",-16708),    
		array("jiu",-16706),    
		array("ju",-16689),    
		array("juan",-16664),    
		array("jue",-16657),    
		array("jun",-16647),    
		array("ka",-16474),    
		array("kai",-16470),    
		array("kan",-16465),    
		array("kang",-16459),    
		array("kao",-16452),    
		array("ke",-16448),    
		array("ken",-16433),    
		array("keng",-16429),    
		array("kong",-16427),    
		array("kou",-16423),    
		array("ku",-16419),    
		array("kua",-16412),    
		array("kuai",-16407),    
		array("kuan",-16403),    
		array("kuang",-16401),    
		array("kui",-16393),    
		array("kun",-16220),    
		array("kuo",-16216),    
		array("la",-16212),    
		array("lai",-16205),    
		array("lan",-16202),    
		array("lang",-16187),    
		array("lao",-16180),    
		array("le",-16171),    
		array("lei",-16169),    
		array("leng",-16158),    
		array("li",-16155),    
		array("lia",-15959),    
		array("lian",-15958),    
		array("liang",-15944),    
		array("liao",-15933),    
		array("lie",-15920),    
		array("lin",-15915),    
		array("ling",-15903),    
		array("liu",-15889),    
		array("long",-15878),    
		array("lou",-15707),    
		array("lu",-15701),    
		array("lv",-15681),    
		array("luan",-15667),    
		array("lue",-15661),    
		array("lun",-15659),    
		array("luo",-15652),    
		array("ma",-15640),    
		array("mai",-15631),    
		array("man",-15625),    
		array("mang",-15454),    
		array("mao",-15448),    
		array("me",-15436),    
		array("mei",-15435),    
		array("men",-15419),    
		array("meng",-15416),    
		array("mi",-15408),    
		array("mian",-15394),    
		array("miao",-15385),    
		array("mie",-15377),    
		array("min",-15375),    
		array("ming",-15369),    
		array("miu",-15363),    
		array("mo",-15362),    
		array("mou",-15183),    
		array("mu",-15180),    
		array("na",-15165),    
		array("nai",-15158),    
		array("nan",-15153),    
		array("nang",-15150),    
		array("nao",-15149),    
		array("ne",-15144),    
		array("nei",-15143),    
		array("nen",-15141),    
		array("neng",-15140),    
		array("ni",-15139),    
		array("nian",-15128),    
		array("niang",-15121),    
		array("niao",-15119),    
		array("nie",-15117),    
		array("nin",-15110),    
		array("ning",-15109),    
		array("niu",-14941),    
		array("nong",-14937),    
		array("nu",-14933),    
		array("nv",-14930),    
		array("nuan",-14929),    
		array("nue",-14928),    
		array("nuo",-14926),    
		array("o",-14922),    
		array("ou",-14921),    
		array("pa",-14914),    
		array("pai",-14908),    
		array("pan",-14902),    
		array("pang",-14894),    
		array("pao",-14889),    
		array("pei",-14882),    
		array("pen",-14873),    
		array("peng",-14871),    
		array("pi",-14857),    
		array("pian",-14678),    
		array("piao",-14674),    
		array("pie",-14670),    
		array("pin",-14668),    
		array("ping",-14663),    
		array("po",-14654),    
		array("pu",-14645),    
		array("qi",-14630),    
		array("qia",-14594),    
		array("qian",-14429),    
		array("qiang",-14407),    
		array("qiao",-14399),    
		array("qie",-14384),    
		array("qin",-14379),    
		array("qing",-14368),    
		array("qiong",-14355),    
		array("qiu",-14353),    
		array("qu",-14345),    
		array("quan",-14170),    
		array("que",-14159),    
		array("qun",-14151),    
		array("ran",-14149),    
		array("rang",-14145),    
		array("rao",-14140),    
		array("re",-14137),    
		array("ren",-14135),    
		array("reng",-14125),    
		array("ri",-14123),    
		array("rong",-14122),    
		array("rou",-14112),    
		array("ru",-14109),    
		array("ruan",-14099),    
		array("rui",-14097),    
		array("run",-14094),    
		array("ruo",-14092),    
		array("sa",-14090),    
		array("sai",-14087),    
		array("san",-14083),    
		array("sang",-13917),    
		array("sao",-13914),    
		array("se",-13910),    
		array("sen",-13907),    
		array("seng",-13906),    
		array("sha",-13905),    
		array("shai",-13896),    
		array("shan",-13894),    
		array("shang",-13878),    
		array("shao",-13870),    
		array("she",-13859),    
		array("shen",-13847),    
		array("sheng",-13831),    
		array("shi",-13658),    
		array("shou",-13611),    
		array("shu",-13601),    
		array("shua",-13406),    
		array("shuai",-13404),    
		array("shuan",-13400),    
		array("shuang",-13398),    
		array("shui",-13395),    
		array("shun",-13391),    
		array("shuo",-13387),    
		array("si",-13383),    
		array("song",-13367),    
		array("sou",-13359),    
		array("su",-13356),    
		array("suan",-13343),    
		array("sui",-13340),    
		array("sun",-13329),    
		array("suo",-13326),    
		array("ta",-13318),    
		array("tai",-13147),    
		array("tan",-13138),    
		array("tang",-13120),    
		array("tao",-13107),    
		array("te",-13096),    
		array("teng",-13095),    
		array("ti",-13091),    
		array("tian",-13076),    
		array("tiao",-13068),    
		array("tie",-13063),    
		array("ting",-13060),    
		array("tong",-12888),    
		array("tou",-12875),    
		array("tu",-12871),    
		array("tuan",-12860),    
		array("tui",-12858),    
		array("tun",-12852),    
		array("tuo",-12849),    
		array("wa",-12838),    
		array("wai",-12831),    
		array("wan",-12829),    
		array("wang",-12812),    
		array("wei",-12802),    
		array("wen",-12607),    
		array("weng",-12597),    
		array("wo",-12594),    
		array("wu",-12585),    
		array("xi",-12556),    
		array("xia",-12359),    
		array("xian",-12346),    
		array("xiang",-12320),    
		array("xiao",-12300),    
		array("xie",-12120),    
		array("xin",-12099),    
		array("xing",-12089),    
		array("xiong",-12074),    
		array("xiu",-12067),    
		array("xu",-12058),    
		array("xuan",-12039),    
		array("xue",-11867),    
		array("xun",-11861),    
		array("ya",-11847),    
		array("yan",-11831),    
		array("yang",-11798),    
		array("yao",-11781),    
		array("ye",-11604),    
		array("yi",-11589),    
		array("yin",-11536),    
		array("ying",-11358),    
		array("yo",-11340),    
		array("yong",-11339),    
		array("you",-11324),    
		array("yu",-11303),    
		array("yuan",-11097),    
		array("yue",-11077),    
		array("yun",-11067),    
		array("za",-11055),    
		array("zai",-11052),    
		array("zan",-11045),    
		array("zang",-11041),    
		array("zao",-11038),    
		array("ze",-11024),    
		array("zei",-11020),    
		array("zen",-11019),    
		array("zeng",-11018),    
		array("zha",-11014),    
		array("zhai",-10838),    
		array("zhan",-10832),    
		array("zhang",-10815),    
		array("zhao",-10800),    
		array("zhe",-10790),    
		array("zhen",-10780),    
		array("zheng",-10764),    
		array("zhi",-10587),    
		array("zhong",-10544),    
		array("zhou",-10533),    
		array("zhu",-10519),    
		array("zhua",-10331),    
		array("zhuai",-10329),    
		array("zhuan",-10328),    
		array("zhuang",-10322),    
		array("zhui",-10315),    
		array("zhun",-10309),    
		array("zhuo",-10307),    
		array("zi",-10296),    
		array("zong",-10281),    
		array("zou",-10274),    
		array("zu",-10270),    
		array("zuan",-10262),    
		array("zui",-10260),    
		array("zun",-10256),    
		array("zuo",-10254)    
		);
		if($num>0&$num<160)
		{    
		   return chr($num);    
		}    
		elseif($num<-20319||$num>-10247)
		{    
		   return "";    
		}
		else
		{
		  for($i=count($ddddd)-1;$i>=0;$i--)    
		  {
			if($ddddd[$i][1]<=$num)
				break;
		  }    
		  return $ddddd[$i][0];    
		}    
	}    
	     
	public static function StrToPinyin($str, $encoding='utf-8')
	{
		$encoding = strtolower($encoding);
		if($encoding!='gbk' && $encoding!='gb2312')
		{
			$str = iconv($encoding, 'gbk', $str);
		}
		$ret="";    
		for($i=0;$i<strlen($str);$i++)
		{    
		  	$p=ord(substr($str,$i,1));    //echo $p.'<br>';
		   	if($p>160)
		   	{    
			    $q=ord(substr($str,++$i,1));    
			    $p=$p*256+$q-65536;    
		   	}    
			$ret.=self::GetPinyinByChar($p);    
		}    
		return $ret;
	}   
	
	public static function GetFirstPinyinByChar($str) 
	{
	    $asc = ord(substr($str, 0, 1));
	    if ($asc < 160) 
	    {
	    	//非中文
	        if ($asc >= 48 && $asc <= 57) {
	            return '1';  //数字
	        } elseif ($asc >= 65 && $asc <= 90) {
	            return chr($asc);   // A--Z
	        } elseif ($asc >= 97 && $asc <= 122) {
	            return chr($asc - 32); // a--z
	        } else {
	            return '~'; //其他
	        }
	    } 
	    else 
	    {
	    	//中文
	        $asc = $asc * 1000 + ord(substr($str, 1, 1));
			//获取拼音首字母A--Z
	        if ($asc >= 176161 && $asc < 176197) {
	            return 'A';
	        } elseif ($asc >= 176197 && $asc < 178193) {
	            return 'B';
	        } elseif ($asc >= 178193 && $asc < 180238) {
	            return 'C';
	        } elseif ($asc >= 180238 && $asc < 182234) {
	            return 'D';
	        } elseif ($asc >= 182234 && $asc < 183162) {
	            return 'E';
	        } elseif ($asc >= 183162 && $asc < 184193) {
	            return 'F';
	        } elseif ($asc >= 184193 && $asc < 185254) {
	            return 'G';
	        } elseif ($asc >= 185254 && $asc < 187247) {
	            return 'H';
	        } elseif ($asc >= 187247 && $asc < 191166) {
	            return 'J';
	        } elseif ($asc >= 191166 && $asc < 192172) {
	            return 'K';
	        } elseif ($asc >= 192172 && $asc < 194232) {
	            return 'L';
	        } elseif ($asc >= 194232 && $asc < 196195) {
	            return 'M';
	        } elseif ($asc >= 196195 && $asc < 197182) {
	            return 'N';
	        } elseif ($asc >= 197182 && $asc < 197190) {
	            return 'O';
	        } elseif ($asc >= 197190 && $asc < 198218) {
	            return 'P';
	        } elseif ($asc >= 198218 && $asc < 200187) {
	            return 'Q';
	        } elseif ($asc >= 200187 && $asc < 200246) {
	            return 'R';
	        } elseif ($asc >= 200246 && $asc < 203250) {
	            return 'S';
	        } elseif ($asc >= 203250 && $asc < 205218) {
	            return 'T';
	        } elseif ($asc >= 205218 && $asc < 206244) {
	            return 'W';
	        } elseif ($asc >= 206244 && $asc < 209185) {
	            return 'X';
	        } elseif ($asc >= 209185 && $asc < 212209) {
	            return 'Y';
	        } elseif ($asc >= 212209) {
	            return 'Z';
	        } else {
	            return '~';
	        }
	    }
	}
	
	public static function ReplaceQueryString($querystr, $findkey, $newvalue)
	{
		$prefix = '';
		$arrtotal = explode('?', $querystr);
		if(count($arrtotal)==2)
		{
			$prefix = $arrtotal[0];
			$querystr = $arrtotal[1];
		}
		$result = '';
		$found = false;
		$arr = explode('&', $querystr);
		foreach ($arr as $item)
		{
			$itemarr = explode('=',$item);
			if($itemarr[0]==$findkey)
			{
				$itemarr[1] = $newvalue;
				$found = true;
			}
			$itemstr = $itemarr[0].'='.$itemarr[1];
			$result .= $result ? "&$itemstr" : "?$itemstr";
		}
		if(!$found)
		{
			$itemstr = $findkey.'='.$newvalue;
			$result .= $result ? "&$itemstr" : "?$itemstr";
		}
		return $prefix.$result;
	}
}
