<?
/**
 * Base64 Online
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

 
// Instance of Server (ip and port) if the ip is 0, listens in all interfaces
$server = new pserver('0','10066');
// Set Welcome Data
$server->welcome_send = true;
$server->welcome_data = $server->_colorshell(32 , "Welcome to Base64 Online Server\n\r").$server->_colorshell(36 , "this is an example of using the class server pserver").$server->_colorshell(37,"\n\r");
$server->start();
?>
