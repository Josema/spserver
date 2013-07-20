<?php
/**
 * EventDispatcher
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
 
 
 * The EventDispatcher class is the base class for all classes that dispatch events.
 * 
 * The EventDispatcher class implements the Dispatcher interface and is the base class for the DisplayObject class.
 * This class allows any object on the display list to be an event target and as such, to use the methods of the Dispatcher interface.
 * 
 * Event targets are an important part of the Module/Plugin event model.
 * The event object makes a round-trip journey to the event target, which is conceptually divided into three phases: 
 * - The capture phase includes the journey from the root to the last node before the event target's node;
 * - The target phase includes only the event target node;
 * - The bubbling phase includes any subsequent nodes encountered on the return trip to the 'root'.
 * 
 * In general, the easiest way for a user-defined class to gain event dispatching capabilities is to extend EventDispatcher.
 * If this is impossible (that is, if the class is already extending another class), you can instead implement the 
 * Dispatcher interface, create an EventDispatcher member, and write simple hooks to route calls into the aggregated EventDispatcher.
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
 */
 
namespace spserver\events;

require_once dirname(__FILE__) . '/../util/Encapsulation.class.php';
require_once dirname(__FILE__) . '/IEventDispatcher.class.php';
require_once dirname(__FILE__) . '/Event.class.php';

use spserver\util\Encapsulation;
use spserver\events\IEventDispatcher;
use spserver\events\Event;


class EventDispatcher extends Encapsulation implements IEventDispatcher {
	/**
	 * The target object for events dispatched to the EventDispatcher object.
	 * This parameter is used when the EventDispatcher instance is aggregated by a class that implements Dispatcher; 
	 * it is necessary so that the containing object can be the target for events.
	 * @var Dispatcher
	 */
	private $target = null;
	/**
	 * The list of event types that this Dispatcher will handle.
	 * @var array
	 */
	private $eventTypes = null;
	/**
	 * Capture Listeners list.
	 * @var array
	 */
	private $captureListeners = null;
	/**
	 * Listeners list.
	 * @var array
	 */
	private $listeners = null;
	/**
	 * Aggregates an instance of the EventDispatcher class.
	 * 
	 * This class is generally used as a base class, which means that you do not need to use this constructor function.
	 * However, advanced developers who are implementing the Dispatcher interface need to use this constructor.
	 * If you are unable to extend the EventDispatcher class and must instead implement the Dispatcher interface, 
	 * use this constructor to aggregate an instance of the EventDispatcher class.
	 * 
	 * @param Dispatcher $target The target object for events dispatched to the EventDispatcher object.
	 * This parameter is used when the EventDispatcher instance is aggregated by a class that implements Dispatcher; 
	 * it is necessary so that the containing object can be the target for events.
	 * Do not use this parameter in simple cases in which a class extends EventDispatcher.
	 * @return EventDispatcher
	 * @todo 
	 */
	public function __construct( $target = null, $eventTypes = null )	{
		$this->target = ( $target !== null ? $target : $this );
		$this->eventTypes = ( is_array( $eventTypes ) && !empty( $eventTypes ) ? $eventTypes : null );
		$this->listeners = array( );
	}

