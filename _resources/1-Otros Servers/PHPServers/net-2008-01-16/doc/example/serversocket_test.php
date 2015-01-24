<?php
/*
 * Created on 28/12/2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once("../../Socket.php");
require_once("../../ServerSocket.php");
require_once("../../URL.php");

error_reporting(E_ALL);
try {
	/*
	 * Server Socket Test
	 */
	echo "Init Server";
	$server = new ServerSocket(9000);
	
	for ($i = 0;$i < 5;$i++) {
		$client = new Socket("localhost", 9000);
		$client->write("Hi server.");
	}
	$client = new Socket("localhost", 9000);
	$client->write("quit");
	
	$i = 0;
	while (true) {
		$socket = $server->accept();
		if ($socket != null) {
			$data = $socket->read();
			$i++;
			echo "<br>Client: ".$i;
			echo "<br>Info Local - IP: ".$socket->getLocalAddress()->getAddress()." PORT:".$socket->getLocalPort();
			echo "<br>Info Remote - IP: ".$socket->getInetAddress()->getAddress()." PORT:".$socket->getPort();
			echo "<br>Data - ".$data;
			echo "<br>";
			$socket->close();
			if ($data == "quit")
				break;
		}
	}
	$server->close();
	/**/
} catch (SocketException $se) {
	echo $se;
} catch (UnknownHostException $uhe) {
	echo $uhe;
} catch (Exception $e) {
	echo $e;
}
?>
