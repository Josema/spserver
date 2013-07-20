<?php
/******************************************************************************************************
	LICENSE

	Copyright (C) 2006 Juan M. Hidalgo

    This program is free software; you can redistribute it and/or modify it under the terms of the
    GNU General Public License as published by the Free Software Foundation; either version 2 of the License,
    or (at your option) any later version.

    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
    without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    See the GNU General Public License for more details.

    You should have received a copy of the GNU General
    Public License along with this program; if not,
    write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330,
    Boston, MA 02111-1307 USA
****************************************************************************************************/

require_once("AbstractSocket.php");

/**
 * Client Socket to comunication with servers
 *
 * @author		Juan M. Hidalgo
 * @author		Gustavo Gomes
 * @version		0.9
 * @copyright	2006-12-27
 */
class Socket extends AbstractSocket {
	
	const READER_BUFFER = 4096;
	
	/**
	 * Remote Socket Address representation
	 * @var InetSocketAddress
	 */
	protected $remoteAddr = null;
	
	/**
	 * Socket buffer
	 * @var string
	 */
	private $sBuffer;

	/**
	 * Read TimeOut (seconds)
	 * @var int
	 */
	private $iReadTimeOut = 2;

	/**
	 * Write TimeOut (seconds)
	 * @var int
	 */
	private $iWriteTimeOut = 2;

	/**
	 * Constructor - create socket handler, set all parameters
	 * and open socket connection.
	 *
	 * @param	mixed $addr
	 * @param	mixed $port
	 * @param	int $family - (AF_INET|AF_INET6|AF_UNIX)
	 * @param	int $type - (SOCK_DGRAM | SOCK_RAW |	SOCK_RDM | SOCK_SEQPACKET | SOCK_STREAM)
	 * @param	int $protocol - (SOL_SOCKET | SOL_TCP | SOL_UDP)
	 * @throws	SocketException, UnknownHostException
	 */
	public function __construct($addr=null,$port=null,$family=AF_INET,$type=SOCK_STREAM ,$protocol=SOL_TCP) {
		parent::__construct($family, $type, $protocol);
		$this->sBuffer = false;

		if ($addr != null && $port != null)
			$this->open($addr, $port);
	}
	
	/**
	 * Get client address
	 * 
	 * @return	InetAddress
	 */
	public function getLocalAddress() {
		if ($this->socketAddr != null)
			return $this->socketAddr->getAddress();
		return null;
	}

	/**
	 * Get client port
	 * 
	 * @return	int
	 */
	public function getLocalPort(){
		if ($this->socketAddr != null)
			return $this->socketAddr->getPort();
		return null;
	}
	
	/**
	 * Get remote address
	 * 
	 * @return	InetAddress
	 */
	public function getInetAddress() {
		if ($this->remoteAddr != null)
			return $this->remoteAddr->getAddress();
		return null;
	}

	/**
	 * Get remote port
	 * 
	 * @return	int
	 */
	public function getPort(){
		if ($this->remoteAddr != null)
			return $this->remoteAddr->getPort();
		return null;
	}
	
	/**
	 * Get remote socket informations
	 * 
	 * @return	InetSocketAddress
	 */
	public function getRemoteSocketAddress(){
		return $this->remoteAddr;
	}

	/**
	 * Sets inetAddress by host name or ip
	 * 
	 * @param	string $addr
	 * @throws	UnknownHostException
	 */
	private function findHost($addr) {
		try {
			return $this->findByHost($addr);
		} catch (UnknownHostException $uhe) {
			return $this->findByIp($addr);
		}
	}

	/**
	 * Sets a host and tries to resolve IP address. If Ip is valid adds it to List of ip
	 * 
	 * @param	string $sHost
	 * @throws	UnknownHostException
	 */
	private function findByHost($sHost){
		if (strlen($sHost) == 0)
			$this->error("setByIp argument invalid.");

		return InetAddress::getByName($sHost);
	}

	/**
	 * Sets Ip addres
	 * @param	string $sIp - (xxx.xxx.xxx.xxx)
	 * @throws	UnknownHostException
	 */
	private function findByIp($sIp){
		if (strlen($sIp) == 0)
			$this->error("setByIp argument invalid.");

		return InetAddress::getByAddress($sIp);
	}
	
	/**
	 * Set socket based on socket connection resource
	 * 
	 * @param	resource $rsc
	 * @return	InetSocketAddress
	 * @throws	SocketException
	 */
	protected function getSocketAddressByResource($rsc, $side=1) {
		if (!$rsc)
			$this->error("Cannot set socket by resource");
    
		$ip = null;
		$port = null;
		if ($side == 1) {
			if (!@socket_getpeername($rsc, $ip, $port))
				$this->error();
		} else {
			if (!@socket_getsockname($rsc, $ip, $port))
				$this->error();
		}
		
		$inetAddr = $this->findByIp($ip);
		return new InetSocketAddress($inetAddr, $port);
	}

