<?php

///////////////////////////////////////////////////////////////////////////////////////
//
//	Class for wrap and unwrap message transfers. Only work with AES CBC mode encryption (why i wrote this?)
//
//	2012/I/24
//
///////////////////////////////////////////////////////////////////////////////////////


namespace spserver\util;

use Exception;
use stdClass;


class Message
{
	
	const SEPARATOR = 124;

	///////////////
    //	METHODS  //
    ///////////////

	/**
	 * Wrap a message for to be sent
	 * 
	 * @param object|array|string $message
	 * @param AES $aes
	 * @param boolean $compress
	 * @param boolean $base64
	 * @return string
	 * 
	 */
	
	public static function wrap($message, $aes=NULL, $base64=false, $compress=true, $forcecompress=false)
	{
		$_message = $message;

		$_compress = ($compress) ? 1 : 0;
		$_base64 = ($base64) ? 1 : 0;
		$_encryption = ($aes == NULL) ? 0 : 1;
		$_json = (gettype($message) == 'object' || gettype($message) == 'array') ? 1 : 0;


		//Converting to JSON
		if ($_json)
		{
			//echo $message;
			$message = json_encode($message);
            if (json_last_error() !== JSON_ERROR_NONE)
            {	
            	$_json = 0;
            	$message = $_message;
            }	     
		}

		//Compresing
		if ($_compress)
		{
			$_message = $message;
			$message = gzdeflate($message);
			#echo strlen($message) . " " . strlen($_message) . " " . strlen(base64_encode($_message)) . " " . strlen(base64_encode(gzdeflate($_message))) . "\n";
			if (strlen($message) > strlen($_message) && !$forcecompress)
			{
				$message = $_message;
				$_compress = 0;
			}
		}

		//Base64decoding
		if ($_base64)
			$message = base64_encode($message);

		//Encrypting
		if ($_encryption)
			$message = $aes->encrypt($message);

		unset($_message);
		unset($aes);

		
		return $message . chr(bindec("$_encryption$_base64$_compress$_json")) . chr(Message::SEPARATOR);
	}
	
	
	/**
	 * Unwrap a message for to be sent
	 * 
	 * @param string $message
	 * @param AES $aes
	 * @param boolean $toArray
	 * @return array
	 * 
	 */
	
	public static function unwrap($message, $aes=NULL, $toArray=false)
	{
		$_result = array();
		$msgCompiled = '';
		$byteN = 0;
		$alert = false;
		for ($i=0; $i<strlen($message); $i++)
		{
			$byteN = ord($message{$i});
			if ($alert && $byteN == Message::SEPARATOR)
			{
				$alert = false;
				$_result[] = Message::unwrapMessage($msgCompiled, $aes, $toArray);
				$msgCompiled = '';
			}
			else
			{
				$msgCompiled .= chr($byteN);
				if ($byteN < 16)
					$alert = true;
			}
		}
		return $_result;
	}
	
	
	
	
	private static function unwrapMessage($message, $aes=NULL, $toArray=false)
	{
		try {
			$instruccion = str_pad(decbin(ord(substr($message,-1))), 4, '0', STR_PAD_LEFT);
			$message = substr($message,0,-1);
			#echo($instruccion);
	
			//Decrypting
			if ($aes != NULL && $instruccion{0} == '1')
				if (!($message = @$aes->decrypt($message)))
					throw new Exception("Not possible Decrypting the message");
			
				
			//Base64decoding
			if ($instruccion{1} == '1')
				if (!($message = @base64_decode($message)))
					throw new Exception("Not possible base64decoding the message");
	
	
			//Uncompresing
			if ($instruccion{2} == '1')
				if (!($message = @gzinflate($message)))
					throw new Exception("Not possible uncompress the message");
	
	
			//Converting to JSON
			if ($instruccion{3} == '1')
			{
				$_message = json_decode($message);
	            if (json_last_error() == JSON_ERROR_NONE)
	            	$message = $_message;
	            else
	            	throw new Exception("Ocurred a json decode error: " . json_last_error());
			}
			
			//Object to Array
			if ($toArray)
				if (!($message = @get_object_vars($message)))
					throw new Exception("Not possible convert to Array");
	
	
			unset($_message);
			unset($instruccion);
			unset($aes);
		}
		catch (Exception $e)
		{
			$message = new stdClass();
			$message->error = true;
			$message->msg = '#'.$e->getCode().': '.$e->getMessage() . ' ('.$e->getFile().' Line:' . $e->getLine() . ')';
		}

		return $message;
	}
}

/*

1º ENCRYPT
2º BASE64
3º COMPRESS
4º JSONED
 


*/
