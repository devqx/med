<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 6/23/17
 * Time: 8:58 AM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $chartData = null;
    header("Access-Control-Allow-Origin:*");
    if(isset($_POST['patient_id']) && isset($_POST['inpatient_id'])){
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FluidChartDAO.php';
        $chartData = (new FluidChartDAO())->forInstance($_POST['inpatient_id'], null, null);
    }
    echo json_encode($chartData, JSON_PARTIAL_OUTPUT_ON_ERROR);
}