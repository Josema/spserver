<?php

///////////////////////////////////////////////////////////////////////////////////////
//
//	Class for AES 128 Encryption/Decyption
//
//	2012/I/28
//
///////////////////////////////////////////////////////////////////////////////////////


namespace spserver\util;


class AES
{
	
	const ALGORITHM = MCRYPT_RIJNDAEL_128;
	const SIZE_KEY = 32;

	
	//////////////////
	//	PROPERTIES  //
	//////////////////

	/**
	 * @var string
	 */
	private $mode = MCRYPT_MODE_CBC;
	/**
	 * @var string
	 */
	private $key = NULL;
	/**
	 * @var string
	 */
	private $iv = NULL;

	
	///////////////
	//	METHODS  //
	///////////////
	
	
	/**
	 *  
	 * @param string $mode
	 * @return void
	 * 
	*/
	public function setMode($mode)
	{
		$this->mode = $mode;
	}
	/**
	 *  
	 * @return string
	 * 
	*/
	public function getMode()
	{
		return $this->mode;
	}
	

	/**
	 *  
	 * @param string $key
	 * @param int $size
	 * @return void
	 * 
	*/
	public function setKey($key, $size=AES::SIZE_KEY)
	{
		if ($size != AES::SIZE_KEY && $size != 24 && $size != 16)
				$size = AES::SIZE_KEY;

		$this->key = $this->resize($key, $size);
	}
	/**
	 *  
	 * @return string
	 * 
	*/
	public function getKey()
	{
		return $this->key;
	}


	/**
	 *  
	 * @param string $iv
	 * @return void
	 * 
	*/
	public function setIV($iv)
	{
		$this->iv = $this->resize($iv, mcrypt_get_iv_size(AES::ALGORITHM, $this->mode));
	}
	/**
	 *  
	 * @return string
	 * 
	*/
	public function getIV()
	{
		return $this->iv;
	}
	/**
	 *  
	 * @return string
	 * 
	*/
	public function getDinamicIV()
	{
		return mcrypt_create_iv(mcrypt_get_iv_size(AES::ALGORITHM, $this->mode), MCRYPT_RAND);
	}

	
	/**
	 *  
	 * @param string $data
	 * @param string $key
	 * @return string
	 * 
	*/
	public function encrypt($data)
	{
		if ($this->needIV() && $this->iv == NULL)
			$this->setIV($this->key);

		return mcrypt_encrypt(AES::ALGORITHM, $this->key, $data, $this->mode, $this->iv);
	}
	/**
	 *  
	 * @param string $data
	 * @param string $key
	 * @return string
	 * 
	*/
	public function decrypt($data)
	{
		if ($this->needIV() && $this->iv == NULL)
			$this->setIV($this->key);

		return trim(mcrypt_decrypt(AES::ALGORITHM, $this->key, $data, $this->mode, $this->iv), "\0..\32");
	}


	/**
	 *  
	 * @param string $str
	 * @param int $size
	 * @return string
	 * 
	*/
	private function resize($str, $size)
	{
		if (strlen($str) != $size)
		{
			$pad_string = $str;
			for ($i=1; $i<$size; ++$i)
				$pad_string .= chr($i);
			
			$str = substr($pad_string, 0, $size);
		}
		
		return $str;
	}
	/**
	 *  
	 * @return boolean
	 * 
	*/
	private function needIV()
	{
		return ($this->mode == MCRYPT_MODE_CBC || $this->mode == MCRYPT_MODE_CFB || $this->mode == MCRYPT_MODE_OFB);
	}
}