	/**
	 * @see Dispatcher::addEventListener
	 */
	public function addEventListener( $types, $listener, $object=NULL, $useCapture = false, $priority = 0 )	{
		
		/*if( !$object )
			throw new InvalidArgumentException( 'No valid object asignation to this EventListener.', E_USER_ERROR );*/
		if( !$types )
			throw new \InvalidArgumentException( 'No valid type asignation to this EventListener.', E_USER_ERROR );
		if( !$listener || !is_callable( $listener ) )
			throw new \InvalidArgumentException( 'No valid callable listener asignation to this EventListener.', E_USER_ERROR );
		
		if( !is_array( $types ) )
			$types = array( (string)$types );
			
		$useCapture = (bool)$useCapture;
		$priority = (int)$priority;
		
		$listeners = array( );
		if( $useCapture )
			$listeners = &$this->captureListeners;
		else
			$listeners = &$this->listeners;
		
			
		foreach( $types as $type )	{
			if( $this->eventTypes && !in_array( $type, $this->eventTypes ) )
				throw new \InvalidArgumentException( 'No action allowed for method "' . $type . '" in this EventListener.', E_USER_ERROR );
			
			if( !isset( $listeners[$type] ) )
				$listeners[$type] = array( );
			if( !isset( $listeners[$type][$priority] ) )
				$listeners[$type][$priority] = array( );
				
			$listeners[$type][$priority][] = array( 'obj' => $object, 'lst' => $listener );
			krsort( $listeners[$type] );
		}
	}
	/**
	 * @see Dispatcher::dispatchEvent
	 */
	public function dispatchEvent( $event )	{
		
		if( $this->eventTypes && !in_array( $event->getType( ), $this->eventTypes ) )
			throw new \Exception( 'No dispatch allowed for method "' . $event->getType( ) . '" in this EventListener.', E_USER_ERROR );
		
		$event->setTarget( $this->target );
		$type = $event->getType( );

		/**
		 * Event Capture phase
		 */
		if( isset( $this->captureListeners[$type] ) )	{
			$event->setPhase( Event::PHASE_CAPTURING );
			$this->listenersCall( $event, $this->captureListeners[$type] );
		}

		/**
		 * Event Target phase
		 */
		
		//print_r($this->listeners);
		if( isset( $this->listeners[$type] ) )	{
			$event->setPhase( Event::PHASE_TARGET );
			$this->listenersCall( $event, $this->listeners[$type] );
		}
		
		$event->setPhase( Event::PHASE_NONE );
		$event->setCurrentTarget( null );
		unset($event);
	}
	
	
	private function listenersCall( Event $event, &$listeners )	{
		if( !$event->isStoped( ) )	{
			foreach( $listeners as $pListeners )	{
				foreach( $pListeners as $listener )	{
					$event->setCurrentTarget( $listener['obj'] );
					call_user_func( $listener['lst'], $event );
					if( $event->isStoped( ) )
						return;
				}
			}
			
			if( $event->isBubbles( ) && $event->getPhase( ) == Event::PHASE_TARGET )	{
				$keys = array_keys( $listeners );
				for( $c = count( $keys ) - 1; $c >= 0; $c-- )	{
					$listeners2 = array_reverse( $listeners[$keys[$c]] );
					foreach( $listeners2 as $listener )	{
						$event->setCurrentTarget( $listener['obj'] );
						call_user_func( $listener['lst'], $event );
						if( $event->isStoped( ) )
							return;
					}
				}
			}
		}
	}
	/**
	 * @see Dispatcher::hasEventListener
	 */
	public function hasEventListener( $type )	{
		return ( isset( $this->listeners[$type] ) || isset( $this->captureListeners[$type] ) );
	}
	/**
	 * @see Dispatcher::willTrigger
	 * @todo No es realmente igual a hasEventListener( )
	 */
	public function removeEventListener( $type, $listener, $useCapture )	{
		$listeners = null;
		if( $useCapture )
			$listeners = &$this->captureListeners;
		else
			$listeners = &$this->listeners;
		
		$found = false;
		$oListener = array();
		if( isset( $listeners[$type] ) )	{
			foreach( $listeners[$type] as &$pListeners )	{
				for( $c = 0, $max = count( $pListeners ); $c < $max; $c++ )	{
					if( $oListener[$c]['lst'] === $listener )	{
						$oListener = array_splice( $oListener, $c, 1 );
						$found = true;
					}
				}
			}
		}
		return $found;
	}
	/**
	 * @see Dispatcher::willTrigger
	 * @todo No es realmente igual a hasEventListener( )
	 */
	public function willTrigger( $type )	{
		return ( isset( $this->listeners[$type] ) || isset( $this->captureListeners[$type] ) );
	}
}
