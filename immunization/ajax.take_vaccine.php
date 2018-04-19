<?php
@session_start();
$return = (object)null;

require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/PatientVaccineDAO.php' ;
require_once $_SERVER['DOCUMENT_ROOT']. '/functions/utils.php' ;
$taken_by = $_SESSION['staffID'];
include_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
$pdo = (new MyDBConnector())->getPDO();
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, 1);
$query = array();
if (count($_POST['pv_id']) <= 0) {
    $return->status = "error";
    $return->message = "No vaccine selected";
    exit(json_encode($return));//although this should never happen
}

foreach ($_POST['pv_id'] as $i => $pvId) {
    //you have to validate each item :O

    if(!isset($_POST['dosage'][$i]) || empty($_POST['dosage'][$i])){
        $return->status = "error";
        $return->message = "Dosage required for ". ($i+1)."th item";
//       todo: should the dosage be required?
//        exit(json_encode($return));
    }
    if(!isset($_POST['route'][$i]) || empty($_POST['route'][$i])){
        $return->status = "error";
        $return->message = "Route required for ". ($i+1)." item";
//        todo: should the route be required?
//        exit(json_encode($return));
    }
    if(!isset($_POST['site'][$i]) || empty($_POST['site'][$i])){
        $return->status = "error";
        $return->message = "Site required for ". ($i+1)." item";
//        todo: should the site be required?
//        exit(json_encode($return));
    }
    $take_type = $_POST['take_type'];
    $pv = (new PatientVaccineDAO())->getPatientVaccine($pvId, FALSE, $pdo);
    $real_shot_date = $_POST['date'][$i];
    $internal_administration = empty($_POST['place'][$i])? "FALSE" : "TRUE";
    $dosage = !is_blank($_POST['dosage'][$i])?"'".$_POST['dosage'][$i]."'":"NULL";
    $site = !is_blank($_POST['site'][$i])?"'".$_POST['site'][$i]."'":"NULL";
    $route = !is_blank($_POST['route'][$i])?"'".$_POST['route'][$i]."'":"NULL";
    $query[] = "UPDATE patient_vaccine SET entry_date = NOW(), taken_by = '$taken_by', take_type='$take_type', real_administer_date = '$real_shot_date', internal = $internal_administration, site=$site, route=$route, dosage=$dosage WHERE patient_id = " . $pv->getPatient()->getId() . " AND id=".$pv->getId();
}

$sql = implode("; ",$query);
//error_log($sql);

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    if($stmt->rowCount() > 0){
        $return->status = "success";
        $return->message = "Vaccine(s) successfully administered";
    } else {
        $return->status = "error";
        $return->message = "We encountered an error, Please try again or notify app manager";
    }
    exit(json_encode($return));
}catch (PDOException $e){
    $return->status = "error";
    $return->message = $e->getMessage();
    exit(json_encode($return));
}
//    $real_shot_date = $_POST['date'][$i];
//    list($y, $m, $d) = explode("-", $real_shot_date);
//    if(!checkdate($m, $d, $y)){
//        exit("error:Invalid date data");
//    }