	/**
	 * Set socket host and port
	 * 
	 * @param	mixed $sHost - The host can be a InetAddress or InetSocketAddress
	 * 			or string or socket resource.
	 * @param	int $iPort - Used if sHost is an InetAddress or string.
	 */
	private function set($sHost=null,$iPort=null) {
		if (is_object($sHost)) {
			if ($sHost instanceof InetAddress) {
				$this->remoteAddr = new InetSocketAddress($sHost, (int)$iPort);
			} else if ($sHost instanceof InetSocketAddress) {
				$this->remoteAddr = $sHost;
			} else {
				throw new SocketException("Invalid class address");
			}
		} else if (is_string($sHost)) {
			$inetAddr = $this->findHost($sHost);
			$this->remoteAddr = new InetSocketAddress($inetAddr, (int)$iPort);
		}
	}
	
	/**
	 * Open socket connection
	 *
	 * @param	mixed $sHost - The host can be a InetAddress or InetSocketAddress
	 * 			or string or socket resource.
	 * @param	int $iPort - Used if sHost is an InetAddress or string.
	 * @throws	SocketException
	 */
	public function open($sHost=null,$iPort=null) {
		$this->set($sHost, $iPort);
		$i = 0;
		$addrs = InetAddress::getAllByName($this->remoteAddr->getHostName());
		do {
			if (@socket_connect($this->hnd, $addrs[$i]->getAddress(), $this->remoteAddr->getPort()))
				$this->bConnected 	= true;
		} while (!$this->bConnected && $i++ < count($addrs));
		$this->error();
		
		$this->socketAddr = $this->getSocketAddressByResource($this->hnd, 2);
	}

	/**
	 * Connect with  host (open alias)
	 * 
	 * @param	mixed $sHost - The host can be a InetAddress or InetSocketAddress
	 * 			or string or socket resource.
	 * @param	int $iPort - Used if sHost is an InetAddress or string.
	 * @throws	SocketException
	 */
	public function connect($sHost=null,$iPort=null) {
		return $this->open($sHost, $iPort);
	}

	public function isConnected() {
		return $this->bConnected;
	}

	/**
	 * Send data
	 * If $sBuf is not empty try to send $sBuf, else try with $this->sBuffer
	 *
	 * @param	string $sBuf
	 * @param	int $iTimeOut
	 * @throws	SocketException
	 */
	public function send($sBuf,$iTimeOut=null){
		if (strlen($this->sBuffer) == 0 && strlen($sBuf) == 0)
			$this->error("Empty buffer");
		if (!$this->bConnected)
			$this->error("Cannot send data on a closed socket.");

		$vWrite = array($this->hnd);

		$WriteTimeOut = (strlen($iTimeOut)) ? $iTimeOut : $this->iWriteTimeOut;
		while (($rr = @socket_select($vRead = null, $vWrite, $vExcept = null, $WriteTimeOut)) === false);

		if ($rr == 0)
			$this->error("Call system select failed");

		$tmpBuf		= strlen($sBuf) ? $sBuf : $this->sBuffer;
		$iBufLen	= strlen($tmpBuf);
		$res 		= @socket_send($this->hnd,$tmpBuf,$iBufLen,0);

		if ($res === false) {
			$this->error();
		} else if ($res < $iBufLen) {
			$tmpBuf = substr($tmpBuf,$res);
			$this->send($tmpBuf);
		}
	}

	/**
	 * Send alias
	 *
	 * @param	string $sBuf
	 * @param	int $iTimeOut
	 */
	public function write($sBuf,$iTimeOut=null) {
		return $this->send($sBuf,$iTimeOut);
	}

	/**
	 * Read data from socket
	 *
	 * @param	int $iTimeOut
	 * @return	string
	 * @throws	SocketException
	 */
	public function recv($iTimeOut=null) {
		if (!$this->bConnected)
			$this->error("Cannot read any data on a closed socket.");

		$vSocket		= array($this->hnd);
		$this->sBuffer 	= null;
		$buf			= null;
		$ReadTimeOut	= (strlen($iTimeOut)) ? $iTimeOut : $this->iReadTimeOut;
		while (($rr = @socket_select($vSocket,$vWrite = null,$vExcept = null,$ReadTimeOut)) === false);

		if($rr == 0)
			$this->error("Call system select failed");

		$res = @socket_recv($this->hnd, $buf, self::READER_BUFFER,0);
		while ($res) {
			$this->sBuffer .=	$buf;
			$buf = null;

			while (($rr = @socket_select($vSocket,$vWrite = null, $vExcept = null, $ReadTimeOut)) === false);
			if ($rr == 0)
				break;

			$res = @socket_recv($this->hnd, $buf, self::READER_BUFFER, 0);
		}
		return $this->sBuffer;
	}

	/**
	 * Recv alias
	 *
	 * @param	int $iTimeOut
	 * @return	string
	 */
	public function read($iTimeOut=null){
		return $this->recv($iTimeOut);
	}

	/**
	 * Send data and wait response
	 *
	 * @param	string $sBuf
	 * @return	string
	 */
	public function sendAndWait($sBuf) {
		$this->send($sBuf);
		return $this->recv();
	}
}
?>