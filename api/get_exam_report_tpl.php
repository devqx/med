<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/5/16
 * Time: 5:58 PM
 */
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/ExamReportingTemplateDAO.php';

$tpl = (new ExamReportingTemplateDAO())->get($_GET['id']);
if($tpl==null){
	exit(json_encode(array('bodyPart'=>' ')));
}
exit(json_encode($tpl));