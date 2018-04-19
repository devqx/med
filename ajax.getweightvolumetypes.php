<?php
$drug = $_POST['drugname'];

$filter="";
if($drug!=""){
    //something selected
    $filter = "WHERE drug_id =". $drug;
}

$sql = "SELECT * FROM drugs ". $filter;
require "Connections/dbconnection.php";
mysql_select_db($database_dbconnection, $dbconnection);
$query = $sql;
$result = mysql_query($query);
$row = mysql_fetch_assoc($result);
$ret = '';
if (mysql_num_rows($result) > 0) {
   do   {
        if ($row['type'] == 'syrup') {
            $ret .= '<option value="ml">ml</option>';
            $ret .= '<option value="teaspoonful">teaspoonfuls</option>';
            $ret .= '<option value="tablespoonful">tablespoonfuls</option>';
        }else if ($row['type'] == 'IV') {
            $ret .= '<option value="ml">ml</option>';
        }else {
            $ret .= '<option value="' . $row['weight'] . '">' . $row['weight'] . '</option>';
        }
    }while ($row = mysql_fetch_assoc($result));
}
echo $ret.'<option>---</option>';
