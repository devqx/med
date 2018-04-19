<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/20/17
 * Time: 4:05 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAttachmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientAttachment.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';

if(isset($_POST['id']) && isset($_POST['user_id'])){
	error_log("doc id".$_POST['id'] . "staff_id".$_POST['user_id']);
	$de_ = new PatientAttachment();
	$de_->setId($_POST['id']);
	$de_->setDeletedBy($_POST['user_id']);
	if(!(new PatientAttachmentDAO())->deleteDoc($de_) == null){
		exit("success");
	}
}
exit('error');