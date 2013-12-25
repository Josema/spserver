<?php
require_once("../../Socket.php");
require_once("../../URL.php");

error_reporting(E_ALL);
try {
	/* Test of the Socket creation - connect with HTTP server and read
	 * contents.
	$socket = new Socket();
	$socket->connect("www2.fsb.com.br",80);
	if ($socket->isConnected())
		echo "Connected...";
	$socket->write("GET / HTTP/1.1\r\n",20);
	echo $socket->read(20);
	*/
	
	
	/* Test of the URL getting content
	 */
	$url = new URL("www.google.com.br/");
	//** Url simple tests
	//echo $url->getExternalForm();
	//echo $url->getContent();
	//**
	$conn = $url->openConnection();
	$conn->connect();
	print_r($conn->getRequestProperties());
	echo $conn->getContentLength()." - ".$conn->getContentEncoding();
	echo $conn->getContent();
	/**/
} catch (SocketException $se) {
	echo $se;
} catch (UnknownHostException $uhe) {
	echo $uhe;
} catch (Exception $e) {
	echo $e;
}
?>