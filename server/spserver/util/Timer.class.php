<?php

///////////////////////////////////////////////////////////////////////////////////////
//
//	Timer class for events
//
//	2012/III/22
//
///////////////////////////////////////////////////////////////////////////////////////


namespace spserver\util;

require_once dirname(__FILE__) . '/../events/EventDispatcher.class.php';
require_once dirname(__FILE__) . '/../events/Event.class.php';

use spserver\events\EventDispatcher;
use spserver\events\Event;


class Timer extends EventDispatcher
{
	
	/////////////////
    //	VARIABLES  //
    /////////////////
	
    /**
	 * The id of the Timer
	 * @var int
	 */
    protected $id;

	
	/**
	 * Counter repeats launched.
	 * @var int
	 */
	protected $repeats = 0;

	/**
	 * Counter repeats launched.
	 * @var int
	 */
	protected $started;
	
	/**
	 * Is running.
	 * @var bool
	 */
	protected $running = true;

	/**
	 * The delay between timer events, in milliseconds.
	 * @var int
	 */
	public $delay;

	/**
	 * Specifies the number of repetitions. If zero, the timer repeats infinitely. If nonzero, the timer runs the specified number of times and then stops.
	 * @var int
	 */
	public $repeatTotal;

	
	///////////////
    //	METHODS  //
    ///////////////
	
	/**
	 * Create instance Timer
	 * 
	 * @param int $delay
	 * @param int $repeatCount
	 * 
	 */
	public function __construct($delay, $repeatTotal=0)
	{
		$this->delay = $delay;
		$this->repeatTotal = $repeatTotal;
		$this->started = $this->militime();
	}

	
	/**
	 * Id socket client
	 * @return int
	 */
    public function id()
	{
	    return $this->id;
	}


	/**
	 * Set a id of this Timer
	 * 
	 * @param int $id
	 * @return void
	 */
    public function setId($id)
	{
	   $this->id = $id;
	}
	
	
	/**
	 * Launch events
	 * @return void
	 */
	public function launch()
	{
		if ($this->running && $this->repeatTotal > -1)
		{
			#echo $this->started ." ". $this->militime()  . " >= " . ($this->started+($this->delay*($this->repeats+1))) . "\n";
			if (($this->repeatTotal == 0 || $this->repeatTotal>$this->repeats) && $this->militime() >= ($this->started+($this->delay*($this->repeats+1))))
			{
				$this->repeats += 1;
				$this->dispatchEvent(new Event(Event::TIMER_LOOP,(object) array(
                        'timer' => $this, 
                        'repeats' => $this->repeats
                )));
			}
			if ($this->repeatTotal > 0 && $this->repeats == $this->repeatTotal)
			{
				$this->dispatchEvent(new Event(Event::TIMER_COMPLETE,(object) array(
                	'timer' => $this, 
                    'repeats' => $this->repeats
                )));
				$this->running = false;
			}
		}
	}
	
	
	/**
	 * Stop this timer
	 * @return void
	 */
	public function stop()
	{
		$this->running = false;
	}


	/**
	 * If timer is running
	 * @return bool
	 */
    public function running()
	{
	    return $this->running;
	}

	/**
	 * Get timestamp in miliseconds
	 * @return float
	 */
	static public function militime()
	{
    	return (microtime(true)*1000);
	}
}

?>