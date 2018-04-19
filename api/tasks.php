<?php
/**
 * Created by PhpStorm.
 * User: nnamdi
 * Date: 3/9/17
 * Time: 1:31 PM
 *
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
header('Access-Control-Allow-Methods: GET, POST, PUT');

$req = $_SERVER['REQUEST_METHOD'];
if ($req === 'GET'){
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/ClinicalTaskDataDAO.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/functions/func.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/functions/utils.php';
$types = getTypeOptions('type', 'clinical_task_data');
$taskType = (isset($_GET['task_type']) && !is_blank($_GET['task_type'])) ? $_GET['task_type'] : null;
$patient_type = (isset($_GET['outpatient']) && $_GET['outpatient'] === "true") ? 'op' : 'ip';
$pageSize = 500000;
$patient = (!is_blank(@$_REQUEST['patient'])) ? @$_REQUEST['patient'] : null;
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$ward = (isset($_GET['ward']) && !is_blank($_GET['ward'])) ? $_GET['ward'] : NULL;
$tasks= (new ClinicalTaskDataDAO())->getAllClinicalTaskDatumSlim($page, $pageSize, $patient_type, ['Active'], TRUE, $ward, $patient, $taskType);
$inbound_patient = (new InPatientDAO())->getInboundInPatients(TRUE,$ward,$page,$pageSize);
$total_inbound = $inbound_patient->total;
$admitted_patients = (new InPatientDAO())->getActiveInPatients(TRUE, $ward, $page, $pageSize);
$total_admitted = $admitted_patients->total;
$data = [];
$data[0] = $tasks;
$data[1] = $total_inbound;
$data[2] = $total_admitted;
header('Content-Type:application/json');
echo json_encode($data);
}