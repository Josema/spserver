<?php 
include('bCrypt.class.php');


$bcrypt = new Bcrypt(15);

$hash = $bcrypt->hash('password');
$isGood = $bcrypt->verify('password', $hash);

echo $isGood;
