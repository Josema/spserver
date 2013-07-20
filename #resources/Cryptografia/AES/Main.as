package  {
	
	import flash.display.MovieClip;
	import flash.utils.ByteArray;
			

	import EncryptorECB;
	import EncryptorCBC;
	import com.hurlant.util.Base64;
	
	
	
	
	public class Main extends MovieClip {
		
		
		public function Main() {
			
			var encryptor:EncryptorECB = new EncryptorECB();
			encryptor.setKey("02306f485f385f6e154312as2");
			var enc:String = encryptor.encrypt("Blabla");
			trace(enc);
			trace(encryptor.decrypt(enc));
			trace(encryptor.decrypt("6xS20kAbzbobajSClnt6cQ=="));
			
			trace("\n-----------------\n");
			
			
			var encryptor2:EncryptorCBC = new EncryptorCBC();
			encryptor2.setKey("02306f485f385f6e154312as2");
			encryptor2.setIV("w58vplLZsrAMvZToObLedQ==");
			var enc2:String = encryptor2.encrypt("Blabla");
			trace(enc2);
			trace(encryptor2.decrypt(enc2));
			trace(encryptor2.decrypt("4lxj2Is0sbhwBETs3SCmag=="));

			
			


		}
		
		
		





		
	}
	
}
