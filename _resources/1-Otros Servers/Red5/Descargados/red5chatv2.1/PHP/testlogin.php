<?php require_once('Connections/con1.php'); ?>
<?php
	$pseudo=$_POST["pseudo"];
	$password=$_POST["password"];
	mysql_select_db($database_con1, $con1);

// test if the IP is not banned !
//$sql="select ip from bannedips where ip=''";

// test if the user is registered !
	$query_users = "SELECT * FROM users where pseudo='$pseudo' and password='$password' and banned=0";
	$users = mysql_query($query_users, $con1) or die(mysql_error());
	$row_users = mysql_fetch_assoc($users);
	$totalRows_users = mysql_num_rows($users);
	$sex = $row_users["sex"];
	$role = $row_users["role"];
	if ($totalRows_users==0) die("status=ko"); else die("status=ok&sex=$sex&role=$role");
	mysql_free_result($users);
?>
