package
{
	import flash.display.MovieClip;

	public class Projectile extends MovieClip implements ISynchable, IRenderable
	{
		protected var ourObjectID:String;
		
		protected var ourOwnerUserID:String;
		
		protected var ourIsDirty:Boolean; // has the data changed since the last communication?
		
		protected var ourVelocity:Number;
		
		protected var ourAcceleration:Number;
				
		protected var ourChangedItems:Object;
		
		protected var ourRotationAngle:Number;
		
		protected var ourAccelerationIncrement:Number = .5;
		protected var ourMaxAcceleration:Number = 2;
		protected var ourMaxVelocity:Number = 20;
		
		protected var ourDragReduction:Number = 0.25; // Projectiles move faster and have less drag
		
		public function Projectile(myObjectID:String, myPlayer:Player)
		{
			ourObjectID = myObjectID;
			ourOwnerUserID = myPlayer.playerID;
			ourRotationAngle = myPlayer.rotationAngle;
			ourVelocity = myPlayer.velocity;
			ourAcceleration = 0;
			this.x = myPlayer.x;
			this.y = myPlayer.y;
			
			ourChangedItems = new Object();			
			ourChangedItems.rotationAngle = ourRotationAngle;
			ourChangedItems.velocity = ourVelocity;
			ourChangedItems.x = this.x;
			ourChangedItems.y = this.y;
			
			this.rotation = ourRotationAngle * 180/Math.PI;
			
			ourIsDirty = true;
			
			var myBullet:MovieClip = new Bullet();
			
			addChild(myBullet);	
			
		}
		
		public function get isDirty():Boolean
		{
			return ourIsDirty;
		}
		
		public function clearDirtyFlag():void
		{
			ourChangedItems = new Object();
			ourIsDirty = false;
		}
		
		public function update(myEnvironment:Environment):void
		{
			if(ourAcceleration < ourMaxAcceleration)
				ourAcceleration += ourAccelerationIncrement;
			
			ourVelocity += ourAcceleration;
									
			if(ourVelocity >= 0)						
				ourVelocity -= ourVelocity * myEnvironment.drag * ourDragReduction;
			
			ourVelocity = Math.max(ourVelocity,0);
			ourVelocity = Math.min(ourMaxVelocity,ourVelocity);
						
			this.x += ourVelocity * Math.cos(ourRotationAngle);
			this.y += ourVelocity * Math.sin(ourRotationAngle);
			
			if(ourVelocity == 0)
			{
				destroy();
			}		
		}
		
		public function destroy():void
		{
			this.visible = false;
			ourIsDirty = true;
			ourChangedItems.visible = false;
		}
		
		public function get changedProperties():Object 
		{			
			return ourChangedItems; 
		}
		
		public function get objectID():String { return ourObjectID; }
		
		public function set acceleration(myVal:Number):void { ourAcceleration = myVal; }
		public function set rotationAngle(myVal:Number):void { ourRotationAngle = myVal; }
		public function set velocity(myVal:Number):void { ourVelocity = myVal; }
		
	}
}