<?php
/**
 * Dispatcher
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
 * Dispatcher
 * 
 * The Dispatcher interface defines methods for adding or removing event listeners, 
 * checks whether specific types of event listeners are registered, and dispatches events.
 * 
 * Event targets are an important part of the Module/Plugin event model.
 * The event target serves as the focal point for how events flow through the display list hierarchy.
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
 * @todo add target currentTarget eventDispacher
 */
interface IEventDispatcher {
	/**
	 * Registers an event listener object with an EventDispatcher object so that the listener receives notification of an event.
	 * 
	 * You can register event listeners for a specific type of event, phase, and priority.
	 * After you successfully register an event listener, you cannot change its priority through additional calls to addEventListener(). To change a listener's priority, you must first call removeEventListener().
	 * Then you can register the listener again with the new priority level.
	 * 
	 * After the listener is registered, subsequent calls to addEventListener() with a different value for 
	 * either type or useCapture result in the creation of a separate listener registration.
	 * 
	 * For example, if you first register a listener with useCapture set to true, it listens only during the capture phase.
	 * If you call addEventListener() again using the same listener object, but with useCapture set to false, you have two separate listeners: 
	 * one that listens during the capture phase, and another that listens during the target and bubbling phases.
	 * 
	 * You cannot register an event listener for only the target phase or the bubbling phase.
	 * Those phases are coupled during registration because bubbling applies only to the ancestors of the target node.
	 * 
	 * When you no longer need an event listener, remove it by calling EventDispatcher.removeEventListener(); otherwise, 
	 * memory problems might result. Objects with registered event listeners are not automatically removed from memory because 
	 * the garbage collector does not remove objects that still have references.
	 * 
	 * @param mixed $object The object containing the listener action.
	 * @param string|array $types The type of event. {@see Event}
	 * @param callback $listener The listener function that processes the event.
	 * This function must accept an event object as its only parameter and must return nothing.
	 * @param boolean $useCapture Determines whether the listener works in the capture phase or the target and bubbling phases.
	 * If useCapture is true,  the listener processes the event only during the capture phase and not in the target or bubbling phase.
	 * If useCapture is false, the listener processes the event only during the target or bubbling phase.
	 * To listen for the event in all three phases, call addEventListener() twice, once with useCapture set to true, then again with false.
	 * @param int $priority The priority level of the event listener. Priorities are designated by a integer.
	 * The higher the number, the higher the priority. 
	 * All listeners with priority n are processed before listeners of priority n-1. 
	 * If two or more listeners share the same priority, they are processed in the order in which they were added.
	 * The default priority is 0.
	 */
	public function addEventListener( $types, $listener, $object=NULL, $useCapture = false, $priority = 0 );
	/**
	 * Dispatches an event into the event flow.
	 * The event target is the EventDispatcher object upon which dispatchEvent() is called. 
	 * 
	 * @param Event $event The event object dispatched into the event flow. 
	 * @return boolean $dispatched A value of true unless preventDefault() is called on the event, in which case it returns false. 
	 */
	public function dispatchEvent( $event );
	/**
	 * Checks whether the EventDispatcher object has any listeners registered for a specific type of event.
	 * 
	 * This allows you to determine where an EventDispatcher object has altered handling of an event type in the event flow hierarchy.
	 * To determine whether a specific event type will actually trigger an event listener, use Dispatcher::willTrigger( ).
	 * 
	 * The difference between hasEventListener() and willTrigger() is that hasEventListener() examines only the object to which it belongs, 
	 * whereas willTrigger() examines the entire event flow for the event specified by the type parameter.
	 * 
	 * @param string $type The type of event.
	 * @return boolean A value of true if a listener of the specified type is registered. 
	 */
	public function hasEventListener( $type );
	/**
	 * Removes a listener from the EventDispatcher object.
	 * 
	 * If there is no matching listener registered with the EventDispatcher object, a call to this method has no effect. 
	 * 
	 * @param string $type The type of event.
	 * @param callback $listener The listener object to remove.
	 * @param boolean $useCapture Specifies whether the listener was registered for the capture phase or the target and bubbling phases.
	 * If the listener was registered for both the capture phase and the target and bubbling phases, two calls to removeEventListener() are required to remove both. 
	 * @return boolean Whether or not the Listener was removed.
	 */
	public function removeEventListener( $type, $listener, $useCapture );
	/**
	 * Checks whether an event listener is registered with this EventDispatcher object or any of its ancestors for the specified event type.
	 * This method returns true if an event listener is triggered during any phase of the event flow when an event of the specified 
	 * type is dispatched to this EventDispatcher object or any of its descendants.
	 * 
	 * The difference between hasEventListener() and willTrigger() is that hasEventListener() examines only the object to which it belongs, 
	 * whereas willTrigger() examines the entire event flow for the event specified by the type parameter.
	 * 
	 * @param string $type The type of event.
	 * @return boolean A value of true if a listener of the specified type will be triggered. 
	 */
	public function willTrigger( $type );
}
