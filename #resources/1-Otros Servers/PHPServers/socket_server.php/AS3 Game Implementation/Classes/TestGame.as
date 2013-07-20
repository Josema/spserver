package
{
	import com.chewtinfoil.utils.StringUtils;
	import com.senocular.utils.KeyObject;
	
	import events.SocketRequestEvent;
	import events.SocketResponseEvent;
	
	import flash.display.Bitmap;
	import flash.display.BitmapData;
	import flash.display.MovieClip;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.KeyboardEvent;
	import flash.events.TimerEvent;
	import flash.geom.Point;
	import flash.ui.Keyboard;
	import flash.utils.Timer;
	import flash.utils.getTimer;
	
	public class TestGame extends MovieClip
	{
	
		protected var ourSocketClient:GameSocketClient;
		protected var ourPlayer:Player;
		protected var ourUI:UserInterface;
		
		// Game Variables
		protected var ourGameID:int;
		protected var ourUserID:int;
		
		protected var ourTimer:Timer;
		
		protected var ourPlayingField:Sprite;
		
		protected var ourRenderedFrame:Bitmap;
		protected var ourBitmapData:BitmapData;
		
		protected var ourGameWindowDimensions:Point = new Point(800,500);
		
		protected var ourRenderableItems:Array;
		
		protected var ourKeyObject:KeyObject;
		
		protected var ourEnvironment:Environment;
		
		protected var ourPlayers:Object;
		
		protected var ourObjectCount:Number;
		
		protected var ourObjects:Object;
		
		protected var ourFiringRateTimeout:int;
		
		protected var ourLastFiredTime:int;
		
		public function TestGame()
		{
			this.addEventListener(Event.ADDED_TO_STAGE, init);
			this.addEventListener(Event.REMOVED_FROM_STAGE, cleanUp);
		}

		protected function init(myEvent:Event):void
		{			
			ourSocketClient = new GameSocketClient();
			addChild(ourSocketClient);
			stage.addEventListener(SocketResponseEvent.CONNECTED, onConnected);
			stage.addEventListener(SocketResponseEvent.JOIN_GAME_CONFIRMATION, onJoinedGame);
			stage.addEventListener(SocketResponseEvent.PLAYER_JOINED, onPlayerJoined);
			stage.addEventListener(SocketResponseEvent.PLAYER_DISCONNECTED, onPlayerDisconnected);
			stage.addEventListener(SocketResponseEvent.PLAYER_STATE_CHANGED, onPlayerStateChanged);
			stage.addEventListener(SocketResponseEvent.OBJECT_CREATED, onObjectCreated);
			stage.addEventListener(SocketResponseEvent.OBJECT_STATE_CHANGED, onObjectStateChanged);
			
			ourTimer = new Timer(33);
			ourTimer.addEventListener(TimerEvent.TIMER, processFrame);
			
			ourUI = this["uiMC"];
			ourRenderedFrame = new Bitmap();
			this.addChildAt(ourRenderedFrame,0);
			
			ourPlayingField = new Sprite();
						
			ourKeyObject = new KeyObject(stage);
						
			ourRenderableItems = new Array();
			ourPlayers = new Object();
			
			ourEnvironment = new Environment();
			
			ourObjectCount = 0;
			
			ourObjects = new Object();
			
			ourFiringRateTimeout = 200;
			
			ourLastFiredTime = 0;
		
			
		}
		
		protected function cleanUp(myEvent:Event):void
		{
			stage.removeEventListener(SocketResponseEvent.JOIN_GAME_CONFIRMATION, onJoinedGame);
			stage.removeEventListener(SocketResponseEvent.PLAYER_JOINED, onPlayerJoined);
			stage.removeEventListener(SocketResponseEvent.PLAYER_DISCONNECTED, onPlayerDisconnected);
			stage.removeEventListener(SocketResponseEvent.PLAYER_STATE_CHANGED, onPlayerStateChanged);
			stage.removeEventListener(SocketResponseEvent.OBJECT_CREATED, onObjectCreated);
			stage.removeEventListener(SocketResponseEvent.OBJECT_STATE_CHANGED, onObjectStateChanged);
		}
		
		protected function onConnected(myEvent:SocketResponseEvent):void
		{
			ourUI.changeStatus("Connected to Server.");
			
			ourGameID = int(ourUI.ourGameIDInput.text);
			ourUserID = int(ourUI.ourUserIDInput.text);
			
			stage.dispatchEvent(new SocketRequestEvent(SocketRequestEvent.JOIN_GAME, ourGameID,{userID: ourUserID}));
		}
		
		protected function processFrame(myEvent:TimerEvent):void
		{
			//processInput(null);
			
			for(var i:int = 0; i < ourRenderableItems.length; i++)
			{
				ourRenderableItems[i].update(ourEnvironment);
				if(!boundaryCheck(ourRenderableItems[i]))
				{
					if(ourRenderableItems[i] is Projectile)
						ourRenderableItems[i].destroy();
				}
			}
				
			ourBitmapData = new BitmapData(ourGameWindowDimensions.x,ourGameWindowDimensions.y, true);
			
			ourBitmapData.draw(ourPlayingField);
			ourRenderedFrame.bitmapData = ourBitmapData;
			
			/*
			if(ourPlayer.isDirty)
			{
				sendPlayerChanges(ourPlayer);
			}
			*/
					
		}
		
		protected function boundaryCheck(myObject:MovieClip):Boolean
		{
			if(myObject.x < 0)
			{
				myObject.x = 0;
				return false;
			}
				
			if(myObject.y < 0)
			{
				myObject.y = 0;
				return false;
			}
				
			if(myObject.x > ourGameWindowDimensions.x)
			{
				myObject.x = ourGameWindowDimensions.x;
				return false;
			}
			
			if(myObject.y > ourGameWindowDimensions.y)
			{
				myObject.y = ourGameWindowDimensions.y;
				return false;
			}
			
			return true;
			
			
		}
		
		protected function onJoinedGame(myEvent:SocketResponseEvent):void
		{	
			ourPlayer = new Player(myEvent.parameters.player_id);
			
			ourPlayer.x = stage.stageWidth/2;
			ourPlayer.y = stage.stageHeight/2;
						
			ourPlayingField.addChild(ourPlayer);
			
			ourRenderableItems.push(ourPlayer);
			
			ourUI.changeStatus("Joined Game Successfully. Player ID: "+ourPlayer.playerID+", Ping: "+myEvent.delay);
			
			
			startGame();
		}
		
		protected function startGame():void
		{
			ourTimer.start();
			this.addEventListener(Event.ENTER_FRAME, processInput);
			
		}
		
		protected function processInput(event:Event):void 
		{						
			if(ourKeyObject.isDown(Keyboard.LEFT))
				ourPlayer.rotateLeft();
			if(ourKeyObject.isDown(Keyboard.RIGHT))
				ourPlayer.rotateRight();
			if(ourKeyObject.isDown(Keyboard.UP))
				ourPlayer.accelerate();
			else
				ourPlayer.decelerate();
				
			if(ourKeyObject.isDown(Keyboard.SPACE))
				createProjectile();
			
			if(ourPlayer.isDirty)
			{
				sendPlayerChanges(ourPlayer);
			}
			
		}
		
		protected function createProjectile():void
		{
			var myTime:int = flash.utils.getTimer();
			if(myTime - ourLastFiredTime < ourFiringRateTimeout)
				return;
				
			ourLastFiredTime = myTime;
			
			var myNewId:String = ourPlayer.playerID + StringUtils.padLeft(ourObjectCount.toString(),"0",6);
			var myProjectile:Projectile = new Projectile(myNewId,ourPlayer);
						
			ourPlayingField.addChild(myProjectile);
			
			ourRenderableItems.push(myProjectile);
			
			ourObjectCount++;
			
			ourObjects[myNewId] = myProjectile;
			
			stage.dispatchEvent(new SocketRequestEvent(SocketRequestEvent.CREATE_OBJECT, ourGameID,{objectID: myProjectile.objectID, properties: myProjectile.changedProperties}));
			
		}
		
		protected function sendPlayerChanges(myPlayer:Player):void
		{			
			stage.dispatchEvent(new SocketRequestEvent(SocketRequestEvent.CHANGE_PLAYER_STATE, ourGameID,myPlayer.changedProperties));
			myPlayer.clearDirtyFlag();
		} 
		
		protected function onPlayerJoined(myEvent:SocketResponseEvent):void
		{
			ourUI.changeStatus("New Player Joined: "+myEvent.parameters.player_id+", Ping: "+myEvent.delay);
			
			var myNewPlayer:Player = new Player(myEvent.parameters.player_id);
			
			myNewPlayer.x = stage.stageWidth/2;
			myNewPlayer.y = stage.stageHeight/2;
			
			for(var i:String in myEvent.parameters.properties)
			{
				myNewPlayer[i] = myEvent.parameters.properties[i];
			}		
						
			ourPlayingField.addChild(myNewPlayer);
			
			ourRenderableItems.push(myNewPlayer);
			
			ourPlayers[myEvent.parameters.player_id] = myNewPlayer;
		}
		
		protected function onPlayerDisconnected(myEvent:SocketResponseEvent):void
		{
			var myPlayer:Player = ourPlayers[myEvent.parameters.player_id];
			ourPlayingField.removeChild(myPlayer);
			ourRenderableItems.splice(ourRenderableItems.indexOf(myPlayer),1);			
			ourPlayers[myEvent.parameters.player_id] = null;
		}	
		
		protected function onPlayerStateChanged(myEvent:SocketResponseEvent):void
		{
			ourUI.changeStatus("Player State Changed: "+myEvent.parameters.player_id+", Ping: "+myEvent.delay);
			
			var myPlayer:Player = ourPlayers[myEvent.parameters.player_id];
			
			for(var i:String in myEvent.parameters.properties)
			{
				myPlayer[i] = myEvent.parameters.properties[i];
			}
			
			myPlayer.delay = myEvent.delay / ourTimer.delay;
			
			//myPlayer.update(ourEnvironment);
		}	
		
		protected function onObjectCreated(myEvent:SocketResponseEvent):void
		{
			ourUI.changeStatus("Object Created: "+myEvent.parameters.objectID+", Ping: "+myEvent.delay);
			
			var myPlayer:Player = ourPlayers[myEvent.parameters.player_id];
			
			var myProjectile:Projectile = new Projectile(myEvent.parameters.objectID,myPlayer);
									
			for(var i:String in myEvent.parameters.properties)
			{
				//trace(i);
				//trace(myEvent.parameters.properties[i]);
				myProjectile[i] = myEvent.parameters.properties[i];
			}
						
			ourPlayingField.addChild(myProjectile);
			
			ourRenderableItems.push(myProjectile);
						
			ourObjects[myEvent.parameters.objectID] = myProjectile;
		}
		
		protected function onObjectStateChanged(myEvent:SocketResponseEvent):void
		{
			
		}
	}
	

}