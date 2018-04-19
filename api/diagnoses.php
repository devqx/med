<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/25/14
 * Time: 3:58 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.diagnoses.php';
$diag = new Diagnoses();
$program = $_REQUEST['program'];
if(isset($_POST['action']) && $_POST['action']=='save'){
    //save the diagnosis
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.patient.php';
    $p = new Manager();
    //echo ($p->saveDiagnosis($_POST['pid'],$_POST['cases'],$_POST['diagnosisNote']));
}else {
    if($program == "hiv"){$filter = TRUE;}
    echo $diag->listDiagnoses(NULL, $filter);
}

exit;