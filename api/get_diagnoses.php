<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/3/14
 * Time: 11:30 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DiagnosisDAO.php';


//error_log(json_encode($_GET));

if (isset($_REQUEST['q']) && !isset($_REQUEST['type'])) {
	echo json_encode((new DiagnosisDAO())->findDiagnoses($_REQUEST['q']), JSON_PARTIAL_OUTPUT_ON_ERROR);
} else if (isset($_REQUEST['q']) && isset($_REQUEST['type'])) {
	echo json_encode((new DiagnosisDAO())->findDiagnoses($_REQUEST['q'], $_REQUEST['type']), JSON_PARTIAL_OUTPUT_ON_ERROR);
} else if (isset($_REQUEST['id']) && isset($_REQUEST['single'])) {
	echo json_encode((new DiagnosisDAO())->getDiagnosis($_REQUEST['id']), JSON_PARTIAL_OUTPUT_ON_ERROR);
} else {
	echo json_encode((new DiagnosisDAO())->getDiagnoses(), JSON_PARTIAL_OUTPUT_ON_ERROR);
}
exit;
