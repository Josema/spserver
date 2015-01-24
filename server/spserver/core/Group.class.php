<?php

///////////////////////////////////////////////////////////////////////////////////////
//
//  Create a new group for send message to a group
//
//	2011/VIII/5
//
///////////////////////////////////////////////////////////////////////////////////////


namespace spserver\core;

require_once dirname(__FILE__) . '/../util/Encapsulation.class.php';
require_once dirname(__FILE__) . '/../util/Error.class.php';
require_once dirname(__FILE__) . '/Server.class.php';

use spserver\util\Encapsulation;
use spserver\util\Error;
use spserver\core\Server;


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