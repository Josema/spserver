package
{
	import flash.display.Sprite;
	import flash.utils.ByteArray;

	
	public class as3corelib extends Sprite
	{
		public function as3corelib()
		{
			var str:String = "-----28 Nov 2011 – In addition, zlib compression and decompression are supported, as well as ... Used to determine whether the ActionScript 3.028 Nov 2011";
			trace(str.length, str)
			var resu:String = (compress(str));
			trace(resu.length, resu);
			trace(uncompress("DclJAQAwDAIwK5VQegH+jW3fZGc8sCrABNtphLmV2mRsy+TBv08gmvho1J3qAQ=="));
		}
		
		
		public static function compress( str:String ) :String
		{
			var b:ByteArray = new ByteArray();
			b.writeUTFBytes( str );
			b.deflate();
			return Base64.encodeByteArray( b );
		}
		
		public static function uncompress( str:String ) :String
		{
			var b:ByteArray = Base64.decodeToByteArray( str );
			b.inflate();
			return b.toString();
		}
	}
}