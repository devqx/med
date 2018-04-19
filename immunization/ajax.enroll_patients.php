<?php
$arr = $_POST['patient_to_be_enrolled'];
$type=$_POST['type'];
require $_SERVER['DOCUMENT_ROOT'].'/classes/class.patient.php';
$assessment = new Manager();

$ret = '';
if(count($arr)<=0 || !isset($arr)){
	exit('error');
}
for($i=0;$i<count($arr);$i++){
	$ret .= $assessment->enrollPatientToProgram($arr[$i],$type);
}

if($ret==""){
	echo 'ok';
}else{
	echo 'error'.$ret;
}
exit;