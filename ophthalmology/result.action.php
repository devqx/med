<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 7/18/14
 * Time: 10:14 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/OphthalmologyResultDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/OphthalmologyResult.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/MessageDispatch.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/MessageDispatchDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/class.config.sms.php';

if(!isset($_SESSION)){session_start();}
$result=(new OphthalmologyResultDAO())->get($_POST['id'], TRUE);

if(isset($_POST['action']) && $_POST['action']==="approve"){
    $result->setApprovedBy(new StaffDirectory($_SESSION['staffID']));
    $status = (new OphthalmologyResultDAO())->approveResult($result);
    $data = new stdClass();
    if($status===NULL){
        $data->status = "error";
        $data->message = "No access";
    }else if($status){

        $lab = (new OphthalmologyResultDAO())->get($result->getId(), TRUE);
        $phone = ($lab->getPatientOphthalmology()->getOphthalmologyGroup()->getPatient() !=null)? $lab->getPatientOphthalmology()->getOphthalmologyGroup()->getPatient()->getPhoneNumber() : '';
        $msg = SmsConfig::$approvedLabSms;
        $dispatch = new MessageDispatch();
        $dispatch->setMessage($msg);
        $dispatch->setSmsChannelAddress($phone);
        (new MessageDispatchDAO())->sendSMS($dispatch);

        $data->status = "ok";
        $data->message = "Result approved successfully";
    } else {
        $data->status = "error";
        $data->message = "Failed to approve result. It might have been already approved";
    }
} else if(isset($_POST['action']) && $_POST['action']==="reject"){
    $status = (new OphthalmologyResultDAO())->rejectResult($result);
    $data = new stdClass();
    if($status===NULL){
        $data->status = "error";
        $data->message = "No access";
    }else if($status){
        $data->status = "ok";
        $data->message = "Result rejected ";
    } else {
        $data->status = "error";
        $data->message = "Action failed";
    }
} else if(isset($_POST['action']) && $_POST['action']==="abnormal"){
    $result->setAbnormalValue($_POST['a']);
    $status = (new OphthalmologyResultDAO())->setAbnormalValue($result);
    $data = new stdClass();
    if($status){
        $data->status = "ok";
        $data->message = "Abnormal value set";
    } else {
        $data->status = "error";
        $data->message = "Abnormal value not set";
    }
}
//error_log(json_encode($data));
exit(json_encode($data));