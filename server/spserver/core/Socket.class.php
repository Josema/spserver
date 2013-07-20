<?php

///////////////////////////////////////////////////////////////////////////////////////
//
//	Listeners instance all of a server socket
//
//	2011/VII/22
//
///////////////////////////////////////////////////////////////////////////////////////


namespace spserver\core;
use spserver\util\Error;


class Socket
{


    /////////////////
    //	CONSTANTS  //
    /////////////////

    const MAX_BUFFER = 1024; //Max Bytes recieve from user in data
    const TIME_OUT = 300; //Seconds max to disconnect by interactivity of cliente (Timeout)
    




    /////////////////
    //	VARIABLES  //
    /////////////////

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
	public $maxClients;
	
	/**
	 * The max number of ip connected to this socket
	 * @var int
	 */
	public $maxIpRepeat;
	
	/**
	 * Max buffer of data received
	 * @var int
	 */
	public $maxBuffer;
	
	/**
	 * Timeout limit that client don't do actions for this socket
	 * @var int
	 */
	public $timeout;
	
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
    		    throw new Error(socket_strerror(socket_last_error()), socket_last_error());

    		// to prevent: address already in use
    		if (!@socket_set_option($this->resource, SOL_SOCKET, SO_REUSEADDR, 1))
    		    throw new Error(socket_strerror(socket_last_error()), socket_last_error());

    		if (!@socket_set_nonblock($this->resource))
    		    throw new Error(3);

    		// bind socket to port
    		if (!@socket_bind($this->resource, $address, $port))
    		    throw new Error(4);

    		// start listening for connections
    		if (!@socket_listen($this->resource))
    		    throw new Error(5);
    		    

            return true;
	    }
        catch (Error $e)
        {
            throw new Error($e->getMessage(), $e->getCode());
            return false;
        }
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
	 * Id master socket
	 * @return int
	 */
    public function id()
	{
	    return $this->id;
	}


	/**
	 * Address connection on this socket
	 * @return String
	 */
    public function address()
	{
	    return $this->address;
	}


	/**
	 * Port of connection
	 * @return int
	 */
    public function port()
	{
	    return $this->port;
	}


	/**
	 * Id master socket
	 * @param int $idsocket
	 * @return void
	 */
    public function setId($idsocket)
	{
	    $this->id = $idsocket;
	}
}

?>