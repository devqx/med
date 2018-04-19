<?php

$cat = $_POST['category'];
$gen = $_POST['generics'];

if($cat=="--" && $gen == "--"){
	//nothing selected
	$filter = "";
}else if($cat !="--" && $gen == "--"){
	//only category filter
	$filter = "WHERE cater_ID = $cat";
}else if($cat == "--" && $gen != "--"){
	//only generic name filter
	$filter = "WHERE generic_name = $gen";
}else if($cat != "--" && $gen != "--"){
	//both cetagory and generic-name filter applied
	$filter = "WHERE cater_ID = $cat AND generic_name = $gen";
}

$sql = "SELECT * FROM drugs $filter";
require "Connections/dbconnection.php";
mysql_select_db($database_dbconnection, $dbconnection);
$query = $sql;
$result = mysql_query($query);
$row = mysql_fetch_assoc($result);
$ret = '';
if(mysql_num_rows($result)>=1){do {
	$ret .= '<option value='.$row['drug_id'].'>'.$row['drug_name'].' - '.ucwords($row['type']).'</option>';
}while($row = mysql_fetch_assoc($result));
}
echo $ret;
