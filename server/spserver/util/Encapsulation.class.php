<?php

///////////////////////////////////////////////////////////////////////////////////////
//
//	Base class of encapsulation methods for subclasses
//
//	2012/VIII/9
//
///////////////////////////////////////////////////////////////////////////////////////


namespace spserver\util;


class Encapsulation
{
	
	//////////////////
	//	PROPERTIES  //
	//////////////////
	
	/**
	 * If the variable can be getted you need to add the string of the var in this array
	 * @var array
	 */
	private $gets = array();
	
	/**
	 * If the variable can be setted you need to add the string of the var in this array
	 * @var array
	 */
	private $sets = array();

	

	///////////////
	//	METHODS  //
	///////////////


	/**
	 * Add a new property, to get in subclasses
	 *  
	 * @param string $name
	 * @param string $callBackFunction
	 * @return void
	*/
	protected function addGet($name, $callBackFunction=false)
	{
		$this->gets[$name] = $callBackFunction;
	}

	/**
	 * Add a new property, to set in subclasses
	 *  
	 * @param string $name
	 * @param string $callBackFunction
	 * @return void
	*/
	protected function addSet($name, $callBackFunction=false)
	{
		$this->sets[$name] = $callBackFunction;
	}
	
	
	public function __get($name)
	{
		if (property_exists($this, $name) && isset($this->gets[$name]))
			return (gettype($this->gets[$name]) == 'string' && method_exists($this, $this->gets[$name])) ? 
				$this->{$this->gets[$name]}($name)
			: 
				$this->{$name};
		else
			trigger_error("You can't get the property " . get_class($this) . "::\$$name", E_USER_NOTICE);
	}

	public function __set($name, $value)
	{
		if (property_exists($this, $name) && isset($this->sets[$name]))
			(gettype($this->sets[$name]) == 'string' && method_exists($this, $this->sets[$name])) ? 
				$this->{$this->sets[$name]}($value, $name)
			: 
				$this->{$name} = $value;
		else
			trigger_error("You can't set the property " . get_class($this) . "::\$$name", E_USER_NOTICE);
	}

	public function __isset($name)
	{
		return (isset($this->{$name}) && (isset($this->gets[$name]) || isset($this->sets[$name])));
	}
}







/*
class TestEncapsulation extends Encapsulation
{
	protected $a = 10;
	protected $b = 20;
	protected $c = 30;
	protected $d = 40;
	
	public function __construct()
	{
		$this->addGet('a');
		$this->addSet('a','setA');
		
		$this->addGet('b','getB');
		$this->addSet('b');
		
		$this->addGet('c');
		//$this->addSet('c');
	}
	
	
	protected function setA($value, $n)
	{
		$this->a = ($value+1); //or $this->{$n} = ($value+1);
	}


	protected function getB($n)
	{
		return $this->b+1; //or return $this->{$n}+1;
	}

}

$obj = new TestEncapsulation;
var_dump($obj->a); //int 10
var_dump($obj->b); //int 21
var_dump($obj->c); //int 30
$obj->a = 1000;
$obj->b = 2000;
$obj->c = 3000; //Error User Notice
var_dump($obj->a); //int 1001
var_dump($obj->b); //int 2001
var_dump($obj->c); //int 30
var_dump(isset($obj->c)); //boolean true
var_dump(isset($obj->d)); //boolean false
*/