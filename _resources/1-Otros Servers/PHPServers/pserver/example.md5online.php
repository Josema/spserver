<?
/**
 * MD5 Online
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

 
class md5server extends pserver
{		
	/**
	* Function responsible for managing the customer in read
	* In this example reads and writes in an array
	*/
	function read($client)
	{		
		$this->data[ $client['n'] ] = md5(trim(@socket_read($client['socket'], $this->bufferin, (  $this->socketbinary ?  PHP_BINARY_READ : PHP_NORMAL_READ  ) )))."\n\r";				
	}	
	
}


$server = new md5server('0','10066');
$server->welcome_send = true;
$server->welcome_data = $server->_colorshell(32 , "Welcome to MD5 Online Server\n\r").$server->_colorshell(36 , "this is an example of using the class server pserver").$server->_colorshell(37,"\n\r");
$server->start();

?>
