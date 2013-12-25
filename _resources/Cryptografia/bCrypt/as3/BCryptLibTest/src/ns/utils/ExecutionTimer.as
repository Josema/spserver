package ns.utils
{
	import flash.utils.getTimer;

	/**
	 * 
	 */
	public final class ExecutionTimer
	{
		/**
		 * @private
		 */
		private static var _start:Number = 0;

		/**
		 * @private
		 */
		private static var _stop:Number = 0;
		
		/**
		 * @private
		 */
		private static var _time:Number = 0;
		
		// ---------------------------------------------------------------------------------------------------- //

		/**
		 * 
		 */
		public static function start():void
		{
			_start = getTimer();
		}
		
		// ---------------------------------------------------------------------------------------------------- //
		
		/**
		 * 
		 */
		public static function stop():Number
		{
			_stop = getTimer();
			_time = _stop - _start;
			_start = 0;
			_stop = 0;
			
			return _time;
		}
		
		// ---------------------------------------------------------------------------------------------------- //
		
		/**
		 * 
		 */
		public static function toString():String
		{
			return _time.toString();
		}
	}
}