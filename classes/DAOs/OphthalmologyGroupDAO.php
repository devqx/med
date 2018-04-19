<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/17/14
 * Time: 1:37 PM
 */

class OphthalmologyGroupDAO {

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientOphthalmologyDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/OphthalmologyGroup.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientOphthalmology.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/ReferralDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/ServiceCenterDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function getPatientLabGroups($pid, $page=0, $pageSize=10, $getFull = FALSE, $pdo = NULL) {
        $sql = "SELECT * FROM ophthalmology_requests WHERE patient_id = $pid ORDER BY time_entered DESC";
        $total = 0;
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $total = $stmt->rowCount();
        } catch (PDOException $e){
            error_log("ERROR: Failed to return total number of records");
        }

        $page = ($page > 0) ? $page : 0;
        $offset = ($page > 0) ? $pageSize * $page : 0;
        $labGroups = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology_requests WHERE patient_id = $pid ORDER BY time_entered DESC LIMIT $offset, $pageSize";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $labGroup = new OphthalmologyGroup();
                if ($getFull) {
                    $pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, NULL);
                    $requestedBy = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
                    $hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
                    
                    $requestData=(new PatientOphthalmologyDAO())->getPatientOphthalmologyByGroupCode($row['group_code'], TRUE, $pdo);
                } else {
                    $pat = new PatientDemograph($row['patient_id']);
                    $requestedBy = new StaffDirectory($row['requested_by']);
                    $hosp=new Clinic($row['hospid']);
                    $requestData=[];
                }
                $labGroup->setId($row['id']);
                $labGroup->setGroupName($row['group_code']);
                $labGroup->setPatient($pat);
                $labGroup->setRequestedBy($requestedBy);
                $labGroup->setRequestTime($row['time_entered']);
                $labGroup->setReferral( (new ReferralDAO())->get($row['referral_id'], $pdo) );
                $labGroup->setServiceCentre( (new ServiceCenterDAO())->get($row['service_centre_id'], $pdo) );
                $labGroup->setRequestData($requestData);
                $labGroups[] = $labGroup;
            }
            $stmt = null;
        } catch (PDOException $e) {
            errorLog($e);
            $labGroups = NULL;
        }

        $results = (object)null;
        $results->data = $labGroups;
        $results->total = $total;
        $results->page = $page;
        return $results;
    }

    function getGroup($id, $getFull = FALSE, $pdo = NULL) {
        $labGroup = new OphthalmologyGroup();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology_requests WHERE group_code='$id'";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                if ($getFull) {
                    $pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
                    $requestedBy = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
                    $hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
                } else {
                    $pat = new PatientDemograph($row['patient_id']);
//                    $requestedBy = new StaffDirectory($row['requested_by']);
                    $requestedBy = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);

                    $hosp = new Clinic($row['hospid']);
//                    foreach ($pref_specs_ids as $ps) {
//                        $spc[] = new LabSpecimen($ps);
//                    }
                }

                $labGroup->setId($row['id']);
                $labGroup->setGroupName($row['group_code']);
                $labGroup->setPatient($pat);
                $labGroup->setRequestedBy($requestedBy);
                $labGroup->setRequestTime($row['time_entered']);
                $labGroup->setServiceCentre( (new ServiceCenterDAO())->get($row['service_centre_id'], $pdo) );
                $labGroup->setReferral( (new ReferralDAO())->get($row['referral_id'], $pdo) );
                $labGroup->setClinic($hosp);
            } else {
                $labGroup = NULL;
                $stmt = NULL;
            }
        } catch (PDOException $e) {
            errorLog($e);
            $labGroup = NULL;
        }
        return $labGroup;
    }

//    function getLab($)
}