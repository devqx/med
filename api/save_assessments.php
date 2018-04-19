<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/18/14
 * Time: 1:55 PM
 */
if(!isset($_SESSION)){@session_start();}

$data = json_decode($_POST['data']);


require_once $_SERVER['DOCUMENT_ROOT']. '/classes/class.assessments.php';
$ASSESSMENT = new Assessments();
sleep(1);
echo $ASSESSMENT->newAssessmentData($data);
//echo "ok";
exit;