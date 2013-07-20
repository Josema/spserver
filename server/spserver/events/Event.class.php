<?php
/**
 * Event
 * 
 * @category	Event
 * @package		dayscript.event
 * @subpackage	
 * @author		Nelson Daza <ndaza@dayscript.com>
 * @copyright	2009 Dayscript Ltda.
 * @license		
 * @version		1.0
 * @version		$Revision: 0 $
 * @filesource	
 * @link		http://www.dayscript.com
 * @link		{docsLink}
 * @uses		
 * @since		1.0
 * @modifiedby	$LastChangedBy: Nelson Daza $
 * @modified	$Date: 2009-09-24 $
 */

namespace spserver\events;

/**
 * Event
 * 
 * The Event class is used as the base class for the creation of Event objects, which are passed as parameters to event listeners when an event occurs. 
 *
 * @category	Event
 * @package		dayscript.event
 * @subpackage	
 * @author		Nelson Daza <ndaza@dayscript.com>
 * @copyright	2009 Dayscript Ltda.
 * @license		
 * @version		1.0
 * @link		http://www.dayscript.com
 * @link		{docsLink}
 * @uses		
 * @since		1.0
 * @modifiedby	$LastChangedBy: Nelson Daza $
 * @modified	$Date: 2009-09-24 $
 * @todo add target currentTarget eventDispacher
 */
class Event	{
	/**
	 * Whether an event is a bubbling event.
	 * 
	 * When an event occurs, it moves through the three phases of the event flow: 
	 * 1. The capture phase, which flows from the top of the listeners list hierarchy to the object just before the target object; 
	 * 2. The target phase, which comprises the target object; 
	 * 3. The bubbling phase, which flows from the object subsequent to the target object back up the list hierarchy.
	 * 
	 * Some events, such as the activate and unload events, do not have a bubbling phase. 
	 * 
	 * @var boolean $bubbles
	 */
	
	
	
	/**
	 * Se lanza cuando hay algun error
	 * 
	 * @param int parameter->idSocket
	 * @param string parameter->code
	 */
	const SERVER_ERROR = 'serverError';


	/**
	 * Se lanza cuando se inflinge una norma de seguridad, como exceso de flood, maximo de ips
	 * 
	 * @param int parameter->idSocket
	 * @param int parameter->idClient
	 * @param int parameter->ip
	 * @param int parameter->resourceClient
	 * @param string parameter->code
	 */
	const SERVER_WARNING = 'serverWarning';
	
	
	/**
	 * Se lanza cuando una ip ha sido desbaneada por timeout
	 * 
	 * @param int parameter->ip
	 */
	const SERVER_BANREMOVED = 'serverBanremoved';


	/**
	 * Se lanza cuando se conecta un nuevo cliente a un Socket
	 * 
	 * @param int parameter->idSocket
	 * @param int parameter->idClient
	 * @param string parameter->ip
	 * @param resource parameter->resourceClient
	 */
	const CLIENT_CONNECT = 'clientConnect';


	/**
	 * Se lanza cuando se desconecta un cliente
	 * 
	 * @param int parameter->idSocket
	 * @param int parameter->idClient
	 * @param string parameter->ip
	 * @param resource parameter->resource
	 */
	const CLIENT_DISCONNECT = 'clientDisconnect';
	
	
	/**
	 * Se lanza cuando se desconecta un cliente
	 * 
	 * @param int parameter->idSocket
	 * @param int parameter->idClient
	 * @param string parameter->ip
	 * @param resource parameter->resource
	 */
	const CLIENT_TIMEOUT = 'clientTimeout';


	/**
	 * Se lanza cuando un usuario ha enviado algo al servidor
	 * 
	 * @param int parameter->idSocket
	 * @param int parameter->idClient
	 * @param string parameter->data
	 * @param object parameter->json
	 * @param resource parameter->resourceClient
	 */
	const CLIENT_DATA = 'clientData';


	/**
	 * Se lanza cuando un usuario de flash solicita el cross-domain
	 * 
	 * @param int parameter->idSocket
	 * @param int parameter->idClient
	 * @param string parameter->data
	 * @param resource parameter->resourceClient
	 */
	const CLIENT_POLICY = 'clientPolicy';
	
	
	
	
	/**
	 * Se lanza cuando un un timer ha terminado una repeticion.
	 */
	const TIMER_LOOP = 'timerLoop';
	
	
	/**
	 * Se lanza cuando un todos los loops/repeticiones han terminado.
	 */
	const TIMER_COMPLETE = 'timerComplete';
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
		
