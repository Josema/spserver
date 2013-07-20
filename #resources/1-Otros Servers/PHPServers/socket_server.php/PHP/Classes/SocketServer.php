<?php
class SocketServer
{
	var $ip;
	var $port;
	
	var $games;
	
	var $masterSocket;
	
	var $logger;
	
	private $currentSockets;
	
	function __construct($ip, $port)
	{
		$this->ip = $ip;
		$this->port = $port;
		
		$this->games = array();
		
		$this->logger = Logger::getInstance();
		
		$this->initSocket();
		$this->currentSockets = array();
		
		$this->currentSockets[] = $this->masterSocket;	
		
		$this->listenToSockets();
	}
	
	private function initSocket()
	{
		//---- Start Socket creation for PHP 5 Socket Server -------------------------------------
	 
		if (($this->masterSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0) 
		{
			$this->logger->log("[SocketServer] "."socket_create() failed, reason: " . socket_strerror($this->masterSocket));
		}
		 
		socket_set_option($this->masterSocket, SOL_SOCKET,SO_REUSEADDR, 1);
						 
		if (($ret = socket_bind($this->masterSocket, $this->ip, $this->port)) < 0) {
		
			$this->logger->log("[SocketServer] "."socket_bind() failed, reason: " . socket_strerror($ret));
		
		}
		 
		 
		if (($ret = socket_listen($this->masterSocket, 5)) < 0) {
		
			$this->logger->log("[SocketServer] "."socket_listen() failed, reason: " . socket_strerror($ret));	
		}
	}
	
	private function parseRequest($socket, $data)
	{
		if(substr($data,0, 22) == "<policy-file-request/>")
		{
			echo "POLICY FILE REQUEST\n";
			$crossFile = file("crossdomain.xml");
			$crossFile = join('',$crossFile);
			$this->sendMessage(array($socket),$crossFile);			
			return;
		}
		
			
		try
		{
			$sxml = new SimpleXMLElement($data);
			
			$gameID = $sxml->gameID ."";
			$game = "";
					
			if(array_key_exists($gameID,$this->games) === FALSE)
			{
				$game = new Game($gameID,$this);
				$this->games[$gameID] = $game;
			}
			else
			{
				$game = $this->games[$gameID];
			}
			
			$game->interpretRequest($socket, $sxml->type, $sxml->data);
		}
		catch(Exception $e)
		{
			$this->logger->log("[SocketServer] Exception: ".$e->getMessage());
		}
	}
	
	private function removePlayerFromGame($socket)
	{
		foreach($this->games as $k=>$game)
		{
			// Loop through each game and tell all of them this player has disconnected, if we find one, stop looking
			if($game->removePlayer($socket))
			{				
				if(count($game->players) == 0)
				{
					unset($this->games[$k]);
					$this->logger->log("[SocketServer] Last Player In Game Has Left, Removing Game.");
				}
				break;
			}
		}
		
	}
	
	public function sendMessage($sockets, $message)
	{
		$message .= "\0";
		
		if(!is_array($sockets))
			$sockets = array($sockets);
	
		foreach($sockets as $socket)
		{
			if($socket === NULL)
				continue;
				
			socket_write($socket, $message, strlen($message)); 
			
			//$this->logger->log("[SocketServer] Wrote : ".$message." to ".$socket);
			
		}
	}
	
	private function listenToSockets()
	{
		//---- Create Persistent Loop to continuously handle incoming socket messages ---------------------
		while (true) {
		
			$changed_sockets = $this->currentSockets;
		 
			$num_changed_sockets = socket_select($changed_sockets, $write = NULL, $except = NULL, NULL);
		 
			foreach($changed_sockets as $socket) 
			{
				if ($socket == $this->masterSocket) {
					if (($client = socket_accept($this->masterSocket)) < 0) {
						$this->logger->log("[SocketServer] "."socket_accept() failed: reason: " . socket_strerror($socket));
						continue;
					} else {
						
						// NEW CLIENT HAS CONNECTED
						$this->currentSockets[] = $client;
						socket_getpeername($client, $newClientAddress);
						$this->logger->log("[SocketServer] "."NEW CLIENT ".$client." [IP: ".$newClientAddress."]");						
					}
				} else {
		 			$bytes = socket_recv($socket, $buffer, 4096, 0);
		 
					if ($bytes == 0) {
						
						// CLIENT HAS DISCONNECTED
						$this->logger->log("[SocketServer] "."REMOVING CLIENT ".$socket);
						$index = array_search($socket, $this->currentSockets);
						
						// Remove Socket from List
						unset($this->currentSockets[$index]);				
						
						$this->removePlayerFromGame($socket);
						
						socket_close($socket);
					
					}else{
						
						// CLIENT HAS SENT DATA
						$this->parseRequest($socket, $buffer);
					}
				}
			}
		}
	}
}
?>