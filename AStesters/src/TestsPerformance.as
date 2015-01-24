package {
    import flash.display.Sprite;
    import flash.events.Event;
    import flash.events.MouseEvent;
	
	import flash.events.IOErrorEvent;
	import flash.events.SecurityErrorEvent;
	import flash.events.ProgressEvent;
	import flash.errors.IOError;
	import flash.utils.ByteArray;
	import flash.utils.*;

	
	import com.net.AES;
	import com.net.Message;
	
	import SocketMulti;
	import flash.net.Socket;
	import flash.events.TimerEvent;

    public class TestsPerformance extends Sprite {
		
		private var sockets:Array = new Array();
		private var aes:AES;
		private var sending:Boolean = false;
		private var conecteds:int;
		private var maxconecteds:int;
		private var hopeconnections:int;
		private var sends:int = 0;
		private var recived:int = 0;
		private var errors:int = 0;
		private var nounwrap:int = 0;
		private var running:Boolean = false;
		private var bytes:String;
		private var timelimit:Number = 0;
		private var timelimitback:int;

        public function TestsPerformance() 
		{
			
			//socket.addEventListener(IOErrorEvent.IO_ERROR, ioErrorHandler);
			//socket.addEventListener(SecurityErrorEvent.SECURITY_ERROR, securityErrorHandler);
			
			
			aes = new AES();
			//aes.setMode(AES.MODE_ECB);
			aes.setKey("02306f485f385f6ed9ab6626052a633d");
			//aes.setIV("0123456789abcdef");
			
			
			
			
			runUI.addEventListener(MouseEvent.CLICK, run, false, 0, true);
        }
		
		private function run(e:Event):void
		{ 
			var i:int,t:int;
			if (!sending && !running)
			{
				bytes = bytesUI.text;
				running = true;
				//traceaUI.htmlText = "";
				conecteds = 0;
				maxconecteds = 0;
				sends = 0;
				recived = 0;
				hopeconnections = int(clientsUI.text);
				sending = true;
				runUI.gotoAndStop(2);
				timelimit = Number(timelimitUI.text);
				
				if (timelimit > 0)
				{
					timelimitback = timelimit*60;
					timelimitbackUI.text = String(timelimitback);
					setTimeout(function() { run(null); }, timelimit*60*1000);
					var temporizador:Timer=new Timer(1000,timelimitback);
					temporizador.addEventListener(TimerEvent.TIMER, function accionRepetida(e:TimerEvent){
						timelimitbackUI.text = String(--timelimitback);
					});
					temporizador.start();
					
				}
				
				for (i=0,t=hopeconnections; i<t; ++i)
				{
					sockets[i] = new SocketMulti();
					sockets[i].id = i;
					sockets[i].addEventListener(Event.CLOSE, onCloseHandler);
					sockets[i].addEventListener(Event.CONNECT, onConnectHandler);
					sockets[i].addEventListener(ProgressEvent.SOCKET_DATA, onSocketDataHandler);
					sockets[i].addEventListener(IOErrorEvent.IO_ERROR, ioErrorHandler);
					sockets[i].addEventListener(SecurityErrorEvent.SECURITY_ERROR, securityErrorHandler);
					conectar(sockets[i]);
				}
			}
			else
			{
				runUI.gotoAndStop(3);
				sending = false;
			}
		}
		
		private function enviar(socket:SocketMulti):void
		{
			setTimeout(function() {
				SocketMulti(socket).sends += 1
				sendsUI.text = String(++sends);
				SocketMulti(socket).lasttime = getTimer();
				SocketMulti(socket).writeBytes(Message.wrap({bytes: bytes}, aes, true, true, true));
				//SocketMulti(socket).writeUTFBytes(bytes);
				SocketMulti(socket).flush();
			},randomNumber(0,2000));
		}
		private function onSocketDataHandler(event:ProgressEvent):void
		{
			var msg:ByteArray = new ByteArray();
			SocketMulti(event.target).readBytes(msg, 0, SocketMulti(event.target).bytesAvailable);
			var obj:* = Message.unwrap(msg, aes);


			
			if (obj.bytes == bytes)
				recivedUI.text = String(++recived);
			else
				nounwrapUI.text = String(++nounwrap);

			if (sending)
			{
				//trace("resta:",getTimer() - SocketMulti(event.target).lasttime)
				pingUI.text = String(getTimer() - SocketMulti(event.target).lasttime);
				SocketMulti(event.target).totaltime += getTimer() - SocketMulti(event.target).lasttime;
				enviar(SocketMulti(event.target));
			}
			else
			{
				connectedsUI.text = String(--conecteds);
				SocketMulti(event.target).close();
				if (conecteds == 0)
				{
					running = false;
					runUI.gotoAndStop(1);
					var sumasecs:Number = 0;;
					var sectemp:Number;
					for (var i:int=0,t:int=sockets.length; i<t; ++i)
					{
						if (sockets[i].conectado)
						{
							sectemp = (sockets[i].totaltime/sockets[i].sends);
							sumasecs += sectemp;
							//tracea("TIME RESPONSE #" +  i + " [" + sectemp + "/s] [Sends: " + sockets[i].sends + "]");
						}
					}
					tracea("====================================");
					tracea("MAX CONNECTIONS: " + maxconecteds + "/" + hopeconnections + " [" + Math.round(maxconecteds*100/hopeconnections) + "%]");
					tracea("RECIVED/SENDS: " + sends + "/" + recived + " [" + Math.round(recived*100/sends) + "%]");
					tracea("AVERAGE PING: " + Math.round(sumasecs/i) + "ms");
					tracea("BYTES SEND: " + decimal(((sends*((bytes).length)/1024)/1024),3) + " Mbytes");
					tracea("====================================");
				}
			}
		}
	

		private function onConnectHandler(event:Event):void
		{
			
			connectedsUI.text = String(++conecteds);
			if (conecteds > maxconecteds)
				maxconecteds = conecteds;
			SocketMulti(event.target).conectado = true;
			enviar(SocketMulti(event.target));
		}
		private function onCloseHandler(event:Event):void
		{
			connectedsUI.text = String(--conecteds)
			SocketMulti(event.target).conectado = false;
			//tracea("DISCONNECTED BY SERVER: #" + event.target.id);
		}
		private function ioErrorHandler(event:IOErrorEvent):void {
			errorsUI.text = String(++errors);
			tracea("ioErrorHandler: #" + event.target.id + " " + event.text);
		}
	
		private function securityErrorHandler(event:SecurityErrorEvent):void {
			errorsUI.text = String(++errors);
			tracea("securityErrorHandler: #" + event.target.id + " " + event.text);
		}
	
		
		private function conectar(socket:SocketMulti)
		{
			setTimeout(function() {
				socket.connect(ipUI.text, int(portUI.text));
			},randomNumber(0,hopeconnections*50));
		}
		
		
		private function tracea(string:*):void
		{
			traceaUI.htmlText += string + "<br/>";
			traceaUI.scrollV = traceaUI.maxScrollV;
		}
		private function randomNumber(low:Number=0, high:Number=1):Number
		{
		  return Math.floor(Math.random() * (1+high-low)) + low;
		}
		private function decimal(n:Number=0, decimal:int=5):Number
		{
			var tens:int = Math.pow(10, decimal);
			return int((n)*tens)/tens;
		}
    }
}

