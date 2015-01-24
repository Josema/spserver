<?php
class Game
{
	public static $MAX_PLAYERS = 99;
	
	var $id;
	var $players;
	var $objects;
	var $properties;
	
	var $socketServer;

	// Constructor
	function __construct($gameID, $server)
	{
		$this->id = $gameID;
		$this->players = array();
		$this->objects = array();
		$this->properties = array();

		$this->socketServer = $server;
	}
	
	public function createPlayer($socket, $properties)
	{
		if(count($this->players) >= Game::$MAX_PLAYERS)
			throw new Exception("Too Many Players");
			
		$myNewID = str_pad(count($this->players),2,"0",STR_PAD_LEFT);
		
		$myPlayer = new Player($myNewID, $socket, $properties);
		
		return $myPlayer;
	}
	
	public function removePlayer($socket)
	{
		// Did we find the player?
		if(!array_key_exists($socket*1,$this->players))
			return false;
		
		$myPlayer = $this->players[$socket];
		
		unset($this->players[$socket]);
		
		$this->socketServer->sendMessage($this->getPlayerSockets(), $this->buildMessage("PlayerDisconnected",$myPlayer));
		
		return true;
	}
	
	public function interpretRequest($socket, $type, $data)
	{
		try
		{
			switch($type)
			{
				case "JoinGame":
				
					$myPlayer = $this->createPlayer($socket, $data);
					
					// Give Player Confirmation
					$this->socketServer->sendMessage(array($socket), $this->buildMessage("JoinGameConfirmation",$myPlayer));
					
					// Tell Player About Other Players
					foreach ($this->players as $player) {
						$this->socketServer->sendMessage(array($socket), $this->buildMessage("PlayerJoined",$player));
					}
					
					// Tell Other Players about New Player
					$this->socketServer->sendMessage($this->getPlayerSockets(), $this->buildMessage("PlayerJoined",$myPlayer));
					
					// Add New Player to Existing Player list.
					$this->players[$socket] = $myPlayer;
								
				break;
				
				case "ChangePlayerState":
					$myPlayer = $this->players[$socket];
					
					if(!$myPlayer)
						throw new Exception("No Such User");
						
					$myPlayer->setProperties($data);
					
					$this->socketServer->sendMessage($this->getPlayerSockets($socket), $this->buildMessage("PlayerStateChanged",$myPlayer));
					
										
				break;
				
				case "CreateObject":
					$myPlayer = $this->players[$socket];
					
					if(!$myPlayer)
						throw new Exception("No Such User");
						
					$myObject = new GameObject($data->objectID,$myPlayer->id,$data->properties);
					
					// Tell Everyone Else about the Object
					$this->socketServer->sendMessage($this->getPlayerSockets($socket), $this->buildMessage("ObjectCreated",$myObject));
					
					$this->objects[$data->objectID.""] = $myObject;
					
 				break;
				
				case "ChangeObjectState":
					
					$myPlayer = $this->players[$socket];
					
					if(!$myPlayer)
						throw new Exception("No Such User");
						
					$myObject = $this->objects[$data->objectID.""];
					
					// Tell Everyone Else about the Object
					$this->socketServer->sendMessage($this->getPlayerSockets($socket), $this->buildMessage("ObjectStateChanged",$myObject));
		
				break;
			}
		}
		catch (Exception $e)
		{
			$this->socketServer->sendMessage(array($socket), $this->buildMessage("Error",$e->getMessage()));
		}
	}
	
	private function buildMessage($type, $data)
	{
		$myMessage = "<response><type>".$type."</type><data>";
		
		switch($type) {
		
			case "JoinGameConfirmation":
				$myMessage .= "<player_id>".$data->id."</player_id>";
			break;
			
			case "PlayerDisconnected":
				$myMessage .= "<player_id>".$data->id."</player_id>";
			break;
			
			case "PlayerJoined":
				$myMessage .= "<player_id>".$data->id."</player_id>";
				$myMessage .= "<properties>";
				$myMessage .= $data->getChangedPropertiesXML();
				$myMessage .= "</properties>";
			break;
			
			case "PlayerStateChanged":
				$myMessage .= "<player_id>".$data->id."</player_id>";
				$myMessage .= "<properties>";
				$myMessage .= $data->getChangedPropertiesXML();
				$myMessage .= "</properties>";
			break;
			
			case "ObjectCreated":
				$myMessage .= "<player_id>".$data->playerID."</player_id>";
				$myMessage .= "<object_id>".$data->id."</object_id>";
				$myMessage .= "<properties>";
				$myMessage .= $data->getChangedPropertiesXML();
				$myMessage .= "</properties>";
			break;
			
			case "ObjectStateChanged":
				$myMessage .= "<player_id>".$data->playerID."</player_id>";
				$myMessage .= "<object_id>".$data->id."</object_id>";
				$myMessage .= "<properties>";
				$myMessage .= $data->getChangedPropertiesXML();
				$myMessage .= "</properties>";
			break;
			
			case "Error":
				$myMessage .= "<message>".$data."</message>";
			break;

		}
		
		$myMessage .= "</data></response>";
		
		return $myMessage;
	}
	
	private function getPlayerSockets($except = NULL)
	{
		$sockets = array();
		
		foreach ($this->players as $player) {
			if($player->socket != $except)
				$sockets[] = $player->socket;
		}	
		
		return $sockets;
	}
}
?>