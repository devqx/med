<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_dbconnection = "localhost";
$database_dbconnection = "surveillance";
$username_dbconnection = "root";
$password_dbconnection = "peter";
$dbconnection = mysql_pconnect($hostname_dbconnection, $username_dbconnection, $password_dbconnection) or trigger_error(mysql_error(), E_USER_ERROR);

