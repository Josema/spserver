<?php

///////////////////////////////////////////////////////////////////////////////////////
//
//  Create a new instance with the data of client connected
//
//	2011/VII/22
//
///////////////////////////////////////////////////////////////////////////////////////


namespace spserver\core;


class Client
{


    /////////////////
    //	VARIABLES  //
    /////////////////

    /**
	 * The resource of client
	 * @var resource
	 */
	protected $reource;
    
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
	public $timeout;
	
	/**
	 * The array with ids group
	 * @var Array
	 */
	public $groups = array();
	

	/**
	 * The time in milisecond of the last sent to client
	 * @var int
	 */
	public $lastSent = 0;


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
        $this->resource = $resource;
	    $this->id = $id;
        $this->idSocket = $idSocket;
        $this->ip = $ip;
	}
	
	
	/**
	 * The master socket
	 * @return resource
	 */
    public function resource()
	{
	    return $this->resource;
	}	


	
	/**
	 * Id socket client
	 * @return int
	 */
    public function id()
	{
	    return $this->id;
	}
	

	
	/**
	 * Id master socket
	 * @return int
	 */
    public function idSocket()
	{
	    return $this->idSocket;
	}
	
	
	
	/**
	 * Ip of client connected
	 * @return string
	 */
    public function ip()
	{
	    return $this->ip;
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