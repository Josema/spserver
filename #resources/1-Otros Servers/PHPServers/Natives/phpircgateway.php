#!/usr/bin/php -q
<?php
 
set_time_limit(0);
ob_implicit_flush();
 
$address = '127.0.0.1';
$port = 4041;

$_sockets = array();

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
{                              
  $started=time();             
  echo "[".date('Y-m-d H:i:s')."] SERVER CREATED ( MAXCONN:".SOMAXCONN." ) \n";
  echo "[".date('Y-m-d H:i:s')."] Listening on ".$address.":".$port."\n";
}                              
                               
$read_sockets = array($master);

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

        socket_write($socket,$bytes);
        $contents="";
       
        $index = array_search($socket, $read_sockets);
        unset($read_sockets[$index]);
        socket_shutdown($socket, 2);
        socket_close($socket);
      }
      
      elseif (( preg_match("/GET/", $buffer) || preg_match("/POST/", $buffer)) && preg_match("/HTTP/", $buffer))
      {
        if (preg_match("//server-status/i", $buffer))
        {
          $uptime = floor((time()-$started)/86400);
       
          socket_write($socket,"OK\n");
          socket_write($socket,"Clients: ".count($read_sockets)."/".SOMAXCONN."\n");
          socket_write($socket,"Created: ".date('Y-m-d H:i:s',$started)."\n");
          socket_write($socket,"Uptime: ".$uptime." days\n");
          echo "[".date('Y-m-d H:i:s')."] STATUS REQUEST\n";
        }
        elseif (preg_match("/favicon.ico/i", $buffer))
        {
          //ignore :)
        }
        else
        {
          // fake web server
          socket_write($socket,"HTTP/1.1 301 Moved Permanentlyn");
          socket_write($socket,"Server: PHP Chat Server by DjZoNe - http://djz.hu/n");
          socket_write($socket,"Date: ".date("d, j M Y G:i:s Tn"));
          socket_write($socket,"Last-Modified: ".date("d, j M Y G:i:s Tn"));
          socket_write($socket,"Location: http://djz.hu/n");
       
        }
        $index = array_search($socket, $read_sockets);
        unset($read_sockets[$index]);
        @socket_shutdown($socket, 2);
        @socket_close($socket);
      }
      
      if (strlen($buffer) == 0)
      {
        //we get the user's uniqe id from the database
        $id=$_sockets[intval($socket)]['nick'];
       
        $index = array_search($socket, $read_sockets);
       
        unset($read_sockets[$index]); // we clean up
        unset($_sockets[intval($socket)]); // we clean up our own data
        // cleaning up is essential when creating a daemon
        // we can't leave junk in the memory
        @socket_shutdown($socket, 2);
        @socket_close($socket);
       
        $allclients = $read_sockets; // reload active clients
       
        // $socket is now pointing to a dead resource id
        // but the send_Message() function will need it, I'll explain later
       
        send_Message($allclients, $socket, "");
        echo "[".date('Y-m-d H:i:s')."] QUIT ".$id."n";
      }
      
      else
      {
        $allclients = $read_sockets;
        array_shift($allclients);
       
        $piece = explode(" ",trim($buffer)); // we strip out all unwanted data
        $cmd = strtoupper($piece[0]);
      }
      
      if (!empty($piece[1])) $content = $piece[1];
       
      switch ($cmd)
      {
        case "IDENTIFY":
          $id = trim($piece[1]);
          $passwd = trim($piece[2]);
          send_Identify($allclients, $socket, $id, $passwd);
        break;
       
        case "MSG":
          $id = trim($piece[1]);
          $msg="";
          foreach ($piece as $key=>$val)
          {
            if ($key > "1") $msg.=$val." ";
          }
          $msg = trim($msg);
          send_Msg($allclients, $socket, $id, $msg);
        break;
       
        case "LIST":
          list_Users($allclients, $socket);
          break;
      }
            
    }
  }
}

function send_Identify($allclients, $socket, $id, $passwd)
{
  global $_sockets;
  $nicks = array();
 
  $dbconf = new DATABASE_CONFIG;
 
  $db_host = $dbconf->host;
  $db_base = $dbconf->database;
  $db_login = $dbconf->login;
  $db_password = $dbconf->password;
 
  foreach ($_sockets as $_socket)
  {
    foreach ($_socket as $key=>$val)
    {
      if (empty($nicks[$val])) $nicks[$val]=1;
      else $nicks[$val]=$nicks[$val]+1;
    }
  }
 
  if (empty($nicks[$id]))
  {
    $s=1;
    //  Here will be a simple authentication.
 
    $link = mysql_connect($db_host, $db_login, $db_password);
    if (!$link) die("Could not connect:" . mysql_error() . "n");
 
    $db_selected = mysql_select_db($db_base, $link);
    if (!$db_selected) die("Can't use $db_base :" . mysql_error() . "n");
 
    $result = mysql_query("SELECT nick FROM members WHERE id='".intval($id)."' AND password='".crypt($passwd)."' AND active='1' LIMIT 1");
    $data = mysql_fetch_array($result);
    $name = $data['name'];
    $_sockets[intval($socket)]=array('id'=>$id, 'nick'=>$name);
 
    mysql_free_result($result);
    mysql_close($link);
  }
  else $s=0;
 
  //   We'll answer to the flash in XML form.
  //   But we receive in plain text format.</strong></strong>
 
  if ($s == 1)
  {
    $out = ""; // yes, XML respond :)
    send_Message($allclients, $socket, "");
    // this goes to all active, identified clients
    echo "[".date('Y-m-d H:i:s')."] LOGIN ".$id."(".count($allclients)."/".SOMAXCONN.")\n";
  }
  else $out = "";
 
  socket_write($socket, $out.chr(0)); // write back to the client
}
 
function send_Msg($allclients,$socket,$id,$msg)
{
    global $_sockets;
 
    if (!empty($_sockets[intval($socket)]))
    {
        $nicks = array();
 
        foreach ($_sockets as $_socket)
        {
             foreach ($_socket as $key=>$val)
             {
                  // this check's the onliners
                  if (empty($nicks[$val])) $nicks[$val]=1;
                  else $nicks[$val]=$nicks[$val]+1; // we shouldn't have duplicated nicks, but what if...
             }
        }
 
        foreach($allclients as $client)
        {
            if (!empty($_sockets[$client]['nick']) && ($_sockets[$client]['nick'] == $id))
            {
              $_client = $client;
              $out = "";
            }
            elseif(empty($nicks[$id]))
            //not online or something similar
            {
               //backto the sender
               $_client = $socket;
               $out = "";
            }
        }
    }
    else
    {
        //backto the sender
        $_client = $socket;
        $out = "";
    }
    if (!empty($out))
    {
       socket_write($socket, $out.chr(0)); //send to back ourself. we have to handle it in flash
       socket_write($_client, $out.chr(0)); //send to the recipient
    }
}

function send_Message($allclients, $socket, $buf)
{
  global $_sockets;

  foreach($allclients as $client)
  {
    @socket_write($client, $buf.chr(0));
  }
}

function list_Users($allclients,$socket)
{
  global $_sockets;
  $out = "";
  foreach($allclients as $client)
  {
    if (!empty($_sockets[$client]['nick']) && ($_sockets[$client]['nick'] != ""))
    {
      $out .= "";
    }
  }
  $out .= "";
  socket_write($socket, $out.chr(0));
}
?>
