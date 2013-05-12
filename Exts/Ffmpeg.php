<?php
class Ffmpeg
{
	const FfmpegPath = "3rdParty/ffmpeg/";
	const FfmpegExe = "ffmpeg.exe";
	private $_ffmpeg_movie = null;
	private $_filepath;
	public $DurationTime = null;
	public $Width = null;
	public $Height = null;
	public $Ratio = null;
	public $Format = null;
	public $Bitrate = null;
	public $FrameCount = 0;
	
	public function __construct($filepath)
	{
		$this->_filepath = $filepath;
	}
	
	public function SaveThumbnail($thumbnail_path, $from_second, $width, $height)
	{
		$cmd = "\"". FW_DIR . self::FfmpegPath . self::FfmpegExe
			. "\" "." -i \"".FW_DIR .'public/'.$this->_filepath."\""
			." -y -f image2 -ss $from_second -s $width"."x"."$height \""
			.FW_DIR .'public/'.$thumbnail_path
			."\"";
		$cmd = str_replace("/", "\\", $cmd);
		//echo $cmd."\n";
		$result = shell_exec($cmd);
		//echo $result."\n";
		if ($result)
		{
		
		}
	}
	
	public function SaveThumbnail2($thumbnail_path,$maxwidth,$maxheight)
	{
		if(extension_loaded('ffmpeg'))
		{   
			$this->_ffmpeg_movie = new ffmpeg_movie($filepath);
			$this->FrameCount = $this->_ffmpeg_movie->getFrameCount();
			$ff_frame = $mov->getFrame(intval($this->FrameCount/2));
			$gd_image = $ff_frame->toGDImage();
			imagejpeg($gd_image, $thumbnail_path);
			imagedestroy($gd_image);
		}
		else
		{     
			throw new PCException("ffmpeg没有载入"); 
		}
		return true;
	}
}