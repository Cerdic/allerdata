<?php
$hostname_allerdata = "localhost";
$database_allerdata = "allerdata3";
$username_allerdata = "root";
$password_allerdata = "";
$allerdata = mysql_pconnect($hostname_allerdata, $username_allerdata, $password_allerdata) or trigger_error(mysql_error(),E_USER_ERROR); 
?>