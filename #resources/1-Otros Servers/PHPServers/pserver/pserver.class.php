<?php
/**
 * PServer
 *
 * Class in PHP to create new Server Socket BSD
 *
 * @package pserver
 * @subpackage pserver.class
 * @author Pedro Vargas (deer@deerme.org) http://deerme.org
 * @version 0.2
 * @licence GNU Free Documentation License (FDL)
 */

class pserver
{
	
	/**
	 * @var $version
	 * Version of Pserver
	*/
	var $version = "0.2";
	/**
	 * @var $ip 
	 * This is the ip of server
	*/
	var $ip;
	/**
	 * @var $port 
	 * This is the port of server
	*/
	var $port;
	
	/**
	 * @var $sock 
	 * This is the socket listen
	*/
	var $sock;
	
	/**
	 * @var $maxc 
	 * Maxime client connect
	*/
	var $maxc = 20;
	
	/**
	 * @var $clients 
	 * Clients connect to Server
	*/
	var $clients;
	
	/**
	 * @var $send_msg_welcome 
	 * Send welcome msg
	*/
	var $welcome_send = true;
	
	/**
	 * @var $msg_welcome 
	 * Message welcome
	*/
	var $welcome_data = "Welcome my Friend\n\r";
	/**
	 * @var $msg_full 
	 * Message to many clients connnected
	*/
	
	/**
	 * @var $send_msg_full 
	 * Send full msg
	*/
	var $full_send = true;
	
	var $full_welcome = "To many Clients connected!\n\r";
	/**
	 * @var $bufferin 
	 * Buffer in
	*/
	var $bufferin = "1024";
	/**
	 * @var $pids 
	 * Pids of the proceess
	*/
	var $pids;

	/**
	 * @var $auto_read 
	 * Auto Read
	*/
	var $auto_read = true;
	
	/**
	 * @var $auto_write 
	 * Auto Write
	*/
	var $auto_write = true;
	/**
	 * @var $socketbinary 
	 * Socket Binary or Normal
	*/
	var $socketbinary = false;
	
	/**
	* Constructor of pserver Class
	*
	* @param string $ip This is the ip of server
	* @param string $port This is the port of server
	*/
	function pserver( $ip = '0' , $port = '30000' )
	{
		$this->ip = $ip;
		$this->port = $port;		
		set_time_limit(0);		
	}	
	/**
	* Start the Server
	* 
	* @return boolean true If possible run the Server
	*/
	function start()
	{
		if ( $this->open_socket() )
		{
			$this->_logger("Start Server","Listen in ".$this->ip.":".$this->port."");
			$this->pids = array();
			while( 1 == 1 )
			{
				
				$read[0] = $this->sock;
				for($i=1; $i<count($this->clients)+1; ++$i)
				{
					
					if($this->clients[$i] != NULL)
					{
						
						$read[$i+1] = $this->clients[$i]['socket'];
					}
				}
				$ready = @socket_select($read, $write = NULL, $except = NULL, $tv_sec = NULL);
				if(in_array($this->sock, $read))
				{
					for($i=1; $i < ($this->maxc+1); $i++)
					{						
						if(!isset($this->clients[$i]))
						{
							$this->clients[$i]['socket'] = socket_accept($this->sock);
							socket_getpeername($this->clients[$i]['socket'],$ip);
							$this->clients[$i]['ip'] = $ip;							
							$this->clients[$i]['hash'] = md5( uniqid($ip,true));							
							$this->clients[$i]['n'] = $i;														
							
							if ( $this->welcome_send )
								$this->write( $this->clients[$i] , $this->welcome_data );
								
							$this->_logger("New Client from",$this->clients[$i]['ip']." - ".$this->clients[$i]['hash']  );							
							break ;
						}
						elseif($i == $this->maxc - 1)
						{
							
							$skt = socket_accept($this->sock);		
							@socket_getpeername($skt,$ip);
							$this->_logger("To many Clients connected", $ip );
							
							if ( $this->full_send )
								@socket_write($skt ,  $this->full_data);
							@socket_close($skt);														
						}						
						if($ready < 1)
						{
							continue;
						}						
					}					
				}				
				for($i=1; $i<($this->maxc+1); ++$i)
				{
					if(in_array($this->clients[$i]['socket'], $read))
					{						
						if ( $this->auto_read )
						{							
							$this->read( $this->clients[$i] );
						}
						if ( $this->auto_write )
							$this->write( $this->clients[$i] , $this->data[ $this->clients[$i]['n'] ] );
					}					
				}
				usleep(10000);
			}			
		}		
	}
	/**
	* Open the Socket Listen
	* 
	* @return boolean true If possible opened socket
	*/
	function open_socket()
	{
		$sock = &$this->sock;
		// Open
		if ( ($sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) == false  )
		{			
			$this->_logger("Start Server","socket_create() failed: reason:".@socket_strerror( socket_last_error($sock)) );
			die();
		}		
		// Binding 
		if ( ($ret = @socket_bind($sock, $this->ip, $this->port)) == false )
		{
			$this->_logger("Start Server","socket_bind() failed: reason: " . @socket_strerror( socket_last_error($sock)) );
			die();
		}
		// Listing 
		if ( ($ret = @socket_listen($sock, (int)$this->maxc   ))  == false )
		{
			$this->_logger("Start Server","socket_listen() failed: reason: ". @socket_strerror( socket_last_error($sock)) );
			die();
		}
		
		
		$this->clients = array('0' => array('socket' => &$sock , 'time' => time() ));
		
		return true;
	}	
	/**
	* Charge of closing the connection to customer
	*/
	function client_close( &$client  )
	{
		if ( $client["hash"] )
		{
			$this->_logger("Client Disconnect","".$client['ip']." - ".$client['hash']." reason: " .@socket_strerror( socket_last_error($client['socket']) )  );    
			@socket_close( $client['socket'] );	
			unset( $this->clients[  $client['n']  ] );		
		}
	}	
	/**
	* Responsible for sending a broadcast write
	*/
	function client_broadcast( $data  )
	{
		foreach( $this->clients as $k => $client )
		{
			$this->write( $client , $data );
		}
	}	
	/**
	* Function responsible for managing the customer in read
	* This method should be overridden if you want to change behavior in a child class
	* In this example reads and writes in an array
	*/
	function read($client)
	{		
		$this->data[ $client['n'] ] = base64_encode(trim(@socket_read($client['socket'], $this->bufferin, (  $this->socketbinary ?  PHP_BINARY_READ : PHP_NORMAL_READ  ) )))."\n\r";				
	}	
	/**
	* Function responsible for managing the customer in write
	* This method should be overridden if you want to change behavior in a child class
	* In this example an echo base64
	*/
	function write($client , $data = NULL)
	{
		$lenght = false;
		if ( $data == NULL )
			$lenght = @socket_write($client['socket'], $this->data[  $client['n']  ]  );
		else
			$lenght = @socket_write($client['socket'], $data  );			
		
		if ( $lenght === FALSE )
			$this->client_close($client);
		
	}	
	/**
	* Auxilary Function  - Logger information of the Server
	*/
	function _logger( $area , $msg )
	{
		print date("[Y-m-d H:i:s]")."\t".$area."\t".$msg."\n\r";
	}	
	/**
		Auxilary Function - ASCII Color
	*/
	function _colorshell($c,$t)
	{
        return sprintf("%c[%d;%d;%dm".$t,27,1, $c ,40);
	}	
}
?>