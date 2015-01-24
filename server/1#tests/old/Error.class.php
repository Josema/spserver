<?php

///////////////////////////////////////////////////////////////////////////////////////
//
//	Errors and warnings code class
//
//	2011/VIII/2
//
///////////////////////////////////////////////////////////////////////////////////////


namespace spserver\util;
use Exception;


class Error extends Exception
{


    /////////////////
    //	VARIABLES  //
    /////////////////
    
    /**
	 * The inc increment for new errors added
	 * @var int
	 */
    private static $inc = 50;  

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
	 * @param mixed $codeMessage
	 * @param int $code
	 * @return void
	 */
    public function __construct($message, $code = NULL)
    {
        if ($code == NULL)
        {
            if (gettype($message) == 'integer')
            {
                $code = $message;
                $message = (isset(self::$codes[$code])) ? self::$codes[$code] : 'Unknown exception';
            }
            else
            	$code = self::$inc++;

        }
        
    
        self::add($code, $message);
        parent::__construct($message, $code);
    }
    
    
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
	 * Add a new error
	 * 
	 * @param int $codeNumber
	 * @param string $message
	 * @return void
	 */
    public static function add($codeNumber, $message)
    {
        if (!isset(self::$codes[$codeNumber]))
            self::$codes[$codeNumber] = $message;
    }
}
?>