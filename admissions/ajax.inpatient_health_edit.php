<?php
/**
 * Created by PhpStorm.
 * User: oluwaseunpaul
 * Date: 4/6/18
 * Time: 5:56 PM
 */


if($_POST ){

    try{

        require_once $_SERVER['DOCUMENT_ROOT'] . '/admissions/InPatientHealthState.php';

        $inp = new InPatientHealthState();

        $request = [];
        foreach ($_POST as $key=>$value){
            $request[$key] = $value;
        }

        $request = (object)$request;

        $inp
            ->setHealthStatusId($request->health_state)
            ->setPatientId($request->in_pid)
            ->setRiskToFall($request->risk_to_fall)
            ->updatePatientHealthStatus();

        echo "Patient Health Updated successfully";

    }
    catch (Exception $e){
        error_log( json_encode( $e->getMessage() ) );
    }
}

?>