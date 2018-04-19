<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/17/14
 * Time: 10:32 AM
 */
$id = $_POST['id'];
$patient_id = $_POST['pid'];

require_once $_SERVER['DOCUMENT_ROOT'] . "/Connections/MyDBConnector.php";
$pdo = (new MyDBConnector())->getPDO();
$sql = "UPDATE patient_diagnoses SET active = FALSE WHERE id = $id AND patient_ID = $patient_id";
$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
$chk = $stmt->execute();
//$sql = "UPDATE patient_pre_conditions SET active = FALSE WHERE id = $id AND patient_id = '$patient_id'";
if($chk){
    exit(json_encode(TRUE));
}
exit(json_encode(FALSE));