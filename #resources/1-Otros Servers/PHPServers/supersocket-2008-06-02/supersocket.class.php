<?php
/*********

SuperSocket Class

----------------------

Unlike other solutions that can be as simple as a single-client server and as advanced as a multiclient single
listener server, SuperSocket takes it to the next level for operation usage.

SuperSocket is a multi-socket (different ports and IPs), multiclient automated socket server that passes
actions through callbacks and automates a TCP server.

-> EVENT HANDLERS
	* NEW_SOCKET_CHANNEL ($socket_id, $channel_id, &$obj)
		- Every new connection will call on the assigned callback function within this event handler.
			- $socket_id is the socket id.
			- $channel_id is the channel id.
			- $obj is the SuperSocket object.

	* LOST_SOCKET_CHANNEL ($socket_id, $channel_id, &$obj)
		- Every lost connection will call on the assigned callback function within this event handler.
			- $socket_id is the socket id.
			- $channel_id is the channel id.
			- $obj is the SuperSocket object.

	* DATA_SOCKET_CHANNEL ($socket_id, $channel_id, $buffer, &$obj)
		- Every new buffer chunk will call on the assigned callback function within this event handler.
			- $socket_id is the socket id.
			- $channel_id is the channel id.
			- $buffer is the recieved data.
			- $obj is the SuperSocket object.

	* END_SOCKET_CHANNEL (&$obj)
		- Every end loop of socket listening will call on the assigned callback function within this event handler. Place any periodic tick functions, etc., within the callback function.
			- $obj is the SuperSocket object.

	* SERVER_STOP (&$obj)
		- Once the server stops, we will call on the assigned callback function within this event handler.
			- $obj is the SuperSocket object.

-----------------------------------------------------------------------------------------------------------

* Methods
	SuperSocket($listen = array('127.0.0.1:6667'))
		- Assign each listener within an array (string, ADDR:PORT)... ADDR may be IP address, or a wildcard ('*')
		  character
	start()
		- Start the listeners.
	stop()
		- Stop the listeners, loop, and current connections.
	loop()
		- Start the server.
	closeall($socket_id = NULL)
		- Close all (optionally, to a specific socket)
	close($socket_id, $channel_id)
		- Close a single channel
	write($socket_id, $channel_id, $buffer)
		- Write to a channel
	get_socket_info($socket_id)
		- Get information about a specific socket
	remote_address($channel_socket, &$ipaddress, &$port)
		- Get the remote address of a channel socket.
	get_raw_channel_socket($socket_id, $channel_id)
		- Get the raw socket of a channel
	new_socket_loop(&$socket)
		- Loop privately used by loop()
	recv_socket_loop(&$socket)
		- Loop privately used by loop()
	event($name)
		- Event relay
	assign_callback($name, $function_name)
		- Event callback handler
	
*********/

