package {
    import flash.display.Sprite;
    import flash.events.Event;
    import flash.events.MouseEvent;
	import flash.net.Socket;
	import flash.events.IOErrorEvent;
	import flash.events.SecurityErrorEvent;
	import flash.events.ProgressEvent;
	import flash.errors.IOError;
	import flash.utils.ByteArray;
	

	
	import com.net.AES;
	import com.net.Message;

    public class Tests extends Sprite {
		
		private var socket:Socket;
		private var aes:AES;

        public function Tests() {
            socket = new Socket();
			socket.addEventListener(Event.CLOSE, onCloseHandler);
			socket.addEventListener(Event.CONNECT, onConnectHandler);
			//socket.addEventListener(IOErrorEvent.IO_ERROR, ioErrorHandler);
			//socket.addEventListener(SecurityErrorEvent.SECURITY_ERROR, securityErrorHandler);
			socket.addEventListener(ProgressEvent.SOCKET_DATA, onSocketDataHandler);
			
			
			aes = new AES();
			//aes.setMode(AES.MODE_ECB);
			aes.setKey("02306f485f385f6ed9ab6626052a633d");
			//aes.setIV("0123456789abcdef");
			
			
			
			
			connectUI.addEventListener(MouseEvent.CLICK, conectar, false, 0, true);
			disconnectUI.addEventListener(MouseEvent.CLICK, function(e:Event):void{ tracea("closeByClient: "); socket.close(); }, false, 0, true);
			sendUI.addEventListener(MouseEvent.CLICK, function(e:Event):void{ enviar(textUI.text); }, false, 0, true);
			clearUI.addEventListener(MouseEvent.CLICK, function(e:Event):void{ traceaUI.text = ""; }, false, 0, true);
			
			conectar(null);
        }
		private function enviar(str:String):void {
			try {
				textUI.text = "";
				//socket.writeBytes(Message.wrap({a: str, b:1}, aes, false, true, true)); //falla 101
				socket.writeUTFBytes(str);
				socket.flush();
			}
			catch(e:IOError) {
				trace(e);
			}
		}
		private function onSocketDataHandler(event:ProgressEvent):void {

			var msg:ByteArray = new ByteArray();
			socket.readBytes(msg, 0, socket.bytesAvailable);

			//var obj:* = Message.unwrap(msg, aes);
			
			//tracea("onSocketDataHandler: " + obj.a);
			tracea("onSocketDataHandler: " + msg.toString());
		}
	

	
		private function onCloseHandler(event:Event):void {
			tracea("closeHandler: " + event);
		}
	
		private function onConnectHandler(event:Event):void {
			tracea("connectHandler: " + event);
		}
	
		/*private function ioErrorHandler(event:IOErrorEvent):void {
			tracea("ioErrorHandler: " + event);
		}
	
		private function securityErrorHandler(event:SecurityErrorEvent):void {
			tracea("securityErrorHandler: " + event);
		}*/
	
		private function conectar(e:Event):void{ traceaUI.text = ""; socket.connect(ipUI.text, int(portUI.text)); }
		
		private function tracea(string:String):void
		{
			traceaUI.htmlText += string + "<br/>";
			traceaUI.scrollV = traceaUI.maxScrollV;
		}
		

    }
}

