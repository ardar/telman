<?php
class MediaInfo
{
	const MediaInfoPath = "3rdParty/MediaInfo/";
	const MediaInfoExe = "MediaInfo.exe";
	private $_filepath;
	public $DurationTime = null;
	public $Width = null;
	public $Height = null;
	public $Ratio = null;
	public $Format = null;
	public $Bitrate = null;
	
	public function __construct($filepath)
	{
		$this->_filepath = $filepath;
	}
	
	public function GetInfo($showfullinfo=false)
	{
		if (!$showfullinfo)
		{
			$options = "--Inform=\"file://".FW_DIR.self::MediaInfoPath."tpl.txt\"";
		}
		$cmd = "\"". FW_DIR . self::MediaInfoPath . self::MediaInfoExe
			. "\" " . $options . " \"" .FW_DIR."public/". $this->_filepath ."\"";
		//echo $cmd = str_replace( '/',"\\", $cmd);
		$result = shell_exec($cmd);
		if ($result)
		{
			//Duration:30093*Format:VC-1*Width:1280*Weigt:720*Ratio:16:9*Bitrate:(CBR)5 942 Kbps*Audio_format:WMA
			//Duration:*ImageFormat:JPEG*Width:1024*Height:768
			$arr = explode('*', $result);
			$count = count($arr);
			for ($i=0;$i<$count;$i++)
			{
				$itemarr = explode('=',trim($arr[$i]));
				$val = trim($itemarr[1]);
				//echo $itemarr[0].'='.$val."<BR>";
				switch (trim($itemarr[0]))
				{
					case 'Duration': $this->DurationTime = intval($val/1000);break;
					case 'Width': $this->Width = $val;break;
					case 'Height': $this->Height = $val;break;
					case 'Ratio': $this->Ratio = $val;break;
					case 'Format': $this->Format = $val;break;
					case 'Bitrate': $this->Bitrate = $val;break;
					case 'AudioFormat': $audioformat = $val;break;
					default:break;
				}
			}
			//$this->Ratio = $this->Ratio ? $this->Ratio : ($this->Width.":".$this->Height);
			$this->Format .= $audioformat ? (" / Audio:$audioformat ") : '';
			return $result;
		}
		return false;
	}
}