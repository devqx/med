<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.config.main.php';
$CFG = new MainConfig();
$hostname_dbconnection = MainConfig::$dbHost;
$database_dbconnection = MainConfig::$dbName;
$username_dbconnection = MainConfig::$dbUser;
$password_dbconnection = MainConfig::$dbPass;
$dbconnection = @mysql_pconnect($hostname_dbconnection, $username_dbconnection, $password_dbconnection) or die('error:Database Connection Failed');

