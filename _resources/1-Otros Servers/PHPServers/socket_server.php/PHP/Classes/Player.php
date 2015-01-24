<?php
class Player
{
	var $id;
	var $socket;
	var $properties;
	
	private $lastChanged;

	// Constructor
	function __construct($playerID, $socket, $properties)
	{
		$this->id = $playerID;
		$this->socket = $socket;
		$this->properties = array();	

		$this->lastChanged = array();
		
		$this->setProperties($properties);
	}
	
	public function setProperties($properties)
	{
		$this->lastChanged = array();
		
		foreach($properties->children() as $prop)
		{
			$this->properties[$prop->getName()] = $prop;
			$this->lastChanged[] = $prop->getName();
		}				
	}
	
	public function getChangedPropertiesXML()
	{
		$xml = "";
		
		foreach($this->properties as $prop=>$val)
		{
			if(in_array($prop,$this->lastChanged))
				$xml .= "<".$prop.">".$val."</".$prop.">";
		}
		
		return $xml;
	}
}
?>