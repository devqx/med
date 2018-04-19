<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 4/19/17
 * Time: 4:19 PM
 */
/** This API controls controls staff login from the inpatient mobile app.
 * It posts login credentials from the mobile to medicplus login server for authentication
 * If post method is met, Pass to server if not return error/empty value.
 * */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));

$status = "";
$patients = [];
$data = null;
header("Access-Control-Allow-Origin:*");
if (isset($_POST['username']) && isset($_POST['password'])) {
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.staff.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';

    $staff = new StaffManager;
    $status = $staff->doLogin($_POST['username'], $_POST['password'], null, null);
    if ($status !== "error:Account not unique"){
        $patients = (new InPatientDAO())->getActiveInPatients();
        $data = json_encode($patients);
    }
    else{
        $data = $status = 'error';
    }
}
//    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
//        header('Content-Type: application/json;charset=UTF-8');

        echo $data;
//}
}
