package
{
	import caurina.transitions.Tweener;
	
	import fl.transitions.Tween;
	import fl.transitions.easing.Strong;
	
	import flash.display.MovieClip;
	import flash.events.Event;
	import flash.text.TextField;

	public class Player extends MovieClip implements IRenderable, ISynchable
	{
		protected var ourPlayerID:String;
		
		protected var ourShip:Ship;
		
		protected var ourIsDirty:Boolean; // has the data changed since the last communication?
		
		protected var ourVelocity:Number;
		
		protected var ourAcceleration:Number;
		
		protected var ourRotationAngle:Number;
		
		protected var ourChangedItems:Object;
		
		protected var ourUserID:String;
		
		protected var ourPlayerNameField:TextField;

		// Delay in Number of "Ticks / Frames" since last update
		protected var ourDelay:Number;
		
					
		function Player(myPlayerID:String)
		{
			ourPlayerID = myPlayerID;
			
			this.addEventListener(Event.ADDED, init);
			this.addEventListener(Event.REMOVED, cleanUp);
		}
		
		protected function init(myEvent:Event):void
		{

			ourChangedItems = new Object();
			ourShip = this["shipMC"];
			ourPlayerNameField = this["playerNameTxt"];
			ourPlayerNameField.text = "Player: "+ourPlayerID;
			
			ourVelocity = 0;
			ourAcceleration = 0;
			ourRotationAngle = 0;
			ourShip.rotation = ourRotationAngle * 180/Math.PI;
			ourDelay = 0;
		}
		
		protected function cleanUp(myEvent:Event):void
		{
		
		}
		
		public function rotateLeft():void
		{
			this.rotationAngle -= ourShip.rotationAngleIncrement;
			ourIsDirty = true;
			ourChangedItems.rotationAngle = ourRotationAngle;
		}
		
		public function rotateRight():void
		{			
			this.rotationAngle += ourShip.rotationAngleIncrement;
			ourIsDirty = true;
			ourChangedItems.rotationAngle = ourRotationAngle;
		}
		
		public function accelerate():void
		{
			var myOrigValue:Number = ourAcceleration;
			
			ourAcceleration = Math.min(ourAcceleration + ourShip.accelerationIncrement, ourShip.maxAcceleration);
			
			if(myOrigValue != ourAcceleration)
			{
				ourChangedItems.acceleration = ourAcceleration;
				ourIsDirty = true;
			}
		}
		
		public function decelerate():void
		{
			var myOrigValue:Number = ourAcceleration;
			
			ourAcceleration = Math.max(ourAcceleration - ourShip.accelerationIncrement, 0);
						
			if(myOrigValue != ourAcceleration)
			{
				ourChangedItems.acceleration = ourAcceleration;
				ourIsDirty = true;
			}			
			
		}
		
		public function update(myEnvironment:Environment):void
		{			
			var myDelayMultiplayer:Number = Math.min(1,ourDelay);
			ourVelocity += ourAcceleration*myDelayMultiplayer;
			
			if(ourVelocity >= 0)						
				ourVelocity -= ourVelocity * myEnvironment.drag * myDelayMultiplayer;
			
			ourVelocity = Math.max(ourVelocity,0);
			
			
			ourShip.rotation = ourRotationAngle * 180/Math.PI;
			this.x += ourVelocity * Math.cos(ourRotationAngle);
			this.y += ourVelocity * Math.sin(ourRotationAngle);		
			
			ourDelay = 1;
		}
		
		public function get playerID():String { return ourPlayerID; }
		
		public function get isDirty():Boolean { return ourIsDirty; }
		public function clearDirtyFlag():void { ourIsDirty = false; ourChangedItems = new Object(); }
		
		public function set acceleration(myVal:Number):void { ourAcceleration = myVal; }
		public function set rotationAngle(myVal:Number):void { ourRotationAngle = myVal;}
		
		public function set userID(myVal:String):void { ourUserID = myVal; }
		
		public function get velocity():Number { return ourVelocity; }
		public function get rotationAngle():Number { return ourRotationAngle;  }
		
		public function get changedProperties():Object 
		{			
			ourChangedItems.x = this.x;
			ourChangedItems.y = this.y;
			
			return ourChangedItems; 
		}
		
		public function set delay(myVal:int) { ourDelay = myVal; }
		
				
	}
}