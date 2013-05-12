<?php
class MonitorClient
{
	private $_deviceid;
	private $_deviceMac;
	private $_deviceMacFull;
	private $_serverIP;
	private $_serverPort;
	/**
	 * FWSocket instance.
	 * @var FWSocket
	 */
	private $_socket;
	
	const PackInit = 0x0101;
	const PackError = 0x0110;
	const PackMachineState = 0x0112;
	const PackMachineClose = 0x0113;
	const PackMachineScreen = 0x0114;
	const PackMachineUpdate = 0x0115;
	
	const ErrorTimeout = 0x2;
	const ErrorNoMachine = 0x3;
	
	public function __construct($deviceMac)
	{
		$this->_deviceMac = $deviceMac;
		$this->_deviceMacFull = OpenDev::MacToBin($this->_deviceMac);
		
		$this->_socket = new FWSocket();
	}
	
	public function __destruct()
	{
		if ($this->_socket->IsConnected())
		{
			$this->_socket->Disconnect();
		}
	}
	
	public function Connect($serverip, $serverport)
	{
		$this->_serverIP = $serverip;
		$this->_serverPort = $serverport;
		$this->_socket-> Connect( $this->_serverIP, $this->_serverPort);
		return $this->_socket->IsConnected();
	}
	
	public function Init()
	{
		$binarydata = pack("SSLLL", 16, self::PackInit, 0xF0F0F0F0, 0x0000F0F0, 0);
		$this->_socket->Send($binarydata, 16);
		$buf = "";
		$this->_socket->Recv($buf, 4, 30);
		$pack = unpack("Slen/Stype", $buf);
		//print_r($pack);
	}
	
	public function RequestState()
	{
		$binarydata = pack("SSH16L", 16, self::PackMachineState, 
			$this->_deviceMacFull, 0);
		$this->_socket->Send($binarydata, 16);
		$pack = $this->recvResult();
		if($pack['state'])
		{
			$device = Device::DeviceFromMac($this->_deviceMac);
			if ($device)
			{
				foreach ($pack['state'] as $key=>$val)
				{
					$showval = $val;
					switch ($key)
					{
						case 'disktotal': 
							$device->st_disktotal = $val; 
							$showval=OpenDev::GetFileSizeDesc($val*1024); 
							break;
						case 'diskfree': 
							$device->st_diskfree = $val; 
							$showval=OpenDev::GetFileSizeDesc($val*1024); 
							break;
						case 'diskuse': 
							$device->st_diskuse = $val; 
							$showval=OpenDev::GetFileSizeDesc($val*1024); 
							break;
						case 'cpunice': $device->st_cpunice=$val; break;
						case 'cpuidle': $device->st_cpuidle=$val; break;
						
						case 'memtotalmem': 
							$device->st_memtotalmem=$val;
							$showval=OpenDev::GetFileSizeDesc($val*1024); 
							break;
						case 'membuffers': 
							$device->st_membuffers=$val;
							$showval=OpenDev::GetFileSizeDesc($val*1024); 
							break;
						case 'memfreemem': 
							$device->st_memfreemem=$val;
							$showval=OpenDev::GetFileSizeDesc($val*1024); 
							break;
						case 'memcached': 
							$device->st_memcached=$val;
							$showval=OpenDev::GetFileSizeDesc($val*1024); 
							break;
						case 'memtotalswap': 
							$device->st_memtotalswap=$val;
							$showval=OpenDev::GetFileSizeDesc($val*1024); 
							break;
						case 'memfreeswap': 
							$device->st_memfreeswap=$val;
							$showval=OpenDev::GetFileSizeDesc($val*1024); 
							break;							
						case 'procpri': $device->st_procpri=$val; break;
						case 'procnice': $device->st_procnice=$val; break;
						case 'procsize': $device->st_procsize=$val; break;
						case 'procrss': $device->st_procrss=$val; break;
						case 'procstate': $device->st_procstate=$val; break;
						case 'proctime': $device->st_proctime=$val; break; 
						case 'procpcpu': $device->st_procpcpu=$val; break; 
						case 'procwcpu': $device->st_procwcpu=$val; break; 
						default: break;
					}
					$ret.= $ret ? ",$key=$showval" : "$key=$showval";
				}
				$device->st_lastbeattime = time();
				$device->SaveData();
			}
			return true;
		} 
		else
		{
			throw new PCException("获取状态失败");
		}
	}
	
	public function RequestClose($tostate)
	{
		$tostate = intval($tostate);
		$binarydata = pack("SSH16LL", 20, self::PackMachineClose, 
			$this->_deviceMacFull, 0, $tostate);
		$this->_socket->Send($binarydata, 20);
		$pack = $this->recvResult();
	
		$device = Device::DeviceFromMac($this->_deviceMac);
		if(!$device || !$device->GetData())
		{
			throw new PCException("查找设备失败".$this->_deviceMac);
		}
		if($pack['screenstate']==-1)
		{
			throw new PCException("操作太频繁");
		}
		else 
		{
			switch($pack['screenstate'])
			{
				case 3:
					$device->st_status = 0;
					$device->st_lastbeattime = time();
					$device->SaveData();
					break;
				case 2:
				case 1:
					$device->st_status = 1;
					$device->st_screenstate = 1;
					$device->st_lastbeattime = time();
					$device->SaveData();
					break;
				case 0:
					$device->st_status = 1;
					$device->st_screenstate = 0;
					$device->st_lastbeattime = time();
					$device->SaveData();
					break;
				default:
					break;
			}
		}
		return $pack['screenstate'];
	}
	
