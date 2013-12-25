#!/usr/bin/php
<?php

// This script is included as an example only, it will definitely not work for you unmodified
// The idea is to have a list of active socket servers in a database, and then run this script on an interval via a cron job
// this script checks the list of socket servers, and starts any of them that are not currently running.
// this comes in pretty handy if your physical server restarts unexpectidly, or if you want 
// to add socket servers without having to start them manually


set_time_limit(0);

$connection = new mysqli('localhost', 'user', 'password', 'database_name');
if (!$connection) {
	throw new Exception('Could not connect to the database.');
}

$result = $connection->query("select * from servers
								where active = 1");
if(!$result) {
	throw new Exception('Could not retrieve servers from database');
}
if($result->num_rows == 0) {
	output('There are no active servers to check.');
}

while($row = $result->fetch_object()){
	check_server($row);
}





//--- start a server if it is not already running ---
function check_server($row) {
	$server_id = $row->server_id;
	$db_id = $row->db_id;
	$script = $row->script;
	$address = $row->address;
	$user_port = $row->user_port;
	$query_port = $row->query_port;
	$admin_port = $row->admin_port;
	$encryption_key = $row->encryption_key;
	$server_name = $row->server_name;
	
	$obj = new stdClass();
	$obj->type = 'get_status';
	$obj->write_num = 1;
	$send_message = json_encode($obj).chr(0x04);
	
	output("checking server $server_name($server_id)");
	
	$start_server = false;
	
	$fsock = @fsockopen($address, $admin_port, $errno, $errstr, 10);
	if($fsock) {
		stream_set_timeout($fsock, 10);
		fputs($fsock, $send_message);
		$received_message = fread($fsock, 9999);
		$info = stream_get_meta_data($fsock);
		fclose($fsock);
		
		if($info['timed_out']) {
			output('connection timed out');
			$start_server = true;
		}
		else{
			$rec_obj = json_decode(substr($received_message, 0, strlen($received_message)-1));
			$status = $rec_obj->status;
			if($status != 'running' && $status != 'shutdown') {
				output("bad response: $received_message");
				$start_server = true;
			}
			else {
				output("good response: $received_message");
			}
		}
	}
	else{
		output("can't connect");
		$start_server = true;
	}
	
	if($start_server) {
		output("starting server $server_name($server_id)");
		$log = '/home/jiggmin/log/blossom/server_'. $server_id .'_'. time() .'.txt &';
		$exec_str = "$script $address $user_port $query_port $admin_port $encryption_key $db_id > $log";
		
		output($exec_str);
		$exec_result = exec($exec_str);
		output($exec_result);
	}
}


//use \n if you're running through cron or ssh, or <br/> if you're running from a browser
function output($str) {
	echo $str ."\n";
	//echo $str ."<br/>";
}

?>