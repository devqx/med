<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 6/24/17
 * Time: 3:18 AM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $result = null;
    header("Access-Control-Allow-Origin:*");
    if(isset($_POST['staffId']) && isset($_POST['patient_id']) && isset($_POST['inpatient_id'])){
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FluidRouteDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/FluidChart.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
        $staff = new StaffDirectory($_POST['staffId']);
        $instance = (new InPatientDAO())->getInPatient($_POST['inpatient_id']);
        $data = (new FluidChart())->setPatient($instance->getPatient())->setInPatient($instance)->setRoute( (new FluidRouteDAO())->get($_POST['route']) )->setVolume(parseNumber($_POST['volume']))->setUser($staff)->setTimeEntered(date('Y-m-d H:i:s'))->add();
        if($data !== null){
            $result = $data;
        }else {
            $result = 'error:Failed to save';
        }
    }
 echo json_encode($result);
}