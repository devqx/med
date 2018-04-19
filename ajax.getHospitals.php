<?php
require "Connections/dbconnection.php";
mysql_select_db ( $database_dbconnection, $dbconnection );
$lga = mysql_real_escape_string($_GET['lga']);
$sql = "SELECT * FROM clinic WHERE lga ='" . $lga. "'";

$chk = mysql_query ( $sql, $dbconnection );
$row_data = mysql_fetch_assoc ( $chk );
$retVal = '';
do {
	$retVal .= '<option value="'.$row_data['clinicID'].'">'.$row_data ['FullName'] . '</option>';
} while ( $row_data = mysql_fetch_assoc ( $chk ) );
echo $retVal;