package  {
	
	import flash.net.Socket;
	
	public class SocketMulti extends Socket {

		public var id:int;
		public var sends:int;
		public var totaltime:int = 0;
		public var lasttime:int;
		public var conectado:Boolean = false;
	}
	
}
