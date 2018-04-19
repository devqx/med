<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/5/16
 * Time: 7:04 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ExamReportingTemplateDAO.php';
$data = (new ExamReportingTemplateDAO())->all();
exit(json_encode($data));