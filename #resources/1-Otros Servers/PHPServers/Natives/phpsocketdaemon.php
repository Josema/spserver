#!/usr/bin/php -q
<?php
/**
 * PhpSocketDaemon.php
 * 
 * This is a Socket daemon written in PHP for handling socket communication
 * Originaly it's created, to handle connections from Flash(TM) clients.
 * The "basic" version was written by Raymond Fain for kirupa.com 
 * 
 * @author DjZoNe <djz@djz.hu>
 * @version 1.0
 * @package SocketDaemon
 */

/*************************************
*******       Edit this        *******
**************************************/
$address = '127.0.0.1';
$port = 4041;

/**
  * Important settings don't remove them.
  */
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();
 

$_sockets = array();

/**
 * Function to Send out Messages to Everyone Connected
 * 
 * @global string $GLOBALS['_sockets'] 
 * @name $_sockets We store sockets in the global $_sockets variable
 *  
 * @param array $allclients Contains all active client resources
 * @param string $buf Message to send out. 
 */ 
function send_Message($allclients, $buf) 
{
    global $_sockets;
    
    $out = $buf;
    
    foreach($allclients as $client) 
    {
      @socket_write($client, $out.chr(0));
    }
}

/**
 * Function to handle user identification
 * @global string $GLOBALS['_sockets'] 
 * @name $_sockets We store sockets in the global $_sockets variable
 *  
 * @param array $allclients Contains all active client resources
 * @param string $socket current socket
 * @param string $buf Message to send out. 
 */ 
function send_Identify($allclients, $socket, $nick)
{
    global $_sockets;
    /**
      * $nicks stores the nicknames
      */
    $nicks = array(); //amig fut a parancs ebben vannak a nickek
    require_once "database.php";

    $dbconf = new DATABASE_CONFIG;

    $db_host = $dbconf->default['host'];
    $db_base = $dbconf->default['database'];
    $db_login = $dbconf->default['login'];
    $db_password = $dbconf->default['password'];

    foreach ($_sockets as $_socket)
    {
      foreach ($_socket as $key=>$val)
      {
        if (empty($nicks[$val])) $nicks[$val]=1;
        else $nicks[$val]=$nicks[$val]+1;
      }
    }
    
    if (empty($nicks[$nick])) 
    /**
      * If there is no such index, we create one.
      */
    {
    	$s=1;
    	
    	/**
	  * Database connection
	  */
    	$link = mysql_connect($db_host, $db_login, $db_password);
    	if (!$link) die("Could not connect:" . mysql_error() . "\n");
        
    	$db_selected = mysql_select_db($db_base, $link);
    	if (!$db_selected) die("Can't use $db_base :" . mysql_error() . "\n");
    	
    	$result = mysql_query("SELECT CONCAT(first_name,' ',last_name) as name FROM members WHERE id='".intval($nick)."' LIMIT 1");
    	$data = mysql_fetch_array($result);
    		
    	$name = $data['name'];
    	
	/**
	  * Store personal information.
	  */
    	$_sockets[intval($socket)]=array('nick'=>$nick, 'name'=>$name);
    	
    	mysql_free_result($result);
    	mysql_close($link);
    }
    else $s=0;
    
    if ($s == 1) 
    {
    	$out = "<identify aid=\"".$nick."\" name=\"".$name."\" />";
    	send_Message($allclients, "<login aid=\"".$nick."\" name=\"".$name."\" />");
    	
    	echo "[".date('Y-m-d H:i:s')."] LOGIN ".$nick."(".count($allclients)."/".SOMAXCONN.")\n";
    }
    else $out = "<error value=\"Already online.\" />";
    
    socket_write($socket, $out.chr(0));
}

 /**
 * Function to handle nick changes
 * @global string $GLOBALS['_sockets'] 
 * @name $_sockets We store sockets in the global $_sockets variable
 *  
 * @param array $allclients Contains all active client resources
 * @param string $socket current socket
 * @param string $nick To change current nick to.
 */
function nick_Changed($allclients, $socket, $nick)
{
    $out="<nickchanged id=\"".intval($socket)."\" to=\"".$nick."\">";
    socket_write($socket, $out.chr(0));
}

 /**
 * Function to handle messages
 * @global string $GLOBALS['_sockets'] 
 * @name $_sockets We store sockets in the global $_sockets variable
 *  
 * @param array $allclients Contains all active client resources
 * @param string $socket current socket
 * @param string $nick current nick
 * @param string $msg current message
 */
