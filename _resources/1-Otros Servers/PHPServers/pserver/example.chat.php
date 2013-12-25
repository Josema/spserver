<?php
/**
 * Chat PHP
 *
 * Thiss Class in a example of a Server create using class pserver
 *
 * @package pserver
 * @subpackage pserver.example
 * @author Pedro Vargas (deer@deerme.org) http://deerme.org
 * @version 0.2
 * @licence GNU Free Documentation License (FDL)
 */
require('pserver.class.php'); 

 
class chat extends pserver
{
	//Deactivate the automatic writing
	var $auto_write = false;	

	/**
	* Function responsible for managing the customer in read
	* In this example reads and writes in an array
	*/
	function read($client)
	{
		// Read Data
		$this->data[ $client['n'] ] = trim(@socket_read($client['socket'], $this->bufferin, (  $this->socketbinary ?  PHP_BINARY_READ : PHP_NORMAL_READ  ) ));
		if ( $this->data[ $client['n'] ] != "" )
		{
			if ( $this->data[ $client['n'] ] == ":clock" )
			{
				$this->write(  $client , $this->_colorshell(33,"[SERVER] says : ").$this->_colorshell(37,  date("Y-m-d H:i:s")  )."\n\r"  );
			}
			elseif( $this->data[ $client['n'] ] == ":w" )
			{
				$this->write(  $client , $this->_colorshell(33,"[SERVER] says : ").$this->_colorshell(37,   str_replace( array("\n") , array("\n\r")  ,  print_r($this->clients,1) )   )."\n\r"  );
			}
			elseif( $this->data[ $client['n'] ] == ":quit" )
			{
				$this->client_broadcast( $this->_colorshell(33,"[SERVER] says : ").$this->_colorshell(37,  "Client ".$client["ip"]." disconnect at ".date("Y-m-d H:i:s")." "   )."\n\r"  );
				$this->client_close( $client );
				
			}
			elseif( $this->data[ $client['n'] ] == ":clear" )
			{
				for( $i=0;$i<=100;$i++ )
				{
					$this->write(  $client , $this->_colorshell(33,"[SERVER] says : ").$this->_colorshell(37,  "Clear ... "    )."\n\r"  );
				}
			}
			elseif( $this->data[ $client['n'] ] == ":funny" )
			{
				$xhtml = @file_get_contents("http://www.chistes.com/ChisteAlAzar.asp");
				if ( preg_match('|\<div class\=\"chiste\"\>(.*?)\<\/div\>|is', $xhtml , $cap ) )
				{
					$this->client_broadcast( $this->_colorshell(33,"[SERVER] says : ").$this->_colorshell(37, utf8_decode(strip_tags($cap[1]))  )."\n\r"  );
				}
			}			
			else
			{
				foreach( $this->clients as $k => $v )
				{
					$this->write(  $this->clients[$k] , $this->_colorshell(32,"[".$client["ip"]."] says : ").$this->_colorshell(37, $this->data[ $client['n'] ] )."\n\r"  );
				}
			}
		}
		
		
	}	
}

/*
	This chat allows communication between all users connected, besides a couple of functions implemented in the chat (:w :quit :clock :funny)
*/
$server = new chat('127.0.0.1','4041');
$server->welcome_data = $server->_colorshell(32,"Welcome to the Chat \n\r").$server->_colorshell(37,'');
$server->start();

?>
