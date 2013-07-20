<?php

///////////////////////////////////////////////////////////////////////////////////////
//
//	Basic communications class father sockets
//
//	2011/VII/22
//
///////////////////////////////////////////////////////////////////////////////////////


namespace spserver\core;

require_once '/../events/EventDispatcher.class.php';
require_once '/../events/Event.class.php';
require_once '/../util/Timer.class.php';
require_once '/../util/Strings.class.php';
require_once 'Socket.class.php';
require_once 'Client.class.php';
require_once 'Group.class.php';

use spserver\events\EventDispatcher;
use spserver\events\Event;
use spserver\util\Timer;
use spserver\util\Strings;
use spserver\core\Socket;
use spserver\core\Client;
use spserver\core\Group;


class Server extends EventDispatcher
{


	/////////////////
	//	CONSTANTS  //
	/////////////////

	const BUCLE_TIME_LIMIT = 500; //Max miliseconds stop the loop by socket_select()
	
	
	


	//////////////////
	//	PROPERTIES  //
	//////////////////

	/**
	 * Sockets classes
	 * @var array
	 */
	protected $sockets = array();

	/**
	 * Sockets id increment
	 * @var int
	 */
	protected $incSocket = 1;

	/**
	 * Clients classes
	 * @var array
	 */
	protected $clients = array();

	 /**
	 * Clients id increment
	 * @var int
	 */
	protected $incClients = 1;

	/**
	 * Groups classes
	 * @var array
	 */
	protected $groups = array();
	
	
	/**
	 * Timers classes
	 * @var array
	 */
	protected $timers = array();

	 /**
	 * Groups id increment
	 * @var int
	 */
	protected $incGroups = 1;
	
	
	/**
	 * Timers id increment
	 * @var int
	 */
	protected $incTimers = 1;

	/**
	 * Running server in bucle start()
	 * @var Boolean
	 */
	protected $running = true;

	/**
	 * Array of all ips banned
	 * @var array
	 */
	protected $bans = array();

	/**
	 * Time Limit of the main loop, if he doesn't get any data. Max miliseconds stop the loop by socket_select()
	 * @var int
	 */
	protected $bucleTimeLimit;



	///////////////
	//	METHODS  //
	///////////////

	public function __construct($bucleTimeLimit = Server::BUCLE_TIME_LIMIT)
	{
		$this->bucleTimeLimit = $bucleTimeLimit;
	}

	/**
	 * Return a Socket class instance
	 * 
	 * @param integer|resource $socket
	 * @return Socket
	 */
	public function socket($socket)
	{
		$id = (gettype($socket) == 'integer') ? $socket : $this->getIdSocketByResource($socket);
		return (isset($this->sockets[$id])) ? $this->sockets[$id] : null;
	}


	/**
	 * Return a Client class instance
	 * 
	 * @param integer|resource $client
	 * @return Client
	 */
	public function client($client)
	{
		$id = (gettype($client) == 'integer') ? $client : $this->getIdClientByResource($client);
		return (isset($this->clients[$id])) ? $this->clients[$id] : null;
	}


	/**
	 * Return a Group class instance
	 * 
	 * @param integer $idGroup
	 * @return Group
	 */
	public function group($idGroup)
	{
		return $this->groups[$idGroup];
	}

	
	/**
	 * Return a Timer class instance
	 * 
	 * @param integer $idTimer
	 * @return Timer
	 */
	public function timer($idTimer)
	{
		return $this->timers[$idTimer];
	}