function send_Msg($allclients,$socket,$nick,$msg)
{
    global $_sockets;

    if (!empty($_sockets[intval($socket)]))
    {
      /**
       * This array stores the nick names
       */             
    	$nicks = array();
    
    	foreach ($_sockets as $_socket)
    	{
    	    foreach ($_socket as $key=>$val)
    	    {
        		if (empty($nicks[$val])) $nicks[$val]=1;
        		else $nicks[$val]=$nicks[$val]+1;
    	    }
    	}
    
    	foreach($allclients as $client) 
    	{
    	    if (!empty($_sockets[$client]['nick']) && ($_sockets[$client]['nick'] == $nick))
    	    {
    	     /**
    	      * $_client will contain the recipient
    	      */
        		$_client = $client;
        		$out = "<msg aid=\"".$_sockets[$socket]['nick']."\" time=\"".date("H:i:s")."\" msg=\"".$msg."\" from=\"".$_sockets[$client]['nick']."\" />";
    	    }
    	    elseif(empty($nicks[$nick]))
    	    /**
    	     * If user is not online right now we notify the sender
    	     */               	    
    	    {
        		$_client = $socket;
        		$out = "<error value=\"User is already left.\"/>";
    	    }
    	}
    }
    else
    /**
     * Identification error.
     */         
    {
    	$_client = $socket;
    	$out = "<error value=\"Not identified.\"/>";
    }
    if (!empty($out)) 
    {
    /**
     * We send the message to ourself, and also to the recipient
     */
    	socket_write($socket, $out.chr(0));
    	socket_write($_client, $out.chr(0));
    }
}

 /**
 * Function to list active connections with names 
 * @global string $GLOBALS['_sockets'] 
 * @name $_sockets We store sockets in the global $_sockets variable
 *  
 * @param array $allclients Contains all active client resources
 * @param string $socket current socket
 */
function list_Users($allclients,$socket)
{
    global $_sockets;
    $out = "<nicklist>";
    foreach($allclients as $client)
    {
    	if (!empty($_sockets[$client]['nick']) && ($_sockets[$client]['nick'] != ""))
    	{ 
    	    $out .= "<nick aid=\"".$_sockets[$client]['nick']."\" name=\"".$_sockets[$client]['name']."\" />";
    	}
    }
    $out .= "</nicklist>";
    socket_write($socket, $out.chr(0));
}

