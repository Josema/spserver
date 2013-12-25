#!/usr/bin/php
<?php

require_once('blossom_server/BlossomServer.php');


//change to whatever localhost is
$address = 'localhost'; 

//the port that users will connect to
$user_port = 8081; 



//traffic sent between the users and this server will be encrypted with this key
$encryption_key = 'eWFDMU42UVRJd1JBRmYxWg==';



$server = new BlossomServer();

$server->connect($address, $user_port, 'UserSocket');
$server->listen($address, $query_port, 'LocalQuerySocket');
//$server->listen($address, $admin_port, 'AdminSocket', false);

$server->set_key($encryption_key);
//$server->set_query_server_path("/home/jiggmin/blossom/query_server.php $address $query_port $db_id > /dev/null &");

$server->start();

?>