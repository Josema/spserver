<?php
/*
 * Created on 26/12/2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

// Require class
require_once("exception/SocketException.php");
require_once("InetSocketAddress.php");

abstract class AbstractSocket {

	/**
	 * Socket Handle
	 * @var Socket
	 */
	protected $hnd;

	/**
	 * Socket Address representation
	 * @var InetSocketAddress
	 */
	protected $socketAddr = null;

	/**
	 * Socket type.  (SOCK_DGRAM | SOCK_RAW |	SOCK_RDM | SOCK_SEQPACKET | SOCK_STREAM)
	 * @var int
	 */
	protected $type;

	/**
	 * Socket Family (AF_INET|AF_INET6|AF_UNIX)
	 * @var int
	 */
	protected $family;

	/**
	 * Socket protocol (SOL_SOCKET | SOL_TCP | SOL_UDP)
	 * @var int
	 */
	protected $protocol;

	/**
	 * Socket connection state
	 * @var bool
	 */
	protected $bConnected = false;

	/**
	 * Determines if error must be shown
	 * @va bool
	 */
	protected $bShowErros = false;
	
	/**
	 * Constructor - create socket handler and set all parameters
	 *
	 * @param	int $port
	 * @param	int $family - (AF_INET|AF_INET6|AF_UNIX)
	 * @param	int $type - (SOCK_DGRAM | SOCK_RAW |	SOCK_RDM | SOCK_SEQPACKET | SOCK_STREAM)
	 * @param	int $protocol - (SOL_SOCKET | SOL_TCP | SOL_UDP)
	 */
	public function __construct($family=AF_INET,$type=SOCK_STREAM ,$protocol=SOL_TCP) {
		if ($family != null && $type != null && $protocol != null) {
			$this->hnd = @socket_create($family, $type, $protocol);
			$this->error();
		}

		$this->family 	= $family;
		$this->type		= $type;
		$this->protocol	= $protocol;
	}
	
	/**
	 * Set if this class print errors
	 * 
	 * @param	boolean $op
	 */
	public function showErrors($op) {
		$this->bShowErros = (boolean)$op;
	}
	
	/**
	 * Get socket address representation
	 * 
	 * @return	InetSocketAddress
	 */
	public function getLocalSocketAddress(){
		return $this->socketAddr;
	}

	/**
	 * Close socket connection
	 */
	public function close(){
		if ($this->bConnected) {
			@socket_shutdown($this->hnd, 2);
			@socket_close($this->hnd);
			$this->bConnected = false;
		}
	}

	public function isClosed() {
		return (!$this->bConnected);
	}

	/**
	 * Alias for close() method
	 */
	public function disconnect(){
		return $this->close();
	}

	/**
	 * Show errors if $bShowErrors is enabled
	 * If $bExceptions, throws an exception, otherwise prints a message.
	 * If $msg is not empty, show $msg;
	 *
	 * @param	string $msg
	 * @throws	SocketException
	 */
	protected function error($msg=null) {
		$errCode = socket_last_error($this->hnd);
		if ($errCode != 0){
			$msg = socket_strerror($errCode);
			socket_clear_error($this->hnd);

			// Connection reset by peer
			if ($errCode == 104) {
				$this->bConnected = false;
				$this->close();
			}
			
			if ($this->bShowErros)
				trigger_error("Socket Error - Code: ".$errCode." - Message: ".$msg);
			throw new SocketException($msg, $errCode);
		} else if (strlen($msg) > 0){
			if ($this->bShowErros)
				trigger_error("Socket Error - Code: 0 - Message: ".$msg, E_USER_ERROR);
			throw new SocketException($msg);
		}
	}
}
?>
