package com.lia.documents {

	import com.lia.crypto.AES;
	import flash.display.Sprite;



	public class AESDemo extends Sprite {



		public function AESDemo() {
			var pw:String = "02306f485f385f6ed9ab6626052a633d";
			var enc:String = (AES.encrypt("Tu puta madre", pw, AES.BIT_KEY_128));
			var dec:String = (AES.decrypt(enc, pw, AES.BIT_KEY_128));
			trace(enc);
			trace(dec)
		}


	}
}