Class SuperSocket
	{
		var $listen = array();
		var $status_listening = FALSE;
		var $sockets = array();
		var $event_callbacks = array();
		var $recvq = 2;
		var $parent;
		
		function SuperSocket($listen = array('127.0.0.1:6667'))
			{
				$listen = array_unique($listen);
				foreach ($listen as $address)
					{
						list($address, $port) = explode(":", $address, 2);
						$this->listen[] = array("ADDR" => trim($address), "PORT" => trim($port));
					};
			}
		
		function start()
			{
				if ($this->status_listening)
					{
						return FALSE;
					};
				$this->sockets = array();
				$cursocket = 0;
				foreach ($this->listen as $listen)
					{
						if ($listen['ADDR'] == "*")
							{
								$this->sockets[$cursocket]['socket'] = socket_create_listen($listen['PORT']);
								$listen['ADDR'] = FALSE;
							}
						else
							{
								$this->sockets[$cursocket]['socket'] = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
							};
						if ($this->sockets[$cursocket]['socket'] < 0)
							{
								return FALSE;
							};
						if (@socket_bind($this->sockets[$cursocket]['socket'], $listen['ADDR'], $listen['PORT']) < 0)
							{
								return FALSE;
							};
						if (socket_listen($this->sockets[$cursocket]['socket']) < 0)
							{
								return FALSE;
							};
						if (!socket_set_option($this->sockets[$cursocket]['socket'], SOL_SOCKET, SO_REUSEADDR, 1))
							{
								return FALSE;
							};
						if (!socket_set_nonblock($this->sockets[$cursocket]['socket']))
							{
								return FALSE;
							};
						$this->sockets[$cursocket]['info'] = array("ADDR" => $listen['ADDR'], "PORT" => $listen['PORT']);
						$this->sockets[$cursocket]['channels'] = array();
						$this->sockets[$cursocket]['id'] = $cursocket;
						$cursocket++;
					};
				$this->status_listening = TRUE;
			}
		
		function new_socket_loop(&$socket)
			{
				$socket =& $this->sockets[$socket['id']];
				if ($newchannel = @socket_accept($socket['socket']))
					{
						socket_set_nonblock($newchannel);
						$socket['channels'][]['socket'] = $newchannel;
						$channel = array_pop(array_keys($socket['channels']));
						$this->remote_address($newchannel, $remote_addr, $remote_port);
						$socket['channels'][$channel]['info'] = array('ADDR' => $remote_addr, 'PORT' => $remote_port);
						$event = $this->event("NEW_SOCKET_CHANNEL");
						if ($event)
						$event($socket['id'], $channel, $this);
					};
			}
		
		function recv_socket_loop(&$socket)
			{
				$socket =& $this->sockets[$socket['id']];
				foreach ($socket['channels'] as $channel_id => $channel)
					{
						$status = @socket_recv($channel['socket'], $buffer, $this->recvq, 0);
						if ($status === 0 && $buffer === NULL)
							{
								$this->close($socket['id'], $channel_id);
							}
						elseif (!($status === FALSE && $buffer === NULL))
							{
								$event = $this->event("DATA_SOCKET_CHANNEL");
								if ($event)
								$event($socket['id'], $channel_id, $buffer, $this);
							};
					}
			}
		
		function stop()
			{
				$this->closeall();
				$this->status_listening = FALSE;
				foreach ($this->sockets as $socket_id => $socket)
					{
						socket_shutdown($socket['socket']);
						socket_close($socket['socket']);
					};
				$event = $this->event("SERVER_STOP");
				if ($event)
				$event($this);
			}
		
		function closeall($socket_id = NULL)
			{
				if ($socket_id === NULL)
					{
						foreach ($this->sockets as $socket_id => $socket)
							{
								foreach ($socket['channels'] as $channel_id => $channel)
									{
										$this->close($socket_id, $channel_id);
									}
							}
					}
				else
					{
						foreach ($this->sockets[$socket_id]['channels'] as $channel_id => $channel)
							{
								$this->close($socket_id, $channel_id);
							};
					};
			}
		
		function close($socket_id, $channel_id)
			{
				$arrOpt = array('l_onoff' => 1, 'l_linger' => 1);
				@socket_shutdown($this->sockets[$socket_id]['channels'][$channel_id]['socket']);
				@socket_close($this->sockets[$socket_id]['channels'][$channel_id]['socket']);
				$event = $this->event("LOST_SOCKET_CHANNEL");
				if ($event)
				$event($socket_id, $channel_id, $this);
			}
		
		function loop()
			{
				while ($this->status_listening)
					{
						foreach ($this->sockets as $socket)
							{
								$this->new_socket_loop($socket);
								$this->recv_socket_loop($socket);
							};
						$event = $this->event("END_SOCKET_CHANNEL");
						if ($event)
						$event($this);
					};
			}
		
		function write($socket_id, $channel_id, $buffer)
			{	
				@socket_write($this->sockets[$socket_id]['channels'][$channel_id]['socket'], $buffer);
			}
		
		function get_channel_info($socket_id, $channel_id)
			{
				return $this->sockets[$socket_id]['channels'][$channel_id]['info'];
			}
		
		function get_socket_info($socket_id)
			{
				$socket_info = $this->sockets[$socket_id]['info'];
				if (empty($socket_info['ADDR']))
					{
						$socket_info['ADDR'] = "*";
					};
				return $socket_info;
			}
		
		function get_raw_channel_socket($socket_id, $channel_id)
			{
				return $this->sockets[$socket_id]['channels'][$channel_id]['socket'];
			}
		
		function remote_address($channel_socket, &$ipaddress, &$port)
			{
				socket_getpeername($channel_socket, $ipaddress, $port);
			}
		
		function event($name)
			{
				if (isset($this->event_callbacks[$name]))
				return $this->event_callbacks[$name];
			}
		
		function assign_callback($name, $function_name)
			{
				$this->event_callbacks[$name] = $function_name;
			}
	};

?>