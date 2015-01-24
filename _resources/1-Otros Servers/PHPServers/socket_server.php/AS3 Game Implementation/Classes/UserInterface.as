package
{
	import events.SocketRequestEvent;
	
	import flash.display.MovieClip;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import flash.text.TextField;
	
	public class UserInterface extends MovieClip
	{
		protected var ourAddressInput:TextField;
		protected var ourPortInput:TextField;
		public var ourGameIDInput:TextField;
		protected var ourConnectButton:MovieClip;
		protected var ourStatusDisplay:TextField;
		public var ourUserIDInput:TextField;
		
				
		public function UserInterface()
		{
			this.addEventListener(Event.ADDED_TO_STAGE, init);
			this.addEventListener(Event.REMOVED_FROM_STAGE, cleanUp);
		}
		
		protected function init(myEvent:Event):void
		{
			ourAddressInput = this["addressTxt"];
			ourPortInput = this["portTxt"];
			ourGameIDInput = this["gameIdTxt"];
			ourConnectButton = this["connectBtn"];
			ourStatusDisplay = this["statusTxt"];
			ourUserIDInput = this["userIdTxt"];
			
			ourConnectButton.buttonMode = true;
			ourConnectButton.addEventListener(MouseEvent.CLICK, connectToServer);
			
		}
		
		protected function connectToServer(myEvent:MouseEvent):void
		{
			dispatchEvent(new SocketRequestEvent(SocketRequestEvent.CONNECT, 1,{address: ourAddressInput.text, port: ourPortInput.text}));
		}
		
		protected function cleanUp(myEvent:Event):void
		{
			
		}
		
		public function changeStatus(newStatus:String):void
		{
			ourStatusDisplay.text = newStatus;
		}
	}
}