<?php
// Require class
require_once("InetAddress.php");

/**
 * Socket address representation
 * 
 * @author		Gustavo Gomes
 * @copyright	2006-08-04
 */
class InetSocketAddress {
	
	private $address = null;
	
	private $port;
	
	private $unresolved = false;
	
	/**
	 * Constructor
	 * 
	 * @param	mixed $host - the host can be a string (host name / ip) or InetAddress object
	 * @param	int $port
	 */
	public function __construct($host, $port) {
		if (is_string($host)) {
			try {
				$this->address = InetAddress::getByName($host);
			} catch (UnknownHostException $uhe) {
				try {
					$this->address = InetAddress::getByAddress($host);
				} catch (UnknownHostException $uhe) {
					$this->unresolved = true;
				}
			}
			$this->port = $port;
		} else if (is_object($host) && $host instanceof InetAddress) {
			$this->address = $host;
			$this->port = $port;
		} else {
			$this->unresolved = true;
		}
	}
	
	public function getAddress() {
		return $this->address;
	}
	
	public function getHostName() {
		return $this->address->getHostName();
	}
	
	public function getPort() {
		return $this->port;
	}
	
	public function isUnresolved() {
		return $this->unresolve;
	}
}
?>