	/**
	 * Start the server
	 *  
	 * @return void
	 */
	public function start()
	{
		while ($this->running)
		{
			//Preparamos el array de listeners para iniciar la escucha
			$sockets = array();
			$clients = array();
			foreach ($this->sockets as $item)
				$sockets[] = $item->resource;
			foreach ($this->clients as $item)
				$clients[] = $item->resource;
			$listening = array_merge($sockets, $clients);


			// socket_select para el bucle y queda a la espera de recibir algun dato nuevo en los resources pasados por parametros
			// una vez que reciba algo continuará el script y el array $listening que se pasó por parametro contendra unicamente los 
			// resources que hayan sufrido cambios. Esta funcion bloquea el bucle pero una vez pasado el tiempo establecido por TIME_STOP_SELECT
			// hará que continue el bucle de igual forma aunque no hayan actualizaciones en los $listeners.
			@socket_select($listening, $write = NULL, $except = NULL, 0, ($this->bucleTimeLimit*1000));

			//Recorremos todos los resources actualizados por socket_select
			foreach ($listening as $resource)
			{
				 
				//Si el resource es de tipo Socket
				if (in_array($resource, $sockets))
				{
					$id = $this->getIdSocketByResource($resource);

					$newclient = @socket_accept($resource);
					while ($newclient !== false)
					{
						$ip = NULL;
						socket_getpeername($newclient, $ip); //Generamos ip del cliente

						//Comprobamos si la ip esta baneada o repetida excesivamente y tambien comprobamos si se ha pasado el maximo de usuarios
						if ($this->possibleNewClientByIP($ip, $id) && $this->possibleNewClientByMax($id))
						{
							$this->dispatchEvent(new Event(Event::CLIENT_CONNECT, (object) array(
								'idSocket' => $id,
								'idClient' => $this->incClients,
								'ip' => $ip,
								'resourceClient' => $newclient
							)));
						}
						else
							socket_close($newclient);
						

						$newclient = @socket_accept($resource);
					}
				}

				 
				//Si el resource es un cliente
				else 
				{
					$id = $this->getIdClientByResource($resource);
					$idSocket = $this->clients[$id]->idSocket;
					$this->clients[$id]->timeout = time();
					$data = @socket_read($resource, ($this->sockets[$idSocket]->maxBuffer+1), PHP_BINARY_READ);
					#$data = $this->readSocketForDataLength($resource, ($this->sockets[$idSocket]->maxBuffer+1));

					
					if (strlen($data) <= $this->sockets[$idSocket]->maxBuffer)
					{
						if (!$data)
							$this->removeClient($id, true);

						else
						{
							$object = (object) array(
								'idSocket' => $idSocket,
								'idClient' => $id,
								'data' => $data,
								'resourceClient' => $resource
							);
							
							if ($data == '<policy-file-request/>' . chr(0x00))
								$this->dispatchEvent(new Event(Event::CLIENT_POLICY, $object));

							else
								$this->dispatchEvent(new Event(Event::CLIENT_DATA, $object));
						}
					}
					else
					{
						$this->dispatchEvent(new Event(Event::SERVER_WARNING, (object) array(
							'idSocket' => $idSocket,
							'idClient' => $id,
							'ip' => $this->clients[$id]->ip,
							'resourceClient' => $resource,
							'code' => 11
						)));
					}
					
					unset($object);
					unset($data);
				}
			}
			
			
			// TIMERS //
			foreach ($this->timers as $timer)
			   $timer->launch();
 
			
			// TIMEOUT //
			$time = time();
			foreach ($this->clients as $client)
			{
				if (($time-$client->timeout) > $this->sockets[$client->idSocket]->timeout)
				{
					$this->dispatchEvent(new Event(Event::CLIENT_TIMEOUT, (object) array(
						'idSocket' => $client->idSocket, 
						'idClient' => $client->id, 
						'ip' => $client->ip,
						'resourceClient' => $client->resource
					)));
					$this->removeClient($client->id);
				}
			}


			// REMOVE BANS TIMEOUT //
			$time = time();
			foreach ($this->bans as $md5 => $value)
			{
				if ($value[1] != NULL && ($time-($value[1]*60) > $value[2]))
				{
					$this->removeBan($value[0]);
					$this->dispatchEvent(new Event(Event::SERVER_BANREMOVED, (object) array(
						'ip' => $value[0]
					)));
				}
			}
		}
	}
	
	
	/**
	 * Stop the server
	 * 
	 * @return void
	 */
	public function stop()
	{
		foreach ($this->sockets as $idSocket => $value)
			$this->removeSocket($idSocket);

		$this->running = false;
		exit();
	}
	

	/**
	 * Add new listening socket to a server.
	 *  
	 * @param Socket $socket
	 * @return int
	 */
	public function addSocket(Socket $socket)
	{
		$id = $this->incSocket;
		$socket->id = $id;
		$this->sockets[$id] = $socket;
		$this->incSocket += 1;

		return $id;
	}


	/**
	 * Remove socket listener by id
	 * 
	 * @param int $idSocket
	 * @param Boolean $launchEvent
	 * @return void
	 */
	public function removeSocket($idSocket, $launchEvent=false)
	{
		foreach ($this->sockets[$idSocket]->clients as $idClient => $value)
			$this->removeClient($idClient, $launchEvent);

		@socket_close($this->sockets[$idSocket]->resource);
		unset($this->sockets[$idSocket]);
	}
	

