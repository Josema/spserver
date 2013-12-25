<?
  require 'AES.class.php';     // AES PHP implementation
  require 'AESCtr.class.php';  // AES Counter Mode implementation
  
  
 $str = "Hola mundo que tal estais";
 $pw = "603deb1015ca71be2b73aef0857d7781";
 $encr = AESCtr::encrypt($str, $pw, 256);
 $decr = AESCtr::decrypt("MZsCAC8vLy+cVhQ2j+c7/KlwypJN", $pw, 256);
 
 echo "<pre>$encr</pre><pre>$decr</pre>";