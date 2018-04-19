<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 5/12/17
 * Time: 10:56 AM
 */
if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array)json_decode(trim(file_get_contents('php://input')), true));
    $progressNote = null;
    header("Access-Control-Allow-Origin:*");
    if (isset($_POST['patient_id']) && isset($_POST['inpatient_id']) && isset($_POST['noteType'])) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ProgressNote.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProgressNoteDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
        $pdo = (new MyDBConnector())->getPDO();
        $pdo->beginTransaction();
        if ($_POST['noteType'] === 's') {
            $pNote = new ProgressNote();
            $pNote->setInPatient(new InPatient($_POST['inpatient_id']));
            $pNote->setValue(null);
            $pNote->setNote($_POST['note']);
            $pNote->setNoteType('subj');
            $pNote->setNotedBy(new StaffDirectory($_POST['staffId']));
            if ((new ProgressNoteDAO())->add($pNote, $pdo) == null) {
                $pdo->rollBack();
                ob_end_clean();
                $progressNote = "error";
            }
//            $progressNote = "success";
        } else if ($_POST['noteType'] === 'e') {
            $pNote = new ProgressNote();
            $pNote->setInPatient(new InPatient($_POST['inpatient_id']));
            $pNote->setValue(null);
            $pNote->setNote($_POST['note']);
            $pNote->setNoteType('exam');
            $pNote->setNotedBy(new StaffDirectory($_POST['staffId']));
            if ((new ProgressNoteDAO())->add($pNote, $pdo) == null) {
                $pdo->rollBack();
                ob_end_clean();
                $progressNote = "error";
            }
//            $progressNote = "success";

        } else if ($_POST['noteType'] === 'p') {

            $pNote = new ProgressNote();
            $pNote->setInPatient(new InPatient($_POST['inpatient_id']));
            $pNote->setValue(null);
            $pNote->setNote($_POST['note']);
            $pNote->setNoteType('plan');
            $pNote->setNotedBy(new StaffDirectory($_POST['staffId']));
            if ((new ProgressNoteDAO())->add($pNote, $pdo) == null) {
                $pdo->rollBack();
                ob_end_clean();
                $progressNote = "error";
            }

            if (isset($_POST['specialization_id']) && !empty($_POST['specialization_id'])) {
                @session_start();
                require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
                require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
                require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
                require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
                require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
                require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
                require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
                $specialty = (new StaffSpecializationDAO())->get($_POST['specialization_id'], $pdo);

                if ($_POST['follow_up'] === 'yes') {
                    $price = (new InsuranceItemsCostDAO())->getItemFollowUpPriceByCode($specialty->getCode(), $_POST['patient_id'], TRUE, $pdo);

                } else {
                    $price = (new InsuranceItemsCostDAO())->getItemPriceByCode($specialty->getCode(), $_POST['patient_id'], TRUE, $pdo);
                }



                $pat = (new PatientDemographDAO())->getPatient($_POST['patient_id'], FALSE, $pdo, null);

                $staff = (new StaffDirectoryDAO())->getStaff($_POST['staffId'], FALSE, $pdo);

                $bil = new Bill();
                $bil->setPatient($pat);
                $bil->setDescription("Consultancy: " . $specialty->getName());
                $bil->setItem($specialty);
                $bil->setSource((new BillSourceDAO())->findSourceById(5, $pdo));

                $bil->setSubSource((new BillSourceDAO())->findSourceById(3, $pdo));
                $bil->setReceiver($staff);

                $bil->setTransactionType("credit");
                $bil->setAmount($price);
                $bil->setDiscounted(null);
                $bil->setDiscountedBy(null);
                $bil->setClinic($staff->getClinic());
                $bil->setBilledTo($pat->getScheme());

                if ((new BillDAO())->addBill($bil, 1, $pdo, $_POST['inpatient_id']) == null) {
                    $pdo->rollBack();
                    ob_end_clean();
                    $progressNote = "error";
                }

            }

        }
        ob_end_clean();
        $pdo->commit();
        $progressNote = "success";

    }
    echo json_encode($progressNote);

}