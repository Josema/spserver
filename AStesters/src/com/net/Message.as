package com.net
{
	import com.hurlant.util.Base64;
	import com.util.others.adobe.json.JSON;
	
	import flash.utils.ByteArray;

	public class Message
	{
		
		static public const SEPARATOR:int = 124;
		
		public static function wrap(message:*, aes:AES=null, base64:Boolean=false, compress:Boolean=true, forcecompress:Boolean=false) : ByteArray
		{
			var _message:ByteArray = new ByteArray();
			var _compress:int = (compress) ? 1 : 0;
			var _base64:int = (base64) ? 1 : 0;
			var _encryption:int = (aes == null) ? 0 : 1;
			var _json:int = 0;
			for (var i:* in message)
			{
				_json = 1;
				break;
			}
			

			//Converting to JSON
			if (_json)
				message = JSON.encode(message);     

			_message.writeUTFBytes(message);

			//Compresing
			if (_compress)
			{
				_message.deflate();
				if (_message.toString().length > message.length && !forcecompress)
				{
					_message = new ByteArray();
					_message.writeUTFBytes(message);
					_compress = 0;
				}
			}


			//Base64decoding
			if (_base64 || (_compress && _encryption))
			{
				message = Base64.encodeByteArray(_message);
				_message = new ByteArray();
				_message.writeUTFBytes(message);
			}


			//Encrypting
			if (_encryption)
				_message = aes.encrypt(_message);


			_message.writeByte(parseInt((String(_encryption) + String(_base64) + String(_compress) + String(_json)), 2));
			_message.writeByte(SEPARATOR);
			return _message;
		}
		
		
		
		public static function unwrap(message:ByteArray, aes:AES=null) : Array
		{
			message.position = 0;
			var _result:Array = new Array;
			var msgCompiled:ByteArray = new ByteArray();
			var byteN:int;
			var alert:Boolean = false;
			for (var i:int=0; i<message.length; i++)
			{
				byteN = message.readByte();
				if (alert && byteN == SEPARATOR)
				{
					alert = false;
					_result.push(unwrapMessage(msgCompiled, aes));
					msgCompiled = new ByteArray();
				}
				else
				{
					msgCompiled.writeByte(byteN);
					if (byteN < 16)
						alert = true;
				}
			}
			return _result;
		}


		private static function unwrapMessage(message:ByteArray, aes:AES=null) : *
		{
			var _result:*;
			var _message:ByteArray = new ByteArray();

			message.position = 0;
			message.readBytes(_message, 0, message.bytesAvailable-1);
			var instruccion:Array = Message.str_pad(
				uint(message.readByte()).toString(2)
			,4).split('');
			//trace(instruccion, _message.length, message.length)
			
			_message.position = 0;

			//Decrypting
			if (aes != null && instruccion[0] == '1')
			{
				_message = aes.decrypt(_message);
				_message.position = 0;
			}

			//Base64decoding
			if (instruccion[1] == '1')
			{
				var tpm:String = _message.toString();
				_message = Base64.decodeToByteArray(_message.readUTFBytes(_message.bytesAvailable));
				_message.position = 0;
			}

			//Uncompresing
			if (instruccion[2] == '1')
			{
				_message.inflate();
				_message.position = 0;
			}

			_result = _message.readUTFBytes(_message.bytesAvailable);

			//Converting to JSON
			if (instruccion[3] == '1')
			{
				var corta:Array = _result.split("}{");
				if (corta.length>1)
					_result = corta[0] + "}";
				_result = JSON.decode(_result);
			}

			return _result;
		}
		
		
		
		private static function str_pad (str:String, places:int, pad:String='0', side:String='left') : String
		{
			while (str.length < places)
			{
				if (side=="left")
					str = pad + str;
				else
					str = str + pad;
			}
			return str;
		}
	}
}