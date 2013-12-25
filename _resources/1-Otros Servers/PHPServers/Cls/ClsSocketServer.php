<?php
/**
 * Class to handle a sockets server
 * It's abstract class so you need to create another class that will extends ClsSocketServer to run your server
 * 
 * @author Cyril Mazur	www.cyrilmazur.com	twitter.com/CyrilMazur	facebook.com/CyrilMazur
 * @abstract
 */
abstract class ClsSocketServer {
	/**
	 * The address the socket will be bound to
	 * @var string
	 */
	protected $address;
	
	/**
	 * The port the socket will be bound to
	 * @var int
	 */
	protected $port;
	
	/**
	 * The max number of clients authorized
	 * @var int
	 */
	protected $maxClients;
	
	/**
	 * Array containing all the connected clients
	 * @var array
	 */
	protected $clients;
	
	/**
	 * The master socket
	 * @var resource
	 */
	protected $master;
	
	/**
	 * Constructor
	 * @param string $address
	 * @param int $port
	 * @param int $maxClients
	 * @return ClsSocketServer
	 */
	public function __construct($address,$port,$maxClients) {
		$this->address		= $address;
		$this->port			= $port;
		$this->maxClients	= $maxClients;
		$this->clients		= array();
	}
	
	/**
	 * Start the server
	 */
	public function start() {
		// flush all the output directly
		ob_implicit_flush();
		
		// create master socket
		$this->master = @socket_create(AF_INET, SOCK_STREAM, 0) or die($this->log('Could not create socket'));

		// to prevent: address already in use
		socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1) or die($this->log('Could not set up SO_REUSEADDR'));
		
		// bind socket to port
		@socket_bind($this->master, $this->address, $this->port) or die($this->log('Could not bind to socket'));
		
		// start listening for connections
		socket_listen($this->master) or die($this->log('Could not set up socket listener'));
		
		$this->log('Server started on ' . $this->address . ':' . $this->port);
		
		// infinite loop
		while(true) {
			// build the array of sockets to select
			$read	= array_merge(array($this->master),$this->clients);
			
			// if no socket has changed its status, continue the loop
			socket_select($read,$write = null,$except = null,$tv_sec = null);
			
			// if the master's status changed, it means a new client would like to connect
			if (in_array($this->master,$read)) {
				
				// if we didn't reach the maximum amount of connected clients
				if (sizeof($this->clients) < $this->maxClients) {
					
					// attempt to create a new socket
					$socket = socket_accept($this->master);
					
					// if socket created successfuly, add it to the clients array and write message log
					if ($socket !== false) {
						$this->clients[] = $socket;
						
						if (socket_getpeername($socket,$ip)) {
							$this->log('New client connected: ' . $socket . ' (' . $ip . ')');
						} else {
							$this->log('New client connected: ' . $socket);
						}
						
						$this->onClientConnected($socket);
						
					// else display error message to the log console
					} else {
						$this->log('Impossible to connect new client',true);
					}
				
				// else tell the client that there is not place available and display error message to the log console
				} else {
					$socket = socket_accept($this->master);
					socket_write($socket,'Max clients reached. Retry later.' . chr(0));
					socket_close($socket);
					
					$this->log('Impossible to connect new client: maxClients reached');
				}
				
				if (sizeof($read) == 1)
					continue;
			}
			
			// foreach client that is ready to be read
			foreach($read as $client) {
				
				// we don't read data from the master socket
				if ($client != $this->master) {
					
					// read input
					$input = @socket_read($client, 1024, PHP_BINARY_READ);
					
					// if socket_read() returned false, the client has been disconnected
					if (strlen($input) == 0) {
						// disconnect client
						$this->disconnect($client);
						
						// custom method called
						$this->onClientDisconnected($client);
					
					// else, we received a normal message
					} else {
						$input = trim($input);
						
						// special case of a domain policy file request
						if ($input == '<policy-file-request/>') {
							$cmd = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><cross-domain-policy xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"http://www.adobe.com/xml/schemas/PolicyFileSocket.xsd\"><allow-access-from domain=\"*\" to-ports=\"*\" secure=\"false\" /><site-control permitted-cross-domain-policies=\"master-only\" /></cross-domain-policy>";
							
							$this->log('Policy file requested by ' . $client);
							socket_write($client,$cmd . chr(0));
							
						// normal case, standard message
						} else {
							// custom method called
							$this->onDataReceived($client,$input);
						}
					}
				}
			}
		}
	}
	
	/**
	 * Stop the server: disconnect all the coonected clients, close the master socket
	 */
	public function stop() {
		foreach($this->clients as $client) {
			socket_close($client);
		}
		
		$this->clients = array();
		
		socket_close($this->master);
	}
	
	/**
	 * Disconnect a client
	 * @param resource $client
	 * @return bool
	 */
	protected function disconnect($client) {
		// close socket
		socket_close($client);
		
		// unset variable in the clients array
		$key = array_keys($this->clients,$client);
		unset($this->clients[$key[0]]);
		
		$this->log('Client disconnected: ' . $client);
		
		return true;
	}
	
	/**
	 * Send data to a client
	 * @param resource $client
	 * @param string $data
	 * @return bool
	 */
	protected function send($client,$data) {
		return @socket_write($client, $data . chr(0));
	}
	
	/**
	 * Send data to everybody
	 * @param string $data
	 * @return bool
	 */
	protected function sendBroadcast($data) {
		$return = true;
		foreach($this->clients as $client) {
			$return = $return && socket_write($client, $data . chr(0));
		}
		
		return $return;
	}
	
	/**
	 * Method called after a value had been read
	 * @abstract
	 * @param resource $socket
	 * @param string $data
	 */
	abstract protected function onDataReceived($socket,$data);
	
	/**
	 * Method called after a new client is connected
	 * @param resource $socket
	 */
	abstract protected function onClientConnected($socket);
	
	/**
	 * Method called after a new client is disconnected
	 * @param resource $socket
	 */
	abstract protected function onClientDisconnected($socket);
	
	/**
	 * Write log messages to the console
	 * @param string $message
	 * @param bool $socketError
	 */
	public function log($message,$socketError = false) {
		echo '[' . date('d/m/Y H:i:s') . '] ' . $message;
		
		if ($socketError) {
			$errNo	= socket_last_error();
			$errMsg	= socket_strerror($errNo);
			
			echo ' : #' . $errNo . ' ' . $errMsg;
		}
		
		echo "\n";
	}
}
?>