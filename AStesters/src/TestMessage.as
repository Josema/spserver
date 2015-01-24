package  {
	
	import flash.display.MovieClip;
	import com.net.AES;
	import com.net.Message;
	import flash.utils.ByteArray;
	
	public class TestMessage extends MovieClip {
		
		
		public function TestMessage()
		{
			var msg:* = {a: "Mi mensaje molón"};
			var wrap:ByteArray = Message.wrap(msg, null, true, true);
			var unwrap:Array = Message.unwrap(wrap);
			trace(unwrap[0].a)
		}
	}
	
}
