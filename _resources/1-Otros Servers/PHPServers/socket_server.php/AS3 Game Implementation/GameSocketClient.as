package {
	
	import events.SocketRequestEvent;
	import events.SocketResponseEvent;
	
	import flash.display.Sprite;
	import flash.events.DataEvent;
	import flash.events.Event;
	import flash.net.XMLSocket;
	import flash.utils.getTimer;
	
	public class GameSocketClient extends Sprite
	{
		protected var ourXMLSocket:XMLSocket;
		protected var ourServerAddress:String;
		protected var ourServerPort:int;
		
		protected var ourPing:int;
		protected var ourStartRequestTime:int;
		
			
		public function GameSocketClient()
		{
			this.addEventListener(Event.ADDED_TO_STAGE,init);
			this.addEventListener(Event.REMOVED_FROM_STAGE,cleanUp);
		}
		
		protected function init(myEvent:Event):void
		{
			stage.addEventListener(SocketRequestEvent.CONNECT, connectToServer);
			stage.addEventListener(SocketRequestEvent.JOIN_GAME, passEventToServer);
			stage.addEventListener(SocketRequestEvent.CREATE_OBJECT, passEventToServer);
			stage.addEventListener(SocketRequestEvent.CHANGE_PLAYER_STATE, passEventToServer);
			stage.addEventListener(SocketRequestEvent.CHANGE_OBJECT_STATE, passEventToServer);
			
			ourXMLSocket = new XMLSocket();
			
			ourXMLSocket.addEventListener(DataEvent.DATA,parseResponse);
			ourXMLSocket.addEventListener(Event.CONNECT,connectedToServer);			
		}
		
		protected function cleanUp(myEvent:Event):void
		{
			stage.removeEventListener(SocketRequestEvent.CONNECT, connectToServer);
			stage.removeEventListener(SocketRequestEvent.JOIN_GAME, passEventToServer);
			stage.removeEventListener(SocketRequestEvent.CREATE_OBJECT, passEventToServer);
			stage.removeEventListener(SocketRequestEvent.CHANGE_PLAYER_STATE, passEventToServer);
			stage.removeEventListener(SocketRequestEvent.CHANGE_OBJECT_STATE, passEventToServer);

		}
		
		protected function connectedToServer(myEvent:Event):void
		{
			ourPing = flash.utils.getTimer() - ourStartRequestTime;
			var myResponseEvent:SocketResponseEvent = new SocketResponseEvent(SocketResponseEvent.CONNECTED,0,null);
			
			stage.dispatchEvent(myResponseEvent);
		}
		
		protected function connectToServer(myEvent:SocketRequestEvent):void
		{
			ourServerAddress = myEvent.parameters.address;
			ourServerPort = myEvent.parameters.port;
			
			ourStartRequestTime = flash.utils.getTimer();
			ourXMLSocket.connect(ourServerAddress, ourServerPort);
			
		}
		
		protected function passEventToServer(myEvent:SocketRequestEvent):void
		{
			if(!ourXMLSocket.connected)
				return;
				
			ourXMLSocket.send(buildXMLRequest(myEvent));
		}
		
		protected function buildXMLRequest(myEvent:SocketRequestEvent):XML
		{
			var myXML:XML = 
				<request>
					<type></type>
					<gameID></gameID>
					<data></data>
				</request>;
			
			myXML.type = myEvent.type;
			myXML.gameID = myEvent.gameID;
			myXML.data.ping = ourPing;
			
			for(var myStr:String in myEvent.parameters)
			{
				//trace(myEvent.parameters[myStr]+":"+typeof myEvent.parameters[myStr]);
				
				if(myEvent.parameters[myStr] is Number || myEvent.parameters[myStr] is String)
				{
					var xmlParam:XML = new XML("<"+myStr+">"+myEvent.parameters[myStr]+"</"+myStr+">");
					myXML.data.appendChild(xmlParam);
					
				}
				else
				{
					//trace("it is object");
					var xmlParamNode:XML = new XML("<"+myStr+"></"+myStr+">");
					
					for(var myInnerStr:String in myEvent.parameters[myStr])
					{
						var xmlInnerParam:XML = new XML("<"+myInnerStr+">"+myEvent.parameters[myStr][myInnerStr]+"</"+myInnerStr+">");
						xmlParamNode.appendChild(xmlInnerParam);	
					}
					
					
					
					myXML.data.appendChild(xmlParamNode);
				}	
			}
			//trace(myXML);
			return myXML;			
		}		
					
		protected function parseResponse(myEvent:DataEvent):void
		{
			var myXMLResponse:XML = XML(myEvent.data);
			//trace(myXMLResponse);
			var myData:Object = new Object();
			
			var myClientPing:int = 0;
			
			for each(var myChild:XML in myXMLResponse.data.children())
			{					
				//trace(myChild);
				if(myChild.hasSimpleContent())
				{						
					//trace("simple content");
					myData[myChild.name()] = myChild.text();					
				}
				else
				{
					//trace("complex content");
					var mySubObj:Object = new Object();
					for each(var mySubChild:XML in myChild.children())
					{
						if(mySubChild.name().toString() == "ping")
						{
							myClientPing = parseInt(mySubChild.text().toString());
							continue;
						}
						else
						{
							mySubObj[mySubChild.name().toString()] = mySubChild.text();
						}
					}
					myData[myChild.name()] = mySubObj;
				} 
			}					
			
			var myResponseEvent:SocketResponseEvent = new SocketResponseEvent("Response"+myXMLResponse.type.toString(), myClientPing + ourPing, myData);
			
			
			//trace(ObjectUtil.toString(myData));
			stage.dispatchEvent(myResponseEvent);

		}
	}
}