	public function RequestScreen()
	{
		$binarydata = pack("SSH16L", 16, self::PackMachineScreen, 
			$this->_deviceMacFull, 0);
		$this->_socket->Send($binarydata, 16);
		$pack = $this->recvResult();
		if ($pack['image']) 
		{
			$device = Device::DeviceFromMac($this->_deviceMac);
			if(!$device || !$device->GetData())
			{
				throw new PCException("获取设备失败");
			}
			$this->_deviceid = $device->deviceid;
			//echo "iamgelen:".strlen($pack['image']);
			$bigpath = Device::ScreenImageUrl($this->_deviceMac, false);
			$smallpath = Device::ScreenImageUrl($this->_deviceMac, true);
			$bigimagepath = PUBLIC_DIR.$bigpath;
			$smallimagepath = PUBLIC_DIR.$smallpath;
			OpenDev::writetofile($bigimagepath, $pack['image']);
			OpenDev::create_thumbnail($pack['image'], $smallimagepath, 250,250);
			//header('Location: '.$bigpath);
			return true;
		}
		else
		{
			throw new PCException("获取图片失败");
		}
	}
	
	public function RequestUpdate($isEmergency=0)
	{
		//$isEmergency L:0普通,1插播
		$binarydata = pack("SSL", 8, self::PackMachineUpdate, $isEmergency);
		$this->_socket->Send($binarydata, 8);
		sleep(1);
		return true;
	}
	
	private function recvResult()
	{
		$buf = "";
		$this->_socket->Recv($buf, 16, 30);
		$pack = unpack("Slen/Stype/H16mac/Ltag", $buf);
		//print_r($pack);
		$pack['mac'] = OpenDev::BinToMac($pack['mac']);
		$this->_deviceMac = $pack['mac'];
		//echo "RecvHead:".$pack['type']." mac:".$pack['mac']."<BR>\n";
		switch ($pack['type'])
		{
			case self::PackError: 
				$errbuf = '';
				$this->_socket->Recv($errbuf, 4, 30);
				$errpack = unpack('Lerror', $errbuf);
				if ($errpack['error']==self::ErrorNoMachine)
				{
					throw new PCException("设备没有连接到服务器");
				}
				elseif ($errpack['error']==self::ErrorTimeout)
				{
					throw new PCException("连接到设备超时");
				}
				else
				{
					throw new PCException("未知错误".$pack['error']);
				}
				break;
			case self::PackMachineState:
				$pack['state'] = $this->recvMachineState();
				break;
			case self::PackMachineClose:
				$currstate = $this->recvMachineClose();
				$pack['screenstate'] = $currstate;
				break;
			case self::PackMachineScreen:
				$pack['image'] = $this->recvScreen();
				break;
			default:
				throw new PCException('收报type错误');
				break;
		}
		return $pack;
	}
	
	private function recvErrorNo()
	{
		$buf = "";
		$this->_socket->Recv($buf, 4, 30);
		$arr = unpack("Lresult", $buf);
		return $arr;
	}
	
	private function recvMachineClose()
	{
		$buf = "";
		$this->_socket->Recv($buf, 4, 30);
		$arr = unpack("Lstate", $buf);
		return $arr['state'];
	}
	
	private function recvMachineState()
	{
		$buf = "";
		$this->_socket->Recv($buf, 23*4, 30);
		$format = "Ldisktotal/Ldiskfree/Ldiskuse/LcpuNice/LcpuSystem/LcpuSystem/LcpuIdle/Lmemtotalmem/";
		$format .= "Lmemfreemem/Lmembuffers/Lmemcached/Lmemtotalswap/Lmemfreeswap/Lprocpri/Lprocnice/";
		$format .= "Lprocsize/Lprocrss/Lprocstate/Lproctime/H16procpcpu/H16procwcpu";
		$rs = unpack($format, $buf);
		//echo "<pre>";
		//print_r($rs);
		//echo "</pre>\n";
		return $rs;
	}
	
	private function recvScreen()
	{
		$image = "";
		$imagelen = 0;
		$finished = false;
		$times = 0;
		while (!$finished)
		{
			if($times>1000) 
			{
				throw new PCException("截屏收报错误");
			}
			$buf = "";
			$this->_socket->Recv($buf, 5, 30);
			$arr = unpack("Cstate/Llen", $buf);
			//print_r($arr);
			$state = $arr['state'];
			$thislen = $arr['len'];
			if($thislen>1024*1024*10)
			{
				throw new PCException('图片包长度超出 '.$thislen);
			}
			$buf = '';
			$this->_socket->Recv($buf, $thislen, 30);
			$image .= $buf;
			$imagelen += $thislen;
			if ($state==1 || $state==2)
			{
				$headbuf = '';
				$this->_socket->Recv($headbuf, 16, 30);
				//echo "<pre>Imagepack head:".bin2hex($headbuf)."</pre>";
			}
			if ($state == 3)
			{
				$finished = true;
			}
			$times++;
		}
		//OpenDev::writetofile(FW_DIR."image.jpg", $image, $imagelen);
		return $image;
	}
}