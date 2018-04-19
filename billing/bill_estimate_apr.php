<?php
/**
 * Created by PhpStorm.
 * User: nnamdi
 * Date: 4/13/17
 * Time: 11:27 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/EstimatedBillsDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/EstimatedBillLineDAO.php';
//require_once $_SERVER['DOCUMENT_ROOT'].'classes/DAOs/PatientProcedureDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/EstimatedBills.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/EstimatedBillLine.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
$pdo = (new MyDBConnector())->getPDO();

$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
$pid = isset($_GET['id'])? $_GET['id']: '';
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;

$es_bills = (new EstimatedBillsDAO())->EstimatedBills(null,$page,$pageSize);
$total = $es_bills->total+1;
if ($total < 10){
    $code = '000'.$total;
}
elseif ($total < 100){
    $code = '00'.$total;
}
elseif ($total < 1000){
    $code = '0'.$total;
}
else{
    $code = $total;
}
$service_category = (new BillSourceDAO())->getBillSources();

if ($_POST){
    //$aid = isset($_POST['inpatient_id'])? $_POST['inpatient_id']:1;
    $estimate_gen = new EstimatedBills();
    $estimate_gen->setPatient((new PatientDemographDAO())->getPatient($_POST['pid'],False));
    $estimate_gen->setInpatient(1);

    $escode = 'ESB'.$code;
    $estimate_gen->setEsCode($escode);
    $narration = $_POST['narration'];
    $valid = $_POST['valid_till'];
    $scheme_id = $_POST['scheme_id'];
    $app_print = $_POST['a_p'];

    $estimate_gen->setTotalEstimate(floatval($_POST['total']));
    $today = date('Y-m-d H:i:s');
    $estimate_gen->setDateCreated($today);
    $estimate_gen->setLastModified($today);
    $estimate_gen->setScheme($scheme_id);
    $estimate_gen->setNarration($narration);
    $estimate_gen->setValidTill($valid);
    $estimate_gen->setCreatedBy($this_user);
    $estimate_gen->setStatus('draft');


    if ($valid === ''){
        exit("error: Please Choose a valid date");
    }

    if ($valid < date('Y-m-d')){
        exit('error: Please choose recent date');
    }

    if ($narration === ''){
        exit("error: Please write a narration");
    }

    if ($_POST['pid'] === ''){
        exit("error:Please select a patient");
    }

    $es_bill_lines = !is_blank($_POST['item_request'])? $_POST['item_request']:'';
    if ($es_bill_lines === ''){
        exit("error: You have not added any item");
    }

    $data = json_decode($es_bill_lines);

    //$estimate_gen->setEstimateBillLines($data);
    $pdo->beginTransaction();
    $es = (new EstimatedBillsDAO())->AddEstimatedBill($estimate_gen,$pdo);
    if ($es === null){
        $pdo->rollBack();
        exit("error: Failed to generate estimated bill");
    }
    $lastId = $pdo->lastInsertId();
    //$bill_lines = new EstimatedBillLine();
    $pref_lines = [];
    foreach ($data as $es_lines){
        $es_lines = (object)$es_lines;
        $es_lines->estimated_bill_id = $lastId;
        $pref_lines[] = $es_lines;
    }
    //file_put_contents('/tmp/data.txt', json_encode($pref_lines));
    $bl= (new EstimatedBillLineDAO())->addEsBillLines($pref_lines,$pdo);

    if ($bl === null){
        $pdo->rollBack();
        exit("error: Failed to add bill lines");
    }
    $pdo->commit();
    $bill_est = new EstimatedBills();
    $bill_est->setId($lastId);
    $bill_est->setStatus('approved');
    $upd = (new EstimatedBillsDAO())->approveEstimatedBill($bill_est,$pdo);

    if ($upd === null){
        exit('Failed');
    }
    $jsonarr = array("esid"=>$lastId,"pid"=>$_REQUEST['pid']);

    echo json_encode($jsonarr);

}


?>