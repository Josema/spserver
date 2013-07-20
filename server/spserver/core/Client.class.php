<?php

///////////////////////////////////////////////////////////////////////////////////////
//
//  Create a new instance with the data of client connected
//
//	2011/VII/22
//
///////////////////////////////////////////////////////////////////////////////////////


namespace spserver\core;

use spserver\util\Encapsulation;


class Client extends Encapsulation
{


	//////////////////
	//	PROPERTIES  //
	//////////////////

	/**
	 * The resource of client
	 * @var resource
	 */
	protected $resource;
	
	/**
	 * The id of the client
	 * @var int
	 */
	protected $id;
	
	/**
	 * The id of the sockets listener on the core
	 * @var int
	 */
	protected $idSocket;
	
	/**
	 * The ip of conection client
	 * @var string
	 */
	protected $ip;
	
	/**
	 * The time() of the his last acction. To know timeout for disconnect
	 * @var int
	 */
	protected  $timeout;

	/**
	 * The time in milisecond of the last sent to client
	 * @var int
	 */
	protected $lastSent = 0;
	
	/**
	 * The array with ids group
	 * @var Array
	 */
	public $groups = array();


	///////////////
	//	METHODS  //
	///////////////

	/**
	 * Create instance of data Client
	 * 
	 * @param resource $resource
	 * @param int $id
	 * @param int $idSocket
	 * @param string $ip
	 * 
	 */
	public function __construct($resource, $id, $idSocket, $ip=NULL)
	{
		//Encapsulation
		$this->addGet('resource');
		$this->addGet('id');
		$this->addGet('idSocket');
		$this->addGet('ip');
		$this->addGet('timeout');
		$this->addSet('timeout');
		$this->addGet('lastSent');
		$this->addSet('lastSent');

		
		
		$this->resource = $resource;
		$this->id = $id;
		$this->idSocket = $idSocket;
		$this->ip = $ip;
	}
	
	
	
	/**
	 * Return true if this client is in $idGroup
	 * 
	 * @param int $idGroup
	 * @return Boolean
	 */
	public function isInGroup($idGroup)
	{
		return array_key_exists($idGroup, $this->groups);
	}
}
?>