<?php

require_once("AbstractSocket.php");

/**
 * TCP Server Socket
 *
 * @author		Gustavo Gomes
 * @author		MC Breit
 * @version		0.9
 * @copyright	2006-12-27
 * #see			PHP Classes - Simple Server
 */
class ServerSocket extends AbstractSocket {

	/**
	 * Contains the socket handler of client connection.
	 * @var mixed
	 */
	private $socketAccepted = null;

	/**
	 * The connections max
	 * @var int
	 */
	private $backLog = 50;
  
	/**
	 * Creates a new Server and initialize listen
	 * 
	 * @throws	SocketException
	 */
	public function __construct($port, $backLog=0) {
		parent::__construct();
		$inetAddr = InetAddress::getLocalHost();
			$this->socketAddr = new InetSocketAddress($inetAddr, $port);
		
		if (!@socket_bind($this->hnd, $inetAddr->getAddress(), $port))
			$this->error();

		$this->bConnected = true;
		if ((int)$backLog != 0)
			$this->backLog = $backLog;
		
		if (@socket_listen($this->hnd, $this->backLog) < 0)
			$this->error("Cannot listen");
	}
	
	/**
	 * Get server address
	 * 
	 * @return	InetAddress
	 */
	public function getInetAddress() {
		return $this->socketAddr->getAddress();
	}
	
	/**
	 * Get server port
	 * 
	 * @return	int
	 */
	public function getLocalPort(){
		return $this->socketAddr->getPort();
	}
	
	/**
	 * Get last client connection accepted
	 * 
	 * @return	Socket
	 */
	public function getLastSocketAccepted() {
		return $this->socketAccepted;
	}
	
	/**
	 * Waits for incomming connections and the first
	 * connection.
	 * 
	 * @return	Socket - client socket
	 */
	public function accept() {
		/* @ignore
		if (($socket = @socket_accept($this->hnd)) < 0)
			$this->error();
    
		if (!$socket)
			$this->error("Accept socket connection failed");
		$this->socketAccepted = new ClientSocketAccepted($socket);
		return $this->socketAccepted;
		*/
		if (($socket = @socket_accept($this->hnd)) < 0)
			$this->error();
    
		if ($socket) {
			$this->socketAccepted = new ClientSocketAccepted($socket);
			return $this->socketAccepted;
		}
		return null;
	}
}

/**
 * Client socket used on ServerSocket
 * 
 * @author		Gustavo Gomes
 * @copyright	2006-12-27
 */
class ClientSocketAccepted extends Socket {
	
	/**
	 * Cosntructor
	 * 
	 * @param	resource $rscClient
	 */
	public function __construct($rscClient) {
		parent::__construct(null, null, null, null, null);
		if (is_resource($rscClient) && strtolower(get_resource_type($rscClient)) == "socket") {
			$this->socketAddr = $this->getSocketAddressByResource($rscClient, 2);
			$this->hnd = $rscClient;
			$this->bConnected = true;
			$this->remoteAddr = $this->getSocketAddressByResource($rscClient);
		}
	}
}
?>