<?php
$str="caca de vacacaca de vacacaca de vacacaca de vacacaca de vacacaca de vacacaca de vacacaca de vacacaca de vacacaca de vacacaca de vacacaca de vacacaca de vacacaca de vaca";
$time = time();



$resu = gzdeflate($str,9);




echo "<br><textarea cols='200' rows='10'>$resu</textarea><br>";
echo strlen($str) . " " . strlen($resu) . "<br>";







