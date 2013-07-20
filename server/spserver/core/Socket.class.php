<?php

///////////////////////////////////////////////////////////////////////////////////////
//
//	Listeners instance all of a server socket
//
//	2011/VII/22
//
///////////////////////////////////////////////////////////////////////////////////////


namespace spserver\core;

require_once dirname(__FILE__) . '/../util/Encapsulation.class.php';
require_once dirname(__FILE__) . '/../util/Error.class.php';

use spserver\util\Encapsulation;
use spserver\util\Error;
use Exception;


class Socket extends Encapsulation
{


	/////////////////
	//	CONSTANTS  //
	/////////////////

	const MAX_BUFFER = 1024; //Max Bytes recieve from user in data
	const TIME_OUT = 300; //Seconds max to disconnect by interactivity of cliente (Timeout)
	




	//////////////////
	//	PROPERTIES  //
	//////////////////

	/**
	 * The master socket
	 * @var resource
	 */
	protected $resource;
	
	/**
	 * The id of the sockets listener on the core
	 * @var int
	 */
	protected $id;
	
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
	 * The max number of ip connected to this socket
	 * @var int
	 */
	protected $maxIpRepeat;
	
	/**
	 * Max buffer of data received
	 * @var int
	 */
	protected $maxBuffer;
	
	/**
	 * Timeout limit that client don't do actions for this socket
	 * @var int
	 */
	protected $timeout;
	
	/**
	 * Array containing ids clients connected to this socket
	 * @var array
	 */
	public $clients = array();

	



	///////////////
	//	METHODS  //
	///////////////
	
	/**
	 * Create instace with new socket listener
	 *  
	 * @param int $id
	 * @param string $address
	 * @param int $port
	 * @param int $maxClients
	 * @param int $maxBuffer
	 * @param int $timeout
	 * 
	 */
	public function __construct($address, $port, $maxClients=NULL, $maxIpRepeat=NULL, $maxBuffer=NULL, $timeout=NULL)
	{
		//Encapsulation
		$this->addGet('resource');
		$this->addGet('id');
		$this->addSet('id');
		$this->addGet('address');
		$this->addGet('port');
		$this->addGet('maxClients');
		$this->addGet('maxClients');
		$this->addGet('maxIpRepeat');
		$this->addSet('maxIpRepeat');
		$this->addGet('maxBuffer');
		$this->addSet('maxBuffer');
		$this->addGet('timeout');
		$this->addSet('timeout');


		try
		{

			$this->address = $address;
			$this->port = $port;
			$this->maxClients = $maxClients;
			$this->maxIpRepeat = $maxIpRepeat;
			$this->maxBuffer = ($maxBuffer==NULL) ? Socket::MAX_BUFFER : $maxBuffer;
			$this->timeout = ($timeout==NULL) ? Socket::TIME_OUT :  $timeout;
			
			// create master socket
			if (!$this->resource = socket_create(AF_INET, SOCK_STREAM, SOL_TCP))
				throw new Exception(Error::get(1), socket_last_error());

			// to prevent: address already in use
			if (!socket_set_option($this->resource, SOL_SOCKET, SO_REUSEADDR, 1))
				throw new Exception(Error::get(2), socket_last_error());

			socket_set_nonblock($this->resource);
			socket_bind($this->resource, $address, $port);
			socket_listen($this->resource);
		}
		catch (Exception $e)
		{
			Error::ERROR($e->getMessage());
			die();
		}
		
		
	}
}

?>