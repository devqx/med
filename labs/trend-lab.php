<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/10/17
 * Time: 12:04 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabResultDAO.php';
$return = new stdClass();

if (is_blank($_POST['patient'])) {
	$return->status = 'error';
	$return->message = 'Patient info not provided';
} else if (is_blank($_POST['lab'])) {
	$return->status = 'error';
	$return->message = 'Lab info not provided';
} else {
	$patientId = (int)$_POST['patient'];
	$labTemplateData = (new LabDAO())->getLab($_POST['lab'], TRUE)->getLabTemplate()->getData();
	
	$trend = [];
	$methods = [];
	//get the template data methods in the lab clicked
	foreach ($labTemplateData as $datum){
		$methods[] = $datum->getMethod()->getId();
	}
	try {
		$pdo = (new MyDBConnector())->getPDO();
		//$sql = "SELECT l.*, lr.id AS rid, rq.service_centre_id FROM patient_labs l LEFT JOIN lab_result lr ON lr.patient_lab_id=l.id LEFT JOIN patient_demograph p ON p.patient_ID=l.patient_id LEFT JOIN lab_requests rq ON rq.lab_group_id=l.lab_group_id WHERE l.patient_id={$_POST['patient']} AND l.test_id={$_POST['lab']}";
		$sql = "SELECT lr.id AS rid FROM lab_method lm LEFT JOIN lab_template_data ltd ON lm.id=ltd.lab_method_id LEFT JOIN lab_template lt ON ltd.lab_template_id = lt.id LEFT JOIN lab_result_data lrd ON lrd.lab_template_data_id=ltd.id LEFT JOIN lab_result lr ON lr.lab_template_id=lt.id LEFT JOIN patient_labs pl ON lr.patient_lab_id = pl.id WHERE pl.patient_id=".$patientId." AND lm.id IN (".implode(",", $methods).") GROUP BY lr.id";
		//error_log($sql);
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$resultData = [];
		$lab = (new LabDAO())->getLab($_POST['lab'], TRUE);//$result->getPatientLab()->getTest();
		
		while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			$obj = new stdClass();
			
			$result = (new LabResultDAO())->getLabResult($row['rid'], TRUE, $pdo);
			$labTemplateDataMethods = [];
			foreach ($lab->getLabTemplate()->getData() as $datum_){
				$labTemplateDataMethods[] = $datum_->getMethod()->getId();
			}
			//error_log(json_encode($labTemplateDataMethods));
			
			if($result != null/* && $_POST['lab']== $lab->getId()*/){ //$result = new LabResult();
				$obj->{"Test Date"} = date(MainConfig::$dateTimeFormat, strtotime($result->getPatientLab()->getSpecimenDate()));// date(MainConfig::$shortDateFormat, strtotime($row['test_date']));
				//we should be only interested in the template method for the lab selected
				foreach ($result->getData() as $i=>$datum){ //$datum = new LabResultData();
					if(in_array($datum->getLabTemplateData()->getMethod()->getId(), $labTemplateDataMethods)){
						$obj->{ $datum->getLabTemplateData()->getMethod()->getName() } = $datum->getValue();
					}
					$resultData[$result->getId()] = $obj;
				}
			}
		}
		$return->status = 'success';
		$return->message = $resultData;
	} catch (PDOException $e) {
		$return->status = 'error';
		$return->message = 'Application error';
	}
}
ob_end_clean();
exit(json_encode($return));