/**
 * Here is the socket communication initializiation
 */
    if (($master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0)
    {
	   echo "socket_create() failed, reason: " . socket_strerror($master) . "\n";
    }
 
    socket_set_option($master, SOL_SOCKET,SO_REUSEADDR, 1);
 
    if (($ret = socket_bind($master, $address, $port)) < 0) 
    {
	   echo "socket_bind() failed, reason: " . socket_strerror($ret) . "\n";
    }
 
    if (($ret = socket_listen($master, 5)) < 0) 
    {
	   echo "socket_listen() failed, reason: " . socket_strerror($ret) . "\n";
    }
    else
    /**
     * The socket is opened, so let start, the uptime counter, and write out
     * some basic information to the log file.       
     */     
    {
	   $started=time();
    	echo "[".date('Y-m-d H:i:s')."] SERVER CREATED ( MAXCONN:".SOMAXCONN." ) \n";
    	echo "[".date('Y-m-d H:i:s')."] Listening on ".$address.":".$port."\n";
    }

    $read_sockets = array($master);
 
/**
 * This persistent loop will handle the messages, and deliver them to the
 * recipients 
 */ 
while (true) 
{
  $changed_sockets = $read_sockets;
  $num_changed_sockets = socket_select($changed_sockets, $write = NULL, $except = NULL, NULL);
 
  foreach($changed_sockets as $socket) 
  {
    if ($socket == $master) 
    {
    	if (($client = socket_accept($master)) < 0)
    	{
        echo "socket_accept() failed: reason: " . socket_strerror($msgsock) . "\n";
        continue;
    	}
    	else 
    	{
    	  array_push($read_sockets, $client);
    	  echo "[".date('Y-m-d H:i:s')."] CONNECTED "."(".count($read_sockets)."/".SOMAXCONN.")\n";
    	}	
    } 
    else 
    {
      $bytes = @socket_recv($socket, $buffer, 2048, 0);
    
    	if (preg_match("/policy-file-request/i", $buffer) || preg_match("/crossdomain/i", $buffer))
    	{
        echo "[".date('Y-m-d H:i:s')."] CROSSDOMAIN.XML REQUEST\n";
    
    	  $contents='<?xml version="1.0"?><cross-domain-policy><allow-access-from domain="*" to-ports="80" /></cross-domain-policy>';
    
    	  socket_write($socket,$contents);
    	  $contents="";
    
    	  $index = array_search($socket, $read_sockets);
    	  unset($read_sockets[$index]);
    	  socket_shutdown($socket, 2);
        socket_close($socket);
    	}
      elseif (( preg_match("/GET/", $buffer) || preg_match("/POST/", $buffer)) && preg_match("/HTTP/", $buffer))
	    {
    	    if (preg_match("/\/server-status/i", $buffer))
    	    {
            $uptime = floor((time()-$started)/86400);
	
        		socket_write($socket,"OK\n");
        		socket_write($socket,"Clients: ".count($read_sockets)."/".SOMAXCONN."\n");
        		socket_write($socket,"Created: ".date('Y-m-d H:i:s',$started)."\n");
        		socket_write($socket,"Uptime: ".$uptime." days\n");
        		echo "[".date('Y-m-d H:i:s')."] STATUS REQUEST\n";
	       }
	       elseif (preg_match("/favicon.ico/i", $buffer) || preg_match("/robots.txt/i", $buffer))
	       /**
	        * Simply ignore favicon.ico and robots.txt requests
	        */          	       
	       {
	       }
	       else
	       /**
	        * Let's fake a real HTTP Server Response code
	        */          	       
	       {
            socket_write($socket,"HTTP/1.1 301 Moved Permanently\n");
            socket_write($socket,"Server: PHP Chat Server by DjZoNe - http://djz.hu/\n");
            socket_write($socket,"Date: ".date("d, j M Y G:i:s T\n"));
            socket_write($socket,"Last-Modified: ".date("d, j M Y G:i:s T\n"));
            socket_write($socket,"Location: http://djz.hu/\n");
      		
      		  echo "Browser request. Data:\n";
      		  echo $buffer;
      		  echo "--- End data\n";
	       }
	       
          $index = array_search($socket, $read_sockets);
          unset($read_sockets[$index]);
          @socket_shutdown($socket, 2);
     	    @socket_close($socket);
    }
    
    if (strlen($buffer) == 0)
    {
	    $aid=$_sockets[intval($socket)]['nick'];
	
	    $index = array_search($socket, $read_sockets);
	
      /**
       * Remove from the active sockets
       */       
	    unset($read_sockets[$index]);
      /**
       * Remove userdata
       */	    
	    unset($_sockets[intval($socket)]);
      /**
       * Shutdown and close connection.
       */      	
	    @socket_shutdown($socket, 2);
     	@socket_close($socket);
     	
     	/**
     	 * Reload active clients to it's storing variable
     	 */             	
	    $allclients = $read_sockets;

	    send_Message($allclients, "<quit aid=\"".$aid."\" />");
		
	    echo "[".date('Y-m-d H:i:s')."] QUIT ".$aid."\n";
    
    }
    else
    {
      $allclients = $read_sockets;
	    array_shift($allclients);
        
	    $piece = explode(" ",trim($buffer));
    	$cmd = strtoupper(substr($piece[0],1));
	    if (!empty($piece[1])) $content = $piece[1];
        
    	switch ($cmd) 
    	{
        case "IDENTIFY":
          $nick = trim($piece[1]);
		      send_Identify($allclients, $socket, $nick);
        break;

        case "MSG":
		      $nick = trim($piece[1]);
    		  $msg="";
		      foreach ($piece as $key=>$val)
		      {
            if ($key > "1") $msg.=$val." ";
		      }
		      $msg = trim($msg);
		      send_Msg($allclients, $socket, $nick, $msg);
        break;
	  
		    case "LIST":
		      list_Users($allclients, $socket);
		    break;
    	  } //switch
      } //else
    } //else
  } //foreach
} //while
?>
