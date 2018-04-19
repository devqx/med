<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 6/26/17
 * Time: 2:40 PM
 */

if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array)json_decode(trim(file_get_contents('php://input')), true));
    $data = null;
    header("Access-Control-Allow-Origin:*");
//    require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabSpecimenDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabGroup.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
    if (isset($_POST['service_center_id']) && isset($_POST['staffId']) && isset($_POST['labs'])) {
//        $protect = new Protect();
        $staff = (new StaffDirectoryDAO())->getStaff($_POST['staffId'], FALSE);
//        if (!$staff->hasRole($protect->doctor_role) && !$staff->hasRole($protect->lab) && !$staff->hasRole($protect->lab_super))
//            exit ($protect->ACCESS_DENIED);

        $request = new LabGroup();
        $request->setPatient((new PatientDemographDAO())->getPatient($_POST['patient_id'], FALSE));
        $request->setInPatient(new InPatient($_POST['inpatient_id']));
        $request->setRequestedBy($staff);
        $request->setUrgent(isset($_POST['urgent']) ? TRUE : FALSE);
        $request->setRequestNote($_POST['request_note']);
        $request->setServiceCentre((new ServiceCenterDAO())->get($_POST['service_center_id']));
        $request->setRequestTime(date('Y-m-d H:i:s'));
        $request->setReferral((new ReferralDAO())->get($_POST['referral']));

        $preferred_specimens = array();
        $selected_speci = isset($_POST['specimens']) ? $_POST['specimens'] : [];
        foreach ($selected_speci as $spe) {
            $preferred_specimens[] = (new LabSpecimenDAO())->getSpecimen($spe);
        }
        $request->setPreferredSpecimens($preferred_specimens);

        $lab_data = array();
        $tests = $_POST['labs'];
        foreach ($tests as $t) {
            $lab_data[] = (new LabDAO())->getLab($t);
        }
        $request->setRequestData($lab_data);
        $data = (new PatientLabDAO())->newPatientLabRequest($request, false);
        echo json_encode($data);
    }

}