<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_con1 = "localhost";
$database_con1 = "red5chat";
$username_con1 = "your username";
$password_con1 = "your password";
$con1 = mysql_pconnect($hostname_con1, $username_con1, $password_con1) or trigger_error(mysql_error(),E_USER_ERROR); 
?>