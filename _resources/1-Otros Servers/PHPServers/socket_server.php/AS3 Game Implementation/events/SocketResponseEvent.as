package events
{	
	import flash.events.Event;

	public class SocketResponseEvent extends Event
	{		
		// Responses
		public static var CONNECTED:String = "ResponseConnected";
		public static var JOIN_GAME_CONFIRMATION:String = "ResponseJoinGameConfirmation";
		public static var PLAYER_JOINED:String = "ResponsePlayerJoined";
		public static var PLAYER_DISCONNECTED:String = "ResponsePlayerDisconnected";
		public static var PLAYER_STATE_CHANGED:String = "ResponsePlayerStateChanged";
		public static var OBJECT_CREATED:String = "ResponseObjectCreated";
		public static var OBJECT_STATE_CHANGED:String = "ResponseObjectStateChanged";
		
		protected var ourParams:Object;
		protected var ourDelay:int;
		
		public function SocketResponseEvent(type:String, myDelay:int, params:Object)
		{
			if(params != null)
				ourParams = params;
			else
				ourParams = new Object();
				
			ourDelay = myDelay;
					
			super(type, true, false);
		}
		
		public function get parameters():Object { return ourParams; }
		public function get delay():int { return ourDelay; };
		
	}
}