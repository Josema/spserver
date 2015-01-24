<?php
class SocketException extends Exception {
	
	public function __construct($msg="", $code=0) {
		parent::__construct($msg);
		$this->code = $code;
	}
}
?>
