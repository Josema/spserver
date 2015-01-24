<?php
/*
 * Created on 04/08/2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

// Require class
require_once("Socket.php");
require_once("URL.php");

class HttpURLConnection {
	
	const HTTP_ACCEPTED = 202;
	const HTTP_BAD_GATEWAY = 502;
	const HTTP_BAD_METHOD = 405;
	const HTTP_BAD_REQUEST = 400;
	const HTTP_CLIENT_TIMEOUT = 408;
	const HTTP_CONFLICT = 409;
	const HTTP_CREATED = 201;
	const HTTP_ENTTY_TOO_LARGE = 413;
	const HTTP_FORBIDDEN = 403;
	const HTTP_GATEWAY_TIMEOUT = 504;
	const HTTP_GONE = 410;
	const HTTP_INTERNAL_ERROR = 500;
	const HTTP_LENGTH_REQUIRED = 411;
	const HTTP_MOVED_PERM = 301;
	const HTTP_MOVED_TEMP = 302;
	const HTTP_MULT_CHOICE = 300;
	const HTTP_NO_CONTENT = 204;
	const HTTP_NOY_ACCEPTABLE = 406;
	const HTTP_NOT_AUTHORITATIVE = 203;
	const HTTP_NOT_FOUND = 404;
	const HTTP_IMPLEMENTED = 501;
	const HTTP_MODIFIED = 304;
	const HTTP_OK = 200;
	const HTTP_PARTIAL = 206;
	const HTTP_PAYMENT_REQUIRED = 402;
	const HTTP_PRECOND_FAILED = 412;
	const HTTP_PROXY_AUTH = 407;
	const HTTP_REQ_TOO_LONG = 414;
	const HTTP_RESET = 205;
	const HTTP_SEE_OTHER = 303;
	const HTTP_UNAUTHORIZED = 401;
	const HTTP_UNAVALIABLE = 503;
	const HTTP_UNSUPPORTED_TYPE = 415;
	const HTTP_USE_PROXY = 305;
	const HTTP_VERSION = 505;
	
	protected $url = null;
	
	protected $connected = false;
	
	protected $method = "GET";
	
	// @ignore
	//protected $responseCode;
	
	// @ignore
	//protected $responseMessage;
	
	private $headers = array();
	
	private $content;
	
	public function __construct($url,$connect=false) {
		if (is_object($url) && $url instanceof URL) {
			$this->url = $url;
			if ($connect)
				$this->connect();
		}
	}
	
	public function connect() {
		$query = ($this->url->getQuery() != "") ? "?".$this->url->getQuery() : "";
		$request  = $this->method." ".$this->url->getPath().$query." HTTP/1.0\r\n";
		$request .= "Host: ".$this->url->getHost()."\r\n";
		$request .= "User-Agent: PHP/".phpversion()."\r\n";
		if ($this->url->getUserInfo() != "")
			$request .= "Authorization: Basic ".$this->url->getUserInfo()."\r\n";
		
		// Add headers
		if (count($this->headers) > 0) {
			foreach ($this->headers as $key => $value)
				$request .= $key.": ".$value;
		}
		if (strtoupper($this->method) == "POST") {
			$request .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$request .= "Content-Length: ".strlen($this->url->getQuery()) . "\r\n";
			$request .= "\r\n";
			$request .= $this->url->getQuery();
		} else {
			$request .= "\r\n";
		}

		// Initialize socket connection
		$socket = new Socket($this->url->getHost(), 80);
		$socket->write($request);
		$response = "";
		$response = $socket->read();
		$contentAndHeaders = split("\r\n\r\n",$response,2);
		
		$this->content = $contentAndHeaders[1];
		
		$headers = explode("\r\n",$contentAndHeaders[0]);
		for ($i = 0;$i < count($headers);$i++) {
			if (count($header = split(":",$headers[$i],2)) > 1)
				$this->headers[strtolower($header[0])] = $header[1];
		}
	}
	
	public function getURL() {
		return $this->url;
	}
	
	public function addRequestProperty($key,$value) {
		$this->headers[$key] = $value;
	}
	
	public function getRequestProperties() {
		return $this->headers;
	}
	
	public function getRequestProperty($key) {
		return (isset($this->headers[strtolower($key)])) ? $this->headers[$key] : null;
	}
	
	public function getContent() {
		return $this->content;
	}
	
	public function getContentLength() {
		return strlen($this->content);
	}
	
	public function getContentEncoding() {
		if (isset($this->headers["content-type"])) {
			$contentType = explode(";",$this->headers["content-type"]);
			if (count($contentType) > 1)
				return str_replace("charset=","",trim($contentType[1]));
		}
		return null;
	}	
	
	public function getContentType() {
		if (isset($this->headers["content-type"])) {
			$contentType = explode(";",$this->headers["content-type"]);
			return $contentType[0];
		}
		return null;
	}
	
	public function getDate() {
		return $this->headers["date"];	
	}
	
	public function getServerName() {
		return $this->headers["server"];
	}
	
	public function getExpiration() {
		return (isset($this->headers["expires"])) ? $this->headers["expires"] : null;
	}
	
	public function getRequestMethod() {
		return $this->method;
	}
	
	public function setRequestMethod($method) {
		$this->method = $method;
	}
	
	/* @ignore
	public function getResponseCode() {
		return $this->responseCode;
	}
	public function getResponseMessage() {
		return $this->responseMessage;
	}
	*/	
}
?>
