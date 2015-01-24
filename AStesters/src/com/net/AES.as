package com.net  {
	
	import flash.utils.ByteArray;
	
	import com.hurlant.crypto.symmetric.AESKey;
	import com.hurlant.crypto.symmetric.CBCMode;
	import com.hurlant.crypto.symmetric.ECBMode;
	import com.hurlant.crypto.symmetric.IMode;
	import com.hurlant.crypto.symmetric.IVMode;

	public class AES
	{
		static public const MODE_ECB:String = 'ECB';
		static public const MODE_CBC:String = 'CBC';

		private const SIZE_KEY:int = 32;
		private const SIZE_IV:int = 16;

		private var padding:ZeroPad = new ZeroPad();
		private var mode:IMode;
		private var modeSelected:String = MODE_CBC;
		private var keyClass:AESKey;
		private var key:ByteArray;
		private var iv:ByteArray;
		
		
		
		public function setMode(modestr:String) : void
		{
			this.modeSelected = (modestr == MODE_ECB) ? MODE_ECB : MODE_CBC;
			if (this.mode != null)
			{
				if (this.mode is ECBMode && this.modeSelected == MODE_CBC)
				{
					this.mode = new CBCMode(keyClass, this.padding);
					if (this.iv != null)
						IVMode(this.mode).IV = this.iv;
				}
				else if (this.mode is CBCMode && this.modeSelected == MODE_ECB)
					this.mode = new ECBMode(this.keyClass, this.padding);
			}
		}
		public function getMode() : String
		{
			return this.modeSelected;
		}
		
		
		public function setKey(key:String, size:int = SIZE_KEY) : void
		{
			if (size != SIZE_KEY && size != 24 && size != 16)
				size = SIZE_KEY;

			this.key = this.resize(key, size);
			this.keyClass = new AESKey(this.key);
			this.mode = (this.modeSelected == MODE_ECB) ? new ECBMode(this.keyClass, this.padding) : new CBCMode(this.keyClass, this.padding);
		}
		public function getKey() : String
		{
			return this.key.toString();
		}
		
		
		public function setIV(iv:String) : void
		{
			this.iv = this.resize(iv, SIZE_IV);
			if (this.mode is IVMode)
				IVMode(this.mode).IV = this.iv;
		}
		public function getIV() : String
		{
			return this.iv.toString();
		}

		public function encrypt(data:ByteArray) : ByteArray
		{
			if (this.mode is CBCMode && this.iv==null)
				this.setIV(this.key.toString());
			
			var binary:ByteArray = data;
			mode.encrypt(binary);
			return binary;
		}
		public function decrypt(data:ByteArray) : ByteArray
		{
			if (this.mode is CBCMode && this.iv==null)
				this.setIV(this.key.toString());
			
			var binary:ByteArray = data;
			mode.decrypt(binary);
			return binary;
		}
		
		
		
		
		private function resize(string:String, size:int) : ByteArray
		{
			var resized:ByteArray;
			if (string.length != size)
			{
				var pad_string:ByteArray = this.stringToBA(string);
				for (var i:int=1; i<size; ++i)
					pad_string.writeByte(i);
				
				resized = new ByteArray();
				pad_string.position = 0;
				pad_string.readBytes(resized, 0, size);
			}
			else
				resized = this.stringToBA(string);
			
			return resized;
		}
		
		private function stringToBA(string:String) : ByteArray
		{
			var binary:ByteArray = new ByteArray();
			binary.writeUTFBytes(string);
			return binary;
		}

	}
	
	
	

	
}



import com.hurlant.crypto.symmetric.IPad;
import flash.utils.ByteArray;

internal class ZeroPad implements IPad
{
	private var blockSize:uint;
	private var char:String = String.fromCharCode(0);
	
	public function ZeroPad(blockSize:uint=0) {
		this.blockSize = blockSize;
	}
	
	public function pad(a:ByteArray):void {
		while(a.length % blockSize != 0){
			a.writeUTFBytes(char);
		}
	}
	public function unpad(a:ByteArray):void {
		a.position = 0;
		var string:String = a.readUTFBytes(a.bytesAvailable);
		string.split(char).join("");
		a.writeUTFBytes(string);
	}
	
	public function setBlockSize(bs:uint):void {
		blockSize = bs;
	}
	
}



	
	

	

