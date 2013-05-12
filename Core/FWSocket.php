<?php
class FWSocket
{
	private $_remoteIP;
	private $_remotePort;
	private $_socket;
	private $_isConnected = false;
	
	public function __construct()
	{
		$this->_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(!$this->_socket)
		{
			throw new PCException('初始化网络失败');
		}		
		socket_set_option($this->_socket, SOL_SOCKET, SO_SNDTIMEO, array('sec'=>60,'usec'=>0)); 
		socket_set_option($this->_socket, SOL_SOCKET, SO_RCVTIMEO, array('sec'=>60,'usec'=>0)); 
	}
	
	public function __destruct()
	{
		$this->Disconnect();
	}
	
	public function IsConnected()
	{
		return $this->_isConnected;
	}
	
	private function OnError($msg)
	{
		$errorno = socket_last_error($this->_socket);
		$errstr = socket_strerror($errorno);
		echo $errmsg = "FWSocket Error: ".$msg ."  (error ".$errorno. " / ".$errstr.")"; 
		//throw new PCException($errmsg);
	}
	
	public function Connect($serverip, $serverport)
	{
		$this->_remoteIP = $serverip;
		$this->_remotePort = $serverport;
		if(socket_connect($this->_socket, $this->_remoteIP, $this->_remotePort))
		{
			$this->_isConnected = true;
		}
		return $this->_isConnected;
	}
	
	public function Disconnect()
	{
		if ($this->_socket)
		{
			socket_close($this->_socket);
			$this->_socket = null;
		}
		$this->_isConnected = false;
		return true;
	}
	
	public function Send($buf, $len)
	{
		$sent = 0;
		$retry = 0;
		while ($sent<$len)
		{
			$thislen = $len - $sent;
			$thisbuf = substr($buf, $sent, $thislen);
			$thissent = socket_send($this->_socket, $thisbuf, $thislen, null);
			//echo "<pre>sent: $thissent ".bin2hex($thisbuf)."\n</pre>\n";
			if (!$thissent)
			{
				$this->OnError("Send Faild");
			}
			$sent += $thissent;
			$retry++;
			if ($retry>10)
			{
				$this->OnError("Send Faild after retry :".$retry." times.");
			}
		}
		return $sent;
	}
	
	public function Recv(&$buf, $len, $timeout=30)
	{
		$recved = 0;
		$retry = 0;
		$starttime = time();
		while ($recved<$len)
		{
			if ($retry>100)
			{
				$this->OnError("Recv Faild after retry :".$retry." times.");
			}
			if(time()-$starttime>$timeout)
			{
				$this->OnError("Recv Timeout");
			}
			$thislen = $len - $recved;
			$thisbuf = '';
			
			$thisrecv = socket_recv($this->_socket, $thisbuf, $thislen, MSG_WAITALL);
			//echo "<pre>recv: $thisrecv ".bin2hex($thisbuf)."\n</pre>\n";
			if (!$thisrecv)
			{
				$this->OnError("Recv Faild");
			}
			$buf .= $thisbuf;
			$recved += $thisrecv;
			$retry++;
		}
		return $recved;
	}
}