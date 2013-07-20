<?php

///////////////////////////////////////////////////////////////////////////////////////
//
//  Create a new group for send message to a group
//
//	2011/VIII/5
//
///////////////////////////////////////////////////////////////////////////////////////


namespace spserver\core;
use spserver\util\Error;
use spserver\core\Server;
use spserver\util\Encapsulation;


class Group extends Encapsulation
{


	//////////////////
	//	PROPERTIES  //
	//////////////////

	/**
	 * The id of the server
	 * @var Server
	 */
	protected $server;
	
	/**
	 * The id of the group
	 * @var int
	 */
	protected $id;
	
	/**
	 * The id of the sockets listener on the core
	 * @var int
	 */
	protected $idSocket;
	
	/**
	 * The array of ids the clients
	 * @var array
	 */
	protected $clients;





	///////////////
	//	METHODS  //
	///////////////

	/**
	 * Create instance of data Group
	 * 
	 * @param Server $server
	 * @param int $id
	 * @param int $idSocket
	 * 
	 */
	public function __construct($idSocket)
	{
		//Encapsulation
		$this->addGet('server');
		$this->addSet('server');
		$this->addGet('id');
		$this->addSet('id');
		$this->addGet('idSocket');


		$this->idSocket = $idSocket;
	}
	
	
	/**
	 * Add a new client on this group
	 * 
	 * @param int $idClient
	 * @return void
	 */
	public function addClient($idClient)
	{
		if (!isset($this->clients[$idClient]) && $this->server->client($idClient)->idSocket == $this->idSocket)
		{
			$this->clients[$idClient] = true;
			$this->server->client($idClient)->groups[$this->id] = true;
		}
		else
			Error::NOTICE(20);
	}
	
	
	/**
	 * Remove new client on this group
	 * @param int $idClient
	 * @return void
	 */
	public function removeClient($idClient)
	{
		if (isset($this->clients[$idClient]))
		{
			unset($this->clients[$idClient]);
			unset($this->server->client($idClient)->groups[$this->id]);
		}
	}
	
	
	/**
	 * Send a message to all clients on this group
	 * 
	 * @param string $data
	 * @return void
	 */
	public function send($data)
	{
		foreach ($this->clients as $idClient => $true)
			$this->server->send($idClient, $data);
	}
}
?>