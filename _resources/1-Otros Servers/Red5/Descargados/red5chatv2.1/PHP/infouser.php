<?php require_once('Connections/con1.php'); ?>
<?php
	$pseudo=$_POST["pseudo"];
	mysql_select_db($database_con1, $con1);

// test if the IP is not banned !
//$sql="select ip from bannedips where ip=''";

// test if the user is registered !
	$query_users = "SELECT * FROM users where pseudo='$pseudo'";
	$users = mysql_query($query_users, $con1) or die(mysql_error());
	$row_users = mysql_fetch_assoc($users);
	$totalRows_users = mysql_num_rows($users);
	$age= $row_users["age"];
	$description = $row_users["description"];
	$country = $row_users["country"];
	if ($totalRows_users==0) die("status=ko"); else die("status=ok&age=$age&description=$description&country=$country");
	mysql_free_result($users);
?>