   /**
	 * Defines the fase types.
	 * @var int
	 */
	const PHASE_NONE			= 0;
	const PHASE_CAPTURING		= 1;
	const PHASE_TARGET			= 2;
	const PHASE_BUBBLING		= 4;
	
	
	
	
	
	 
	public $bubbles = false;
	/**
	 * Whether the behavior associated with the event can be prevented.
	 * 
	 * @var boolean $cancelable
	 */
	public $cancelable = false;	
	/**
	 * The object that is actively processing the Event object with an event listener.
	 * 
	 * @var mixed $currentTarget
	 */
	public $currentTarget = null;	
	/**
	 * The current phase in the event flow. This property can contain the following numeric values:
	 * 
	 * The capture phase	(Event::PHASE_CAPTURING).
	 * The target phase		(Event::PHASE_TARGET).
	 * The bubbling phase	(Event::PHASE_BUBBLING).
	 * 
	 * @var int $phase
	 */
	public $phase = self::PHASE_NONE;
	/**
	 * The event target.
	 * 
	 * @var mixed $currentTarget
	 */
	public $target = null;	
	/**
	 * The type of event. The type is case-sensitive.
	 * 
	 * @var string $type
	 */
	public $type = null;
	/**
	 * Whether the preventDefault( ) method has been called on the event.
	 * 
	 * @var boolean $defaultPrevented
	 */
	protected $defaultPrevented = false;
	/**
	 * Whether the stopPropagation( ) method has been called on the event.
	 * 
	 * @var boolean $stoped
	 */
	protected $stoped = false;
	
	
	
	

	
	
	
	
	/**
	 * You can transfer parameters from inner clas to external listeners
	 * 
	 * @var mixed $parameteres
	 */
	public $parameters;
	
	
	
	
	/**#@-*/
	/**
	 * Event constructor, creates an Event object to pass as a parameter to event listeners.
	 *  
	 * @param string $type
	 * @param boolean $bubbles
	 * @param boolean $cancelable
	 * @return \dayscript\event\Event $event
	 */
	public function __construct( $type, $parameters=NULL, $bubbles = false, $cancelable = false )	{
		$this->type = (string)$type;
		$this->bubbles = (boolean)$bubbles;
		$this->cancelable = (boolean)$cancelable;
		$this->parameters = $parameters;
	}
	/**
	 * Whether the events bubbles or not.
	 * @return boolean $bubbles
	 */
	public function isBubbles( )	{
		return $this->bubbles;
	}
	/**
	 * Whether the events is cancelable or not.
	 * @return boolean $cancelable
	 */
	public function isCancelable( )	{
		return $this->cancelable;
	}
	/**
	 * Whether the preventDefault( ) method has NOT been called on the event.
	 * @return boolean $defaultPrevent
	 */
	public function isDefaultPrevented( )	{
		return $this->defaultPrevented;
	}
	/**
	 * Whether the stopPropagation( ) method has been called on the event.
	 * @return boolean $stoped
	 */
	public function isStoped( )	{
		return $this->stoped;
	}
	/**
	 * Returns the object that is actively processing the Event.
	 * @return mixed $currentTarget
	 */
	public function getCurrentTarget( )	{
		return $this->currentTarget;
	}
	/**
	 * Sets the object that is actively processing the Event.
	 * @return mixed $currentTarget
	 */
	public function setCurrentTarget( $target )	{
		return $this->currentTarget = $target;
	}
	/**
	 * Returns the current event phase of this Event.
	 * @return int $target
	 */
	public function getPhase( )	{
		return $this->phase;
	}
	/**
	 * Sets the current event phase of this Event.
	 * @param int $phase
	 */
	public function setPhase( $phase )	{
		if( $phase > $this->phase )
			$this->phase = $phase;
	}
	/**
	 * Returns the object target of this Event.
	 * @return mixed $target
	 */
	public function getTarget( )	{
		return $this->target;
	}
	/**
	 * Sets the object target of this Event.
	 * @param mixed $target 
	 */
	public function setTarget( $target )	{
		$this->target = $target;
	}
	/**
	 * Returns the type of this Event.
	 * @return mixed $type
	 */
	public function getType( )	{
		return $this->type;
	}
	/**
	 * Cancels an event's default behavior if that behavior can be canceled. 
	 */
	public function preventDefault( )	{
		$this->defaultPrevented = true;
	}
	/**
	 * Prevents processing of any event listeners in the event flow.
	 * 
	 * Additional calls to this method have no effect.
	 * This method can be called in any phase of the event flow.
	 * Note: This method does not cancel the behavior associated with this event; see preventDefault() for that functionality.
	 */
	public function stopPropagation( )	{
		if( $this->cancelable )	{
			$this->stoped = true;
		}
	}
	/**
	 * Returns a string containing all the properties of the Event object. The string is in the following format: [Event type=value bubbles=value cancelable=value]
	 * @return string A string containing all the properties of the Event object.
	 */
	public function __toString( )	{
		return sprintf( '[%s type=%s bubbles=%s cancelable=%s dispatcherTarget=%s currentTarget=%s]', __CLASS__, $this->type, ( $this->bubbles ? 'true' : 'false' ), ( $this->cancelable ? 'true' : 'false' ), ( $this->target ? ( is_object( $this->target ) ? get_class( $this->target ) : $this->target ) : 'null' ), ( $this->currentTarget ? ( is_object( $this->currentTarget ) ? get_class( $this->currentTarget ) : $this->currentTarget ) : 'null' ) );
	}
}
