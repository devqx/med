<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 5/11/17
 * Time: 4:53 PM
 */
if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array)json_decode(trim(file_get_contents('php://input')), true));
    $c_task = null;
    header("Access-Control-Allow-Origin:*");
    if (isset($_POST['patient_id']) && isset($_POST['inpatient_id']) && isset($_POST['taskId'])) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/NursingServiceDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/NursingService.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VitalSign.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
        $patient = (new PatientDemographDAO())->getPatient($_POST['patient_id'], false);
        $pat = (new PatientDemographDAO())->getPatientMin($_POST['patient_id']);


        require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
        $pdo = (new MyDBConnector())->getPDO();
        $pdo->beginTransaction();

        $task = (new ClinicalTaskDataDAO())->getClinicalTaskDatum($_POST['taskId'], ['Active'], true, $pdo);
        $staff = (new StaffDirectoryDAO())->getStaff($_POST['staffId'], true, $pdo);

        if (isset($_POST['n_service'])) {
            $instance = isset($_POST['n_service']) ? (new InPatientDAO())->getInPatient($_POST['inpatient_id'], true, $pdo) : null;
            $service = (new NursingServiceDAO())->get($_POST['n_service'], $pdo);
            $price = (new InsuranceItemsCostDAO())->getItemPriceByCode($service->getCode(), $_POST['patient_id'], true, $pdo);
            $bill = new Bill();
            $bill->setPatient($patient);
            $bill->setDescription($service->getName() . " [Used in Tasks]");
            $bill->setClinic($staff->getClinic());
            $bill->setItem($service);
            $bill->setReceiver($staff);
            $bill->setSource((new BillSourceDAO())->findSourceById(16, $pdo));
            $bill->setTransactionType('credit');
            $bill->setAmount($_POST['quantity'] * $price);
            $bill->setDiscounted(null);
            $bill->setDiscountedBy(null);
            $bill->setCostCentre($instance && $instance->getWard() ? $instance->getWard()->getCostCentre() : null);
            $bill->setBilledTo($patient->getScheme());
            $bil = $bill->add($_POST['quantity'], $_POST['inpatient_id'], $pdo);
            if ($bil == null) {
                $pdo->rollBack();
                $c_task = 'error:Failed to bill for nursing service';

            }
        }
        if (isset($_POST['p_value']) && $_POST['p_value'] !== null) {
            $value = $_POST['p_value'];
            $comment = $_POST['comment'];
            if (is_blank($comment)) {
                $pdo->rollBack();
                $c_task = 'error:no comment';
            }
//            $type = $task->getType();
            $type = (new VitalDAO())->getByName($_POST['type'], $pdo);
            if (trim($value) == '') {
                $pdo->rollBack();
                $c_task = 'error:no value';
            }

            $staff = (new StaffDirectoryDAO())->getStaff($_POST['staffId'], true, $pdo);
            $ip = (isset($_POST['inpatient_id']) && trim($_POST['inpatient_id']) !== "") ? new InPatient($_POST['inpatient_id']) : null;
            $new = (new VitalSign())->setType($type)->setPatient($pat)->setInPatient($ip)->setEncounter(null)->setHospital(new Clinic(1))->setReadBy($staff)->setReadDate(date(MainConfig::$mysqlDateTimeFormat))->setValue($value)->add($pdo);
            if ($new === null) {
                $pdo->rollBack();
                $c_task = 'error:Sorry unable to save new reading';
            }

            $chart_data = (object)null;
            $chart_data->Staff = $staff;
            $chart_data->Comment = !is_blank($_POST['comment']) ? $_POST['comment'] : 'N/A';
            $chart_data->Value = $value;
            $chart_data->NursingService = (isset($_POST['n_service']) && trim($_POST['n_service']) !== "") ? new NursingService($_POST['n_service']) : null;
            if ((new ClinicalTaskDataDAO())->updateTask($type, $ip, null, $pdo, $_POST['taskId'], $chart_data)) {
                $pdo->commit();
                $c_task = 'success:Vital reading saved successfully';
            } else {
                $pdo->rollBack();
                $c_task = 'error:Sorry something went wrong and we are unable to complete your request';
            }

        } else if (isset($_POST['w_value'], $_POST['h_value'])) {

            if (isset($_POST['inpatient_id']) && trim($_POST['inpatient_id']) !== "") {
                $ip = new InPatient($_POST['inpatient_id']);
            } else {
                $ip = null;
            }

            $weight = $_POST['w_value'];
            $height = $_POST['h_value'];

            $value = $_POST['type'] == 'BMI' ? number_format(($_POST['w_value'] / ($_POST['h_value'] * $_POST['h_value'])), 1) :
                //else it has to be BSA
                number_format(parseNumber(($_POST['w_value'] ^ 0.425 * ($_POST['h_value'] / 100) ^ 0.725) * 0.007184), 2);
            $type = (new VitalDAO())->getByName($_POST['type'], $pdo);
            $new1 = (new VitalSign())->setType((new VitalDAO())->getByName('Weight', $pdo))->setPatient(new PatientDemograph($_POST['patient_id']))->setInPatient(isset($_POST['inpatient_id']) ? new InPatient($_POST['inpatient_id']) : NULL)
                ->setEncounter(NULL)->setHospital(new Clinic(1))->setReadBy(new StaffDirectory($_POST['staffId']))
                ->setReadDate(date(MainConfig::$mysqlDateTimeFormat))->setValue($_POST['w_value'])->add($pdo);

            $new2 = (new VitalSign())->setType((new VitalDAO())->getByName('Height', $pdo))->setPatient(new PatientDemograph($_POST['patient_id']))->setInPatient(isset($_POST['inpatient_id']) ? new InPatient($_POST['inpatient_id']) : NULL)
                ->setEncounter(NULL)->setHospital(new Clinic(1))->setReadBy(new StaffDirectory($_POST['staffId']))
                ->setReadDate(date(MainConfig::$mysqlDateTimeFormat))->setValue($_POST['h_value'])->add($pdo);

            $new = (new VitalSign())->setType($type)->setPatient(new PatientDemograph($_POST['patient_id']))->setInPatient(isset($_POST['inpatient_id']) ? new InPatient($_POST['inpatient_id']) : NULL)
                ->setEncounter(NULL)->setHospital(new Clinic(1))->setReadBy(new StaffDirectory($_POST['staffId']))
                ->setReadDate(date(MainConfig::$mysqlDateTimeFormat))->setValue($value)->add($pdo);
            if ($new == null || $new1 == null || $new2 == null) {
                $pdo->rollBack();
                $c_task = 'error:Failed to save one or more vital sign component';
            }

            $chart_data = (object)null;
            $chart_data->Staff = (new StaffDirectoryDAO())->getStaff($_POST['staffId'], true, $pdo);
            $chart_data->Comment = !is_blank($_POST['comment']) ? $_POST['comment'] : 'N/A';
            $chart_data->Value = $value;
            $chart_data->NursingService = (isset($_POST['n_service']) && trim($_POST['n_service']) !== "") ? new NursingService($_POST['n_service']) : null;

            if ((new ClinicalTaskDataDAO())->updateTask($type, $ip, null, $pdo, $_POST['taskId'], $chart_data)) {
                $pdo->commit();
                $c_task = 'success:Vital reading saved successfully';
            } else {
                $pdo->rollBack();
                $c_task = 'error:Sorry something went wrong and we are unable to complete your request';
            }

        }

    }

    echo json_encode($c_task, JSON_PARTIAL_OUTPUT_ON_ERROR);
}