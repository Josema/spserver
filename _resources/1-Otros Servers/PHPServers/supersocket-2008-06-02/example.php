<?php

/*

Simple "echo back" TCP server.

*/

include("supersocket.class.php");

##### FIRST WITH OUR CALLBACKS #####
function newdata($socket_id, $channel_id, $buffer, &$obj)
	{
		$obj->write($socket_id, $channel_id, $buffer);
	};

$socket = new SuperSocket(array("127.0.0.1:4041")); // will listen on ALL IPs over port 10000
$socket->assign_callback("DATA_SOCKET_CHANNEL", "newdata");
$socket->start();
$socket->loop();

?>