#!/usr/bin/php -Cq
<?php
ini_set('error_reporting', E_ALL);


include_once('spserver/util/Error.class.php');
include_once('spserver/util/Encapsulation.class.php');
include_once('spserver/util/AES.class.php');
include_once('spserver/util/Message.class.php');
include_once('spserver/events/Event.class.php');
include_once('spserver/events/EventDispatcher.class.php');
include_once('spserver/core/Server.class.php');
include_once('spserver/core/Socket.class.php');
include_once('spserver/core/Client.class.php');
include_once('spserver/core/Group.class.php');


use spserver\util\AES;
use spserver\util\Message;
use spserver\util\Error;
use spserver\events\Event;
use spserver\core\Server;
use spserver\core\Socket;
use spserver\core\Client;
use spserver\core\Group;


class MyServer
{
    private $server;
    private $temp;
	private $users = array();
	private $aes;
		

    public function __construct()
    {
		//$aes->setMode(MCRYPT_MODE_ECB);
		$this->aes = new AES();
		$this->aes->setKey("02306f485f385f6ed9ab6626052a633d");
		//$aes->setIV("0123456789abcdef");
	
        $this->server = new Server();
        $this->createListeners();


        $this->temp = new Socket(
            	0,   //Address
                4041,        //Port
                NULL,        //Max Clients
                NULL,        //Max Ip repeat
                2048,     	 //Max buffer bytes per data recieved
                9999         //Seconds max to disconnect by interactivity of cliente (Timeout)
        );
		

        $this->server->addSocket($this->temp);
        $this->onCreate($this->temp->id, $this->temp->address, $this->temp->port);
        $this->server->start();
    }


    private function createListeners()
    {
		$this->server->addEventListener(Event::CLIENT_DATA, array($this, 'onSend'));
        $this->server->addEventListener(Event::SERVER_ERROR, array($this, 'onError'));
        $this->server->addEventListener(Event::SERVER_WARNING, array($this, 'onWarning'));
        $this->server->addEventListener(Event::CLIENT_CONNECT, array($this, 'onConnected'));
        $this->server->addEventListener(Event::CLIENT_DISCONNECT, array($this, 'onDisconnect'));
        $this->server->addEventListener(Event::CLIENT_POLICY, array($this, 'onPolicy'));
    }



	public function onSend($e)
    {
		/*$unwrap = Message::unwrap($e->parameters->data, $this->aes);
		$wrap = Message::wrap($unwrap, $this->aes, true, true, true);
    	$this->server->send($e->parameters->idClient, $wrap);*/
		
		$this->server->send($e->parameters->idClient, $e->parameters->data);
    }
	public function onConnected($e)
    {
    	
        $this->temp = new Client($e->parameters->resourceClient, $e->parameters->idClient, $e->parameters->idSocket, $e->parameters->ip);
        $this->server->addClient($this->temp);
        $this->logger('Client connected. idS#' . $e->parameters->idSocket . ' idC#' . $e->parameters->idClient . ' IP: ' . $e->parameters->ip);
    }


    public function onError($e)
    {
        $this->logger('Error: #' . $e->parameters->code . ' ' . Error::get($e->parameters->code));
    }
    public function onWarning($e)
    {
        $this->logger('Warning: #' . $e->parameters->code . ' ' . Error::get($e->parameters->code) . ' ' . $e->parameters->ip);
        $this->server->send($e->parameters->resourceClient, Error::get($e->parameters->code));
    }
    public function onCreate($idSocket, $address, $port)
    {
        $this->logger('Creating Socket Listener at (#' . $idSocket . ') ' . $address .':'. $port);
    }
    
    public function onDisconnect($e)
    {
        $this->logger('Client Disconnected. idS#' . $e->parameters->idSocket . ' idC#' . $e->parameters->idClient . ' IP: ' . $e->parameters->ip);
    }
    public function onPolicy($e)
    {
        $this->server->send($e->parameters->idClient, "<?xml version=\"1.0\" encoding=\"UTF-8\"?><cross-domain-policy xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"http://www.adobe.com/xml/schemas/PolicyFileSocket.xsd\"><allow-access-from domain=\"*\" to-ports=\"*\" secure=\"false\" /><site-control permitted-cross-domain-policies=\"master-only\" /></cross-domain-policy>" . chr(0));
        $this->logger('Policy request #' . $e->parameters->idClient . ': ' . $e->parameters->data);
    }
    
    
    
    
    private function logger( $msg )
    {
    	echo date("[Y-m-d H:i:s][") . $this->convert(memory_get_usage(true)) . "/" .  $this->convert(memory_get_peak_usage(true)) . "] $msg \n\r";
    }
    private function convert($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).''.$unit[$i];
    }
}


$myserver = new MyServer();






