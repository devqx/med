<?php
if(!isset($_SESSION)){session_start();}
require $_SERVER['DOCUMENT_ROOT']. '/Connections/dbconnection.php';
mysql_select_db($database_dbconnection,$dbconnection);
$ret='';
if(isset($_SESSION['room'])){ $room = $_SESSION['room']; } else { $room = 0; }
$query = "select admissions.patient_id as patientID,admissions.bed_id, admissions.date_discharged, admissions.discharged_by, concat(bedspaces.bed_name,'(',bedspaces.room_type,')') as roomName from admissions left join bedspaces on admissions.bed_id=bedspaces.bed_id join patient_labs on admissions.patient_id = patient_labs.patient_id where (admissions.date_discharged IS NULL or admissions.discharged_by IS NULL )";

// $query = "SELECT lr.patient_id AS patientID , pl . * , ad . *, CONCAT(bd.bed_name, '(',bd.room_type,')') AS roomName
// 		FROM lab_requests lr, patient_labs pl, admissions ad, bedspaces bd WHERE lr.lab_group_id = pl.lab_group_id AND pl.test_value IS NULL 
// 		AND ad.patient_id = lr.patient_id AND ad.date_discharged IS NULL AND ad.bed_id =bd.bed_id GROUP BY pl.patient_id";

// $sqlStr = "SELECT lab_requests.patient_id AS patientID, CONCAT( bedspaces.room_type,  ' (', bedspaces.bed_name,  ') ' ) as roomName/*, patient_labs . * , admissions . * */
// FROM lab_requests, patient_labs, admissions, bedspaces
// WHERE lab_requests.lab_group_id = patient_labs.lab_group_id
// AND patient_labs.test_value IS NULL 
// AND admissions.patient_id = lab_requests.patient_id AND bedspaces.bed_id = admissions.bed_id
// GROUP BY patient_labs.patient_id";

// $sqlStr1 = "SELECT lab_requests.patient_id AS patientID , patient_labs . * , admissions . * 
// FROM lab_requests, patient_labs, admissions, bedspaces 
// WHERE lab_requests.lab_group_id = patient_labs.lab_group_id
// AND patient_labs.test_value IS NULL 
// AND admissions.patient_id = lab_requests.patient_id 
// GROUP BY patient_labs.patient_id";

//if (mysql_num_rows(mysql_query($sqlStr))!=0 ) {
	//$chk=mysql_query($sqlStr); ;
	$chk=mysql_query($query);
//}else {
	//$chk=mysql_query($sqlStr1); ;
//}
if(mysql_num_rows($chk)>0){
	$ret .= '<h4>Admitted Patients Labs</h4><ul>';
	$row=mysql_fetch_assoc($chk); 
	do{ require_once '../class.patient.php';
		$pt = new Manager();
		$ret .='<li><a href="'.$_SERVER['REQUEST_URI'].'/../index.php?search='.$row['patientID'].'">'.$pt->getPatientName($row['patientID']).' - '.$row['roomName'].'</a></li>';
	}while($row = mysql_fetch_assoc($chk));
	$ret .= '</ul>';
}
else { 
	$ret .= '<ul><li>No Patient on Queue</li></ul>';
}
echo $ret;
?>