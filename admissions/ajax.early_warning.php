<?php
/**
 * Created by PhpStorm.
 * User: oluwaseunpaul
 * Date: 4/4/18
 * Time: 11:19 AM
 */


//require the needed files to work with

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/EarlyWarningScore.php';
session_start();
if( $_POST ){

    $ewScore = new EarlyWarningScore();

    //set a request array to hold the posted data, i just like currying
    $request = array();

    foreach( $_POST as $key => $val) {

        $request[$key] = $val;


    }


    //cast to object
    $request = (object)$request;



    //extract vitals values
    $vitals = (object)[];

    //other additional details
    $misc = (object)[];

    $vitals->temperature= $request->temperature;
    $vitals->systolic_bp = $request->systolic_bp;
    $vitals->oxygen_saturations = $request->oxygen_saturations;
    $vitals->respiration_rate = $request->respiration_rate;


    $misc->patient_id= $request->patient_id;
    $misc->read_by = $_SESSION['staffID'];
    $misc->encounter_id= 1;
    $misc->hospital_id= 1;

    $count = 0;

    foreach ($vitals as $key => $value ){

        $count++;

        $type_id = $ewScore->setTypeId( $key );

        $vitals->type_id = $type_id;

        $ewScore->saveToVitalSign($vitals, $misc, $value);


    }


    //set and save the scores to the database
    $ewScore
        ->setBloodPressure($request->systolic_bp)
        ->setHeartRate($request->heart_rate)
        ->setLoc($request->loc)
        ->setOxygenSaturations($request->oxygen_saturations)
        ->setRespirationRate($request->respiration_rate)
        ->setSupplementalOxygen($request->supplemental_oxygen)
        ->setTemperature($request->temperature)
        ->setPatientId($request->patient_id)
        ->setAdmissionId($request->admission_id)
        ->setTakenById($_SESSION['staffID'])
        ->setScore(0)
        ->setScoreAverage(0)
        ->setColorCode(0)
        ->save();


    //write the values to the vital_sign table




}