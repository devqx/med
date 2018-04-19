<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImmunizationEnrollmentDAO
 *
 * @author pauldic
 */
class ImmunizationEnrollmentDAO {

    private $conn = null;

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ImmunizationEnrollment.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function addImmunizationEnrollment($ae, $pdo = NULL) {
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "INSERT INTO enrollments_immunization SET patient_id = '" . $ae->getPatient()->getId() . "', enrolled_at = '" . $ae->getEnrolledAt()->getId() . "', enrolled_by='" . $ae->getEnrolledBy()->getId() . "'";
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $ae->setId($pdo->lastInsertId());
            } else {
                $ae = NULL;
            }

            $stmt = NULL;
        } catch (PDOException $e) {
            $ae = $stmt = NULL;
        } catch (Exception $e) {
            $ae = $stmt = NULL;
        }

        return $ae;
    }

    function isEnrollmented($pid, $pdo = NULL) {
        $status = FALSE;
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM enrollments_immunization patient_id =" . $pid . "";
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $status = TRUE;
            }
            $stmt = null;
        } catch (PDOException $e) {
            $stmt = NULL;
            $status = FALSE;
        }
        return $status;
    }

    function getImmunizationEnrollment($pid, $getFull = FALSE, $pdo = NULL) {
        $iEnroll = new ImmunizationEnrollment();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM enrollments_immunization patient_id =" . $pid . "";
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                if ($getFull) {
                    $patient = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
                    $at = (new ClinicDAO())->getClinic($row['enrolled_at'], FALSE, $pdo);
                    $by = (new StaffDirectoryDAO())->getStaff($row['enrolled_by'], FALSE, $pdo);
                } else {
                    $patient = new PatientDemograph($row["patient_id"]);
                    $at = new Clinic($row['enrolled_at']);
                    $by = new StaffDirectory($row['enrolled_by']);
                }
                $iEnroll->setPatient($patient);
                $iEnroll->setEnrolledAt($at);
                $iEnroll->setEnrolledOn($row['enrolled_on']);
                $iEnroll->setEnrolledBy($by);
            } else {
                $iEnroll = NULL;
            }
            $stmt = null;
        } catch (PDOException $e) {
            $iEnroll = $stmt = NULL;
        }
        return $iEnroll;
    }

    function getImmunizationEnrollments($getFull = FALSE, $order = NULL, $pdo = NULL) {
        $iEnrolls = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM enrollments_immunization ORDER BY enrolled_at";
//            error_log("......" . $sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $iEnroll = new ImmunizationEnrollment();
                if ($getFull) {
                    $patient = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
                    $at = (new ClinicDAO())->getClinic($row['enrolled_at'], FALSE, $pdo);
                    $by = (new StaffDirectoryDAO())->getStaff($row['enrolled_by'], FALSE, $pdo);
                } else {
                    $patient = new PatientDemograph($row["patient_id"]);
                    $at = new Clinic($row['enrolled_at']);
                    $by = new StaffDirectory($row['enrolled_by']);
                }
                $iEnroll->setPatient($patient);
                $iEnroll->setEnrolledAt($at);
                $iEnroll->setEnrolledOn($row['enrolled_on']);
                $iEnroll->setEnrolledBy($by);
                $iEnrolls[] = $iEnroll;
            }
            $stmt = null;
        } catch (PDOException $e) {
            $iEnrolls = [];
        }
        return $iEnrolls;
    }

}
