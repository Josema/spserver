<?php
class GameObject
{
	var $id;
	var $playerID;
	var $properties;
	
	var $lastChanged;

	// Constructor
	function __construct($objectID, $playerID, $properties)
	{
		$this->id = $objectID;
		$this->playerID = $playerID;
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