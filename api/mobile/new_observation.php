<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 5/18/17
 * Time: 1:20 PM
 */
if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array)json_decode(trim(file_get_contents('php://input')), true));
    $n_observe = null;
    header("Access-Control-Allow-Origin:*");
        require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InpatientObservation.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InpatientObservationDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/NursingServiceDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
    if (!is_blank($_POST['patient_id']) && !is_blank($_POST['inpatient_id']) && !is_blank($_POST['comment'])) {
        $pdo = (new MyDBConnector())->getPDO();
        $pdo->beginTransaction();
        $staff = (new StaffDirectoryDAO())->getStaff($_POST['staffId'], FALSE, $pdo);
        if(!is_blank($_POST['n_service'])){
            $instance = (new InPatientDAO())->getInPatient($_POST['inpatient_id'], TRUE, $pdo);
            $patient = (new PatientDemographDAO())->getPatient($_POST['patient_id'], FALSE, $pdo, null);
            $service = (new NursingServiceDAO())->get($_POST['n_service'], $pdo);
            $price = (new InsuranceItemsCostDAO())->getItemPriceByCode($service->getCode(), $instance->getPatient()->getId(), TRUE, $pdo);
            $bil = new Bill();

            $bil->setPatient($patient);
            $bil->setCostCentre($instance && $instance->getWard() ? $instance->getWard()->getCostCentre() : null);
            $bil->setItem($service);
            $bil->setDescription($service->getName() . "[Used in Task-]");
            $bil->setBilledTo($patient->getScheme());
            $bil->setClinic($staff->getClinic());
            $bil->setDiscounted(null);
            $bil->setDiscountedBy(null);
            $bil->setTransactionType("credit");
            $bil->setReceiver($staff);
            $bil->setSource((new BillSourceDAO())->findSourceById(16, $pdo));
            $bil->setAmount($_POST['quantity']*$price);
            $bill = (new BillDAO())->addBill($bil, $_POST['quantity'], $pdo, $_POST['inpatient_id']);
            if($bill === null){
                $pdo->rollBack();
                $n_observe = 'error:Failed to add nursing service charge';
            }
        }

        $observation = (new InpatientObservation())->setNote($_POST['comment'])->setInPatient(new InPatient($_POST['inpatient_id']))->setUser($staff);
        if(is_null((new InpatientObservationDAO())->add($observation, $pdo))){
            $pdo->rollBack();
            $n_observe = 'error:Could not save nursing observation';
        }else{
            $pdo->commit();
            $n_observe = 'success:Observation noted';

        }

        echo json_encode($n_observe, JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    }