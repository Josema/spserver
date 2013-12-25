<?php
include("Encryptor.php");

$encryptor = new Encryptor();

$text = "Mensaje";
$key = "02306f485f385f6ed9ab6626052a633d";
$iv = 'b6772b9MZ0WY3i5KfxCYUQ=='; #$encryptor->generate_iv();
echo "iv: $iv<br>key: $key";







$encryptor->set_key($key);
$en = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, base64_decode($key), "Hola acosa", MCRYPT_MODE_ECB));
//$encryptor->set_iv($iv);
$enc = $encryptor->encrypt($text);
echo  "<br><br>$enc<br>\n";
$dec = $encryptor->decrypt($en);
echo  "<br><br>$dec<br>\n";


	
	
	

$en = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, base64_decode($key), "Hola acosa", MCRYPT_MODE_ECB));
echo $string = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, base64_decode($key), base64_decode($en), MCRYPT_MODE_ECB), "\0");