	/**
	 * Add new listening client connected from idSocket
	 *  
	 * @param Client $client
	 * @return void
	 */
	public function addClient(Client $client)
	{
		$this->clients[$client->id] = $client; //Creamos instancia
		$this->sockets[$client->idSocket]->clients[$client->id] = true; //Guardamos id del cliente en la clase socket
		$this->clients[$client->id]->timeout = time();
		$this->incClients += 1;
	}


	/**
	 * Remove client by id
	 *  
	 * @param int $idClient
	 * @param boolean $launchEvent
	 * @return void
	 */
	public function removeClient($idClient, $launchEvent=false)
	{
		if ($launchEvent)
			$this->dispatchEvent(new Event(Event::CLIENT_DISCONNECT, (object) array(
				'idSocket' => $this->clients[$idClient]->idSocket, 
				'idClient' => $idClient, 
				'ip' => $this->clients[$idClient]->ip,
				'resourceClient' => $this->clients[$idClient]->resource
			)));
		
		@socket_close($this->clients[$idClient]->resource);
		unset($this->sockets[$this->clients[$idClient]->idSocket]->clients[$idClient]);
		unset($this->clients[$idClient]);  
	}	

	
	/**
	 * Add a new Group
	 *  
	 * @param Group $group
	 * @return id
	 */
	public function addGroup(Group $group)
	{
		$id = $this->incGroups;
		$this->groups[$id] = $group;
		$this->groups[$id]->id = $id;
		$this->groups[$id]->server = $this;
		$this->incGroups += 1;
		
		return $id;
	}

	
	/**
	 * Remove Group
	 *  
	 * @param int $idGroup
	 * @return void
	 */
	public function removeGroup($idGroup)
	{
		if (isset($this->groups[$idGroup]))
			unset($this->groups[$idGroup]);
	}


	/**
	 * Add new Timer.
	 *  
	 * @param Timer $timer
	 * @return int
	 */
	public function addTimer(Timer $timer)
	{
		$id = $this->incTimers;
		$this->timers[$id] = $timer;
		$this->timers[$id]->setId($id);
		$this->incTimers += 1;

		return $id;
	}

	/**
	 * Remove Timer
	 *  
	 * @param int $idTimer
	 * @return void
	 */
	public function removeTimer($idTimer)
	{
		if (isset($this->timers[$idTimer]))
		{
			$this->timers[$idTimer]->stop();
			unset($this->timers[$idTimer]);
		}
	}
	
	/**
	 * Add a new ban with ip
	 *  
	 * @param string $ip
	 * @param int $timelimit
	 * @return void
	 */
	public function addBan($ip, $minutsBan=NULL, $automaticRemoveClients=true)
	{
		$md5 = md5($ip);
		if ((Strings::IPv4($ip) || Strings::IPv6($ip)) && !isset($this->bans[$md5]))
			$this->bans[$md5] = array($ip, $minutsBan, time());

		if ($automaticRemoveClients)
		{
			$idClients = $this->getIdClientsByIp($ip);
			foreach ($idClients as $idClient)
				$this->removeClient($idClient, true);
		}
	}


	/**
	 * Remove a ip ban added previously
	 *  
	 * @param string $ip
	 * @return void
	 */
	public function removeBan($ip)
	{
		$md5 = md5($ip);
		if (isset($this->bans[$md5]))
			unset($this->bans[$md5]);
	}


	/**
	 * Send data to a client
	 * 
	 * @param integer|resource $client
	 * @param string $data
	 * @return void
	 */
	public function send($client, $data)
	{
		if (gettype($client) == 'integer')
		{
			$id = $client;
			if (!isset($this->clients[$id]->lastSent))
			{
				echo "#$id";
				print_r($data);
				print_r($this->clients);
			}
			$lastSent = $this->clients[$id]->lastSent;
			$resource = $this->clients[$id]->resource;
		}
		else
		{
			$id = $this->getIdClientByResource($client);
			$lastSent = $this->clients[$id]->lastSent;
			$resource = $client;
		}

		$this->clients[$id]->lastSent = Timer::militime();
		@socket_write($resource, $data, strlen($data));
	}



