package {
	
	import flash.text.TextField;
	import flash.display.SimpleButton;
	import flash.display.MovieClip;
	import flash.events.MouseEvent;
	import flash.utils.*;
	import com.jiggmin.blossomSocket.BlossomSocket;
	import com.jiggmin.blossomSocket.BlossomRoom;
	import com.jiggmin.blossomSocket.BlossomEvent;
	
	public class Ping extends MovieClip
	{
		
		private var socket:BlossomSocket;
		private var chatRoom:BlossomRoom;
		private var interval:uint;
		private var duration:Number = 1000;
		private var runing:Boolean = false;
		//private var textoadicional:String = "";
		
		
		public function Ping():void
		{
			socket = new BlossomSocket("localhost", 4041, "eWFDMU42UVRJd1JBRmYxWg==");      	//[Localhost]
			//socket = new BlossomSocket("208.78.96.138", 1708, "eWFDMU42UVRJd1JBRmYxWg==");   	//[Blossom Server]
			//socket = new BlossomSocket("91.121.85.216", 1708, "eWFDMU42UVRJd1JBRmYxWg==");   	//[Mi Servidor]
			
			chatRoom = new BlossomRoom(socket, "User Socket");
			
			chatRoom.addEventListener(BlossomEvent.RECEIVE_MESSAGE, receiveMessageHandler, false, 0, true);
			startUI.addEventListener(MouseEvent.CLICK, startTimer, false, 0, true);
			stopUI.addEventListener(MouseEvent.CLICK, stopTimer, false, 0, true);
		}
		

		
		private function receiveMessageHandler(be:BlossomEvent):void {
			var nowtimer = microtime();
			var corta = String(be.data).split('|',2);
			var diferencia = (nowtimer - Number(corta[0]));
			var bytes = (String(be.data).length * 8);
			var echo = "ID: " + corta[1] + ", Ping: " + diferencia + "|" + socket.pingTime +"ms, bytes: " + bytes;
			tracea( echo );
			if (runing && corta[1] == socket.socketID)
			{
				statusUI.text = echo;
				setTimeout(ping, duration);
			}
		}
		
		private function tracea(string:String):void
		{
			traceaUI.htmlText += string + "<br/>";
			traceaUI.scrollV = traceaUI.maxScrollV;
		}
		
		
		private function startTimer(me:MouseEvent):void
		{
			runing = true;
			setTimeout(ping, duration);
			//interval = setInterval(ping, duration);
		}
		
		private function stopTimer(me:MouseEvent):void
		{
			runing = false;
			//clearInterval(interval); 
		}
		
		
		private function ping():void {
			socket.sendPing();
			chatRoom.sendToRoom(microtime() + '|' + socket.socketID + '|' + textoadicionalUI.text);

		}
		
		public function microtime( get_as_float:Boolean = false ) : Number
		{  
			var now = new Date().getTime();  
			//var s = parseInt(now);  
		  
			return (get_as_float) ? (now/1000) : now;  
		}
		
		
	}
}