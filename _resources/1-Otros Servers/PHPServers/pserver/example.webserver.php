<?
/**
 * Web Server
 *
 * This Class in a example of a Web Server create using class pserver
 *
 * @package pserver
 * @subpackage pserver.example
 * @author Pedro Vargas (deer@deerme.org) http://deerme.org
 * @version 0.2
 * @licence GNU Free Documentation License (FDL)
 */

require('pserver.class.php'); 

 
class webserver extends pserver
{
		
	var $path_site = "./html";
		
	/**
	* Read data on the client
	*/
	
	function read( $client  )
	{
		$data = (trim(@socket_read($client['socket'], $this->bufferin, (  $this->socketbinary ?  PHP_BINARY_READ : PHP_NORMAL_READ  ) )));
		
		$get_headers = explode("\n", $data );
		
		$get = explode(" ", $get_headers[0] );
		
		if ( trim($get[0]) == "GET" )
		{
			if ( trim($get[2]) == "HTTP/1.1" )
			{
				if ( trim($get[1]) == "/" )
					$get[1] = "index.html";
					
				// This server only accept GET in one level				
				if ( is_file( $this->path_site . "/" . basename( $get[1] ) )  )
				{
					$this->_logger("GET" , $this->path_site . "/" . basename( $get[1] ). " ".$client["ip"] );
					$this->write( $client , file_get_contents( $this->path_site . "/" . basename( $get[1] ) ) );
				}
				else
				{
					$this->http_error(404 , "File Not Found","This File not found in Server" , true , $client);
				}
			}
			else
			{
				$this->http_error(501 , "Protocol Error","This Server only accept HTTP/1.1" , true , $client);
			}
		}
		else
		{
			$this->http_error(501, "Method Error","This Server only accept GET Method" , true , $client);
		}		
	}
	
	/**
	* Write data on the client
	*/
	function write(  &$client , $data )
	{		
		$data_return = "HTTP/1.1 200 OK
Server: Web Server extends from pserver Class /(%s) PHP (%s) by deerme.org
X-Powered-By: PServer %s in %s %s
Content-Type: text/html

$data
";
		@socket_write($client['socket'], sprintf($data_return , PHP_OS , PHP_VERSION , $this->verion , PHP_OS , PHP_VERSION )  );
		$this->client_close($client);
		
	}
	
	
	function http_error( $code ,  $title , $error , $disconnect = true , $client)
	{
		$this->_logger("HTTP". $code , $title." ".$error);
		$data_return = "HTTP/1.1 $code Not Found
Date: Wed, 24 Feb 2010 16:59:21 GMT
Server: Web Server extends from pserver Class /(%s) PHP (%s) by deerme.org
X-Powered-By: PServer %s in %s %s


";
		if ( $code == "404" )
			$data_return .= "<html><head><title>404 Not Found</title></head><h1>404 Not Found</h1></html>";
		@socket_write($client['socket'], sprintf($data_return , PHP_OS , PHP_VERSION , $this->verion , PHP_OS , PHP_VERSION )  );
		$this->client_close( $client );
	}
	
	
	
}

$ip = ($argv[1] ? $argv[1] : 0 );
$p = ($argv[2] ? (int)$argv[2] : 8080 );

$server = new webserver($ip, $p );
$server->socketbinary = true;
$server->auto_write = false;
$server->welcome_send = false;
$server->path_site = "./html";
$server->start();

?>