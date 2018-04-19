<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/22/16
 * Time: 3:10 PM
 */
$return = (object)null;

//$_POST = {"name":"lmp_editable","value":"2016-02-11","pk":"1"}
//
$date1 = new DateTime(date("Y-m-d", strtotime($_POST['value'])));
$date2 = new DateTime(date("Y-m-d"));

$interval = $date2->diff($date1);
if($interval->invert == 0){
    $return->status = "error";
    $return->message = "Date should be in the past";
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
    $pdo = (new MyDBConnector())->getPDO();
    $sql = "UPDATE enrollments_antenatal SET lmp_date = '{$_POST['value']}', ed_date=DATE_ADD('{$_POST['value']}', INTERVAL 40 WEEK) WHERE id={$_POST['pk']}";
    $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
    $stmt->execute();
    if($stmt->rowCount() >= 0){
        $return->status = "success";
        $return->message = "Done";
    }else {
        $return->status = "error";
        $return->message = "Failed to update LMP";
    }
}
exit(json_encode($return));
