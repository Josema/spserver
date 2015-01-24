package
{
	import flash.display.MovieClip;
		
	public class Ship extends MovieClip
	{
		public var accelerationIncrement:Number = .1;
		public var maxAcceleration:Number = 1;
		public var maxVelocity:Number = 5;
		public var rotationAngleIncrement:Number = Math.PI/16;
		
		public function Ship()
		{
			
		}
		
	}
}