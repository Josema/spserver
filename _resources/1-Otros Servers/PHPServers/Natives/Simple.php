#!/usr/bin/php -q
<?php
 
error_reporting(E_ALL);
 
set_time_limit(0);
 
ob_implicit_flush();
 
$address = '0';
$port = 4041;

$_sockets = array();
 
//---- Function to Send out Messages to Everyone Connected ----------------------------------------
 
function send_Message($allclient, $socket, $buf) {

foreach($allclient as $client) {

socket_write($client,"$socket,$buf,");

}

}
 
 
 
//---- Start Socket creation for PHP 5 Socket Server -------------------------------------
 
if (($master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0) {

echo "socket_create() failed, reason: " . socket_strerror($master) . "\n";

}
 
socket_set_option($master, SOL_SOCKET,SO_REUSEADDR, 1);
 
 
if (($ret = socket_bind($master, $address, $port)) < 0) {

echo "socket_bind() failed, reason: " . socket_strerror($ret) . "\n";

}
 
 
if (($ret = socket_listen($master, 5)) < 0) {

echo "socket_listen() failed, reason: " . socket_strerror($ret) . "\n";

}
else
{
  $started=time();
  echo "[".date('Y-m-d H:i:s')."] SERVER CREATED ( MAXCONN:".SOMAXCONN." ) \n";
  echo "[".date('Y-m-d H:i:s')."] Listening on ".$address.":".$port."\n";
 
}
 
 
$read_sockets = array($master);
 
//---- Create Persistent Loop to continuously handle incoming socket messages ---------------------
while (true) {

$changed_sockets = $read_sockets;
 
$num_changed_sockets = socket_select($changed_sockets, $write = NULL, $except = NULL, NULL);
 
foreach($changed_sockets as $socket) {
 

if ($socket == $master) {
 

if (($client = socket_accept($master)) < 0) {

echo "socket_accept() failed: reason: " . socket_strerror($msgsock) . "\n";
continue;

} else {

array_push($read_sockets, $client);

echo "[".date('Y-m-d H:i:s')."] ".$client." CONNECTED "."(".count($read_sockets)."/".SOMAXCONN.")\n";
}

} else {
 

$bytes = socket_recv($socket, $buffer, 2048, 0);
 
if ($bytes == 0) {

$index = array_search($socket, $read_sockets);
unset($read_sockets[$index]);
@socket_shutdown($socket, 2);
@socket_close($socket);
/**
* Reload active clients to it's storing variable
*/             	
$allclients = $read_sockets;
echo "[".date('Y-m-d H:i:s')."] QUIT ".count($read_sockets)."\n";
}
else
{

$allclients = $read_sockets;
array_shift($allclients);
send_Message($allclients, $socket, $buffer);
}

}
 

}

}
?>