	/**
	 * Send data to all clients of one socket
	 * 
	 * @param integer $socket
	 * @param string $data
	 * @return void
	 */
	public function sendBroadcast($idSocket, $data)
	{
		foreach ($this->sockets[$idSocket]->clients as $idclient => $client)
			$this->send($idclient, $data);
	}


	/**
	 * Return an array with id located
	 *  
	 * @param string $ip
	 * @param ìnt $idSocket
	 * @return array
	 */
	public function getIdClientsByIp($ip, $idSocket=NULL)
	{
		$ids = array();
		$array = ($idSocket == NULL) ? $this->clients : $this->sockets[$idSocket]->clients;
		foreach ($array as $idClient => $value)
			if ($this->clients[$idClient]->ip == $ip)
				$ids[] = $idClient;
			
		return $ids;
	}


	/**
	 * Return id of socket by resource
	 *  
	 * @param resource $resource
	 * @return int
	 */
	public function getIdSocketByResource($resource)
	{
		return $this->getIdByResource($resource, $this->sockets);
	}
	
	
	/**
	 * Return id of client by resource
	 *  
	 * @param resource $resource
	 * @return int
	 */
	public function getIdClientByResource($resource)
	{
		return $this->getIdByResource($resource, $this->clients);
	}


	/**
	 * Return id of socket or client by resource
	 *  
	 * @param resource $resource
	 * @param array $array (Array instances class)
	 * @return int
	 */
	protected function getIdByResource($resource, $array)
	{
		$id = 0;
		foreach ($array as $key => $item)
		{
			if ($item->resource == $resource)
			{
				$id = $key;
				break;
			}
		}
			
		return $id;
	}


	/**
	 * Return if the ip of parameters is possible for connect. 
	 * This method est $maxIpRepeat and bans.
	 *  
	 * @param string $ip
	 * @param int $idSocket
	 * @return boolean
	 */
	protected function possibleNewClientByIP($ip, $idSocket)
	{
		$possible = true;
		if ($ip!=NULL && $this->sockets[$idSocket]->maxIpRepeat != NULL)
		{
			$idClients = $this->getIdClientsByIp($ip, $idSocket);
			if (count($idClients) >= $this->sockets[$idSocket]->maxIpRepeat)
			{
				$this->dispatchEvent(new Event(Event::SERVER_WARNING, (object) array(
					'idSocket' => $idSocket,
					'idClient' => NULL,
					'ip' => $ip,
					'resourceClient' => NULL,
					'code' => 13
				)));
				$possible = false;
			}
		}

		if ($possible)
		{
			foreach ($this->bans as $array)
			{
				if ($array[0] == $ip)
				{
					$this->dispatchEvent(new Event(Event::SERVER_WARNING, (object) array(
						'idSocket' => NULL,
						'idClient' => NULL,
						'ip' => $ip,
						'resourceClient' => NULL,
						'code' => 14
					)));
					$possible = false;
					break;
				}
			}
		}
		
			
		return $possible;
	}


	/**
	 * Return true if max users is not execeded
	 *  
	 * @param int $idSocket
	 * @return boolean
	 */
	protected function possibleNewClientByMax($idSocket)
	{
		$possible = true;
		if ($this->sockets[$idSocket]->maxClients!=NULL && (count($this->sockets[$idSocket]->clients)+1) > $this->sockets[$idSocket]->maxClients)
		{
			$this->dispatchEvent(new Event(Event::SERVER_WARNING, (object) array(
				'idSocket' => $idSocket,
				'idClient' => NULL,
				'ip' => NULL,
				'resourceClient' => NULL,
				'code' => 12
			)));
			$possible = false;
		}
		
			
		return $possible;
	}
	
	
	
	/**
	 * Read buffer limiting
	 *  
	 * @param resource $resource
	 * @param int $max
	 * @return string
	 */
	/*
	NOT WORK
	private function readSocketForDataLength($resource, $max)
	{
		$offset = 0;
		$socketData = '';
	   
		while ($offset < $max)
		{
			if (($data = socket_read ($resource, $max-$offset, PHP_BINARY_READ)) === false)
				return false;
		   
			$dataLen = strlen ($data);
			$offset += $dataLen;
			$socketData .= $data;
		   
			if ($dataLen == 0) { break; }
		}
	
		return $socketData;
	}*/

}
?>