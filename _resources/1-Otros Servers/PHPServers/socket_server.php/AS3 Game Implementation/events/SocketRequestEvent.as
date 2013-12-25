package events
{	
	import flash.events.Event;

	public class SocketRequestEvent extends Event
	{
		public static var CONNECT:String = "Connect";
		
		// Requests
		public static var JOIN_GAME:String = "JoinGame";
		public static var CHANGE_PLAYER_STATE:String = "ChangePlayerState";
		public static var CREATE_OBJECT:String = "CreateObject";
		public static var CHANGE_OBJECT_STATE:String = "ChangeObjectState";
		
		protected var ourParams:Object;
		
		protected var ourGameID:int;
		
		public function SocketRequestEvent(type:String, myGameID:int, params:Object)
		{
			if(params != null)
				ourParams = params;
			else
				ourParams = new Object();
			
			ourGameID = myGameID;
				
			super(type, true, false);
		}
		
		public function get parameters():Object { return ourParams; }
		
		public function get gameID():int { return ourGameID; }
		
	}
}