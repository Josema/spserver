<?php

///////////////////////////////////////////////////////////////////////////////////////
//
//	Errors and warnings code class
//
//	2012/VIII/10
//
///////////////////////////////////////////////////////////////////////////////////////


namespace spserver\util;


class Error
{

	//////////////////
	//	PROPERTIES  //
	//////////////////

	/**
	 * The all message errors
	 * @var array
	 */
	private static $codes = array
	(
		/* ERROR */
		1 => 'Could not create socket_create',
		2 => 'Could not set up socket_set_option',
		3 => 'Could not set socket_set_nonblock',
		4 => 'Could not bind to socket whith this ip',
		5 => 'Could not set up socket listener',
		6 => 'Not is possible add a Client in a group with different idSocket.',

		/* WARNINGS */
		11 => 'Limit exceeded of buffer.',
		12 => 'Impossible to connect new client: Max clients reached.',
		13 => 'Impossible to connect new client: Too much equal ips for connect.',
		14 => 'Impossible to connect new client: IP banned.',

		/* CORE */
		20 => 'Not is possible add a Client in a group with different idSocket.'
	);



	///////////////
	//	METHODS  //
	///////////////

	
 	/**
	 * Get string of error by code
	 * 
	 * @param int $codeNumber
	 * @return string
	 */
	public static function get($codeNumber)
	{
		return (isset(self::$codes[$codeNumber])) ? self::$codes[$codeNumber] : $codeNumber;
	}

	
	/**
	 * Show a Notice error
	 * 
	 * @param string|int $message
	 * @return void
	 */
	public static function NOTICE($message)
	{
		self::show($message, E_USER_NOTICE);
	}


	/**
	 * Show a Warning error
	 * 
	 * @param string|int $message
	 * @return void
	 */
	public static function WARNING($message)
	{
		self::show($message, E_USER_WARNING);
	}


	/**
	 * Show a Fatal error
	 * 
	 * @param string|int $message
	 * @return void
	 */
	public static function ERROR($message)
	{
		self::show($message, E_USER_ERROR);
	}
	
	
	/**
	 * Get string of error by code
	 * 
	 * @param string|int $message
	 * @param string $type
	 * @return string
	 */
	private static function show($message, $type)
	{
		if (gettype($message) == 'integer')
			$message = (isset(self::$codes[$message])) ? self::$codes[$message] : 'Unknown exception';

		return trigger_error($message, $type);
	}
}
?>