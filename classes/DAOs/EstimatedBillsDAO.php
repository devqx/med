<?php
/**
 * Created by PhpStorm.
 * User: nnamdi
 * Date: 4/16/17
 * Time: 3:53 PM
 */
class EstimatedBillsDAO{
    private $conn = null;

    function __construct()
    {
        try {
            @session_start();
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/EstimatedBillLine.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bill.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/EstimatedBills.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AntenatalEnrollment.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/BillSource.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/EstimatedBillsDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/EstimatedBillLineDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAntenatalUsagesDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackageItemsDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/protect.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function getEstimatedBillById($es_id,$pdo=null){
       $estimated_bill = new EstimatedBills();
       try{
           $pdo = $pdo == null? $this->conn->getPDO() :$pdo;
           $sql ="SELECT * FROM estimated_bills WHERE id=".$es_id;
           $stmt = $pdo->prepare($sql,array(PDO::ATTR_CURSOR=>PDO::CURSOR_SCROLL));
           $stmt->execute();
           if ($row = $stmt->fetch(PDO::FETCH_NAMED,PDO::FETCH_ORI_NEXT)){
               $estimated_bill->setDateCreated($row['date_created']);
               $estimated_bill->setEsCode($row['es_code']);
               $estimated_bill->setNarration($row['narration']);
               $estimated_bill->setStatus($row['status']);
               $estimated_bill->setValidTill($row['valid_till']);
               $estimated_bill->setTotalEstimate($row['total_estimate']);
               $estimated_bill->setCreatedBy((new StaffDirectoryDAO())->getStaff($row['created_by']));
           }
           else{
               $estimated_bill = null;
           }
           $stmt = null;
       }
       catch(PDOException $e){
          $estimated_bill = null;
       }
      return $estimated_bill;
    }

    function getOpenBillEstimates($pid =null,$page,$pageSize,$pdo = null){
        $today = date('Y-m-d');
        $total = 0;
        if ($pid === null) {
            $sql = "SELECT estimated_bills.id AS ESID, estimated_bills.es_code AS ESCODE, estimated_bills.patient_id as PID, estimated_bills.valid_till AS valid_date, estimated_bills.total_estimate AS total, estimated_bills.date_created AS created_on,estimated_bills.status AS status,estimated_bills.narration AS narration,estimated_bills.created_by AS created_by, CONCAT_WS(' ', pd.title, pd.fname, pd.mname, pd.lname) AS Fullname, pd.patient_ID, insurance_schemes.scheme_name AS scheme_name, insurance_schemes.credit_limit AS credit_limit, insurance_schemes.pay_type AS pay_type FROM estimated_bills LEFT JOIN patient_demograph pd ON estimated_bills.patient_id = pd.patient_ID LEFT JOIN insurance_schemes ON  estimated_bills.scheme_id = insurance_schemes.id WHERE estimated_bills.status = 'approved' OR estimated_bills.status <> 'cancelled' AND estimated_bills.valid_till <= ".$today." ORDER BY estimated_bills.es_code DESC ";

        }
        else{
            $sql = "SELECT estimated_bills.id AS ESID, estimated_bills.es_code AS ESCODE, estimated_bills.patient_id as PID, estimated_bills.valid_till AS valid_date, estimated_bills.total_estimate AS total, estimated_bills.date_created AS created_on,estimated_bills.status AS status,estimated_bills.narration AS narration,estimated_bills.created_by AS created_by, CONCAT_WS(' ', pd.title, pd.fname, pd.mname, pd.lname) AS Fullname, pd.patient_ID, insurance_schemes.scheme_name AS scheme_name, insurance_schemes.credit_limit AS credit_limit, insurance_schemes.pay_type AS pay_type FROM estimated_bills LEFT JOIN patient_demograph pd ON estimated_bills.patient_id = pd.patient_ID LEFT JOIN insurance_schemes ON  estimated_bills.scheme_id = insurance_schemes.id WHERE estimated_bills.patient_id=".$pid;

        }
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $total = $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("ERROR: Failed to return total number of records");
        }

        $page = ($page > 0) ? $page : 0;
        $offset = ($page > 0) ? $pageSize * $page : 0;

        try {
            $pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;

            $sql .= " LIMIT $offset, $pageSize";
            //error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            $estimated_bills = [];

            require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $estimated_bill = (object)null;

                /*$patient = (new PatientDemographDAO())->getPatient($row['patient_ID'], TRUE, $pdo);
                if($patient !== null){*/
                $estimated_bill->isAdmitted = (new Manager())->isAdmitted($row['patient_ID']); //$patient->getFullname();
                $estimated_bill->escode = $row['ESCODE'];
                $estimated_bill->esid = $row['ESID'];
                $estimated_bill->Patient = $row['Fullname'];//$patient->getFullname();
                $estimated_bill->PatientID = $row['patient_ID'];
                $estimated_bill->created_on = $row['created_on'];
                $estimated_bill->pid = $row['PID'];
                $estimated_bill->period = $row['valid_date'];
                $estimated_bill->credit_limit = $row['credit_limit'];
                $estimated_bill->pay_type = $row['pay_type'];
                $estimated_bill->Scheme = $row['scheme_name'];
                $estimated_bill->total = $row['total'];
                $estimated_bill->created_by = $row['created_by'];
                $estimated_bill->status = $row['status'];
                $estimated_bill->narration = $row['narration'];

                $estimated_bills[] = $estimated_bill;
                /*} else {
                        error_log("NULL OBJECT FOR PATIENT: (".$row['patient_ID'].")");
                }*/

            }
        } catch (PDOException $e) {
            errorLog($e);
            $estimated_bills = [];
        }

        $results = (object)null;
        $results->data = $estimated_bills;
        $results->total = $total;
        $results->page = $page;

        return $results;

    }
    function EstimatedBills($pid=null, $page, $pageSize, $pdo = null)
    {

        $total = 0;
        if ($pid === null) {
            $sql = "SELECT estimated_bills.id AS ESID, estimated_bills.es_code AS ESCODE, estimated_bills.patient_id as PID, estimated_bills.valid_till AS valid_date, estimated_bills.total_estimate AS total, estimated_bills.date_created AS created_on,estimated_bills.status AS status,estimated_bills.narration AS narration,estimated_bills.created_by AS created_by, CONCAT_WS(' ', pd.title, pd.fname, pd.mname, pd.lname) AS Fullname, pd.patient_ID, insurance_schemes.scheme_name AS scheme_name, insurance_schemes.credit_limit AS credit_limit, insurance_schemes.pay_type AS pay_type FROM estimated_bills LEFT JOIN patient_demograph pd ON estimated_bills.patient_id = pd.patient_ID LEFT JOIN insurance_schemes ON  estimated_bills.scheme_id = insurance_schemes.id WHERE estimated_bills.status <> 'cancelled' ORDER BY estimated_bills.es_code DESC ";

        }
        else{
            $sql = "SELECT estimated_bills.id AS ESID, estimated_bills.es_code AS ESCODE, estimated_bills.patient_id as PID, estimated_bills.valid_till AS valid_date, estimated_bills.total_estimate AS total, estimated_bills.date_created AS created_on,estimated_bills.status AS status,estimated_bills.narration AS narration,estimated_bills.created_by AS created_by, CONCAT_WS(' ', pd.title, pd.fname, pd.mname, pd.lname) AS Fullname, pd.patient_ID, insurance_schemes.scheme_name AS scheme_name, insurance_schemes.credit_limit AS credit_limit, insurance_schemes.pay_type AS pay_type FROM estimated_bills LEFT JOIN patient_demograph pd ON estimated_bills.patient_id = pd.patient_ID LEFT JOIN insurance_schemes ON  estimated_bills.scheme_id = insurance_schemes.id WHERE estimated_bills.status <> 'cancelled' AND estimated_bills.patient_id=".$pid." ORDER BY estimated_bills.es_code DESC ";

        }
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $total = $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("ERROR: Failed to return total number of records");
        }

        $page = ($page > 0) ? $page : 0;
        $offset = ($page > 0) ? $pageSize * $page : 0;

        try {
            $pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;

            $sql .= " LIMIT $offset, $pageSize";
            //error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            $estimated_bills = [];

            require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $estimated_bill = (object)null;

                /*$patient = (new PatientDemographDAO())->getPatient($row['patient_ID'], TRUE, $pdo);
                if($patient !== null){*/
                $estimated_bill->isAdmitted = (new Manager())->isAdmitted($row['patient_ID']); //$patient->getFullname();
                $estimated_bill->escode = $row['ESCODE'];
                $estimated_bill->esid = $row['ESID'];
                $estimated_bill->Patient = $row['Fullname'];//$patient->getFullname();
                $estimated_bill->PatientID = $row['patient_ID'];
                $estimated_bill->created_on = $row['created_on'];
                $estimated_bill->pid = $row['PID'];
                $estimated_bill->period = $row['valid_date'];
                $estimated_bill->credit_limit = $row['credit_limit'];
                $estimated_bill->pay_type = $row['pay_type'];
                $estimated_bill->Scheme = $row['scheme_name'];
                $estimated_bill->total = $row['total'];
                $estimated_bill->created_by = $row['created_by'];
                $estimated_bill->status = $row['status'];
                $estimated_bill->narration = $row['narration'];

                $estimated_bills[] = $estimated_bill;
                /*} else {
                        error_log("NULL OBJECT FOR PATIENT: (".$row['patient_ID'].")");
                }*/

            }
        } catch (PDOException $e) {
            errorLog($e);
            $estimated_bills = [];
        }

        $results = (object)null;
        $results->data = $estimated_bills;
        $results->total = $total;
        $results->page = $page;

        return $results;

    }
    function getBillEstimatesPendingApproval($pid=null, $page, $pageSize, $pdo = null)
    {

        $total = 0;
        if ($pid === null) {
            $sql = "SELECT estimated_bills.id AS ESID, estimated_bills.es_code AS ESCODE, estimated_bills.patient_id as PID, estimated_bills.valid_till AS valid_date, estimated_bills.total_estimate AS total, estimated_bills.date_created AS created_on,estimated_bills.status AS status,estimated_bills.narration AS narration,estimated_bills.created_by AS created_by, CONCAT_WS(' ', pd.title, pd.fname, pd.mname, pd.lname) AS Fullname, pd.patient_ID, insurance_schemes.scheme_name AS scheme_name, insurance_schemes.credit_limit AS credit_limit, insurance_schemes.pay_type AS pay_type FROM estimated_bills LEFT JOIN patient_demograph pd ON estimated_bills.patient_id = pd.patient_ID LEFT JOIN insurance_schemes ON  estimated_bills.scheme_id = insurance_schemes.id WHERE estimated_bills.status = 'draft' AND estimated_bills.status <> 'cancelled' ORDER BY estimated_bills.es_code DESC ";

        }
        else{
            $sql = "SELECT estimated_bills.id AS ESID, estimated_bills.es_code AS ESCODE, estimated_bills.patient_id as PID, estimated_bills.valid_till AS valid_date, estimated_bills.total_estimate AS total, estimated_bills.date_created AS created_on,estimated_bills.status AS status,estimated_bills.narration AS narration,estimated_bills.created_by AS created_by, CONCAT_WS(' ', pd.title, pd.fname, pd.mname, pd.lname) AS Fullname, pd.patient_ID, insurance_schemes.scheme_name AS scheme_name, insurance_schemes.credit_limit AS credit_limit, insurance_schemes.pay_type AS pay_type FROM estimated_bills LEFT JOIN patient_demograph pd ON estimated_bills.patient_id = pd.patient_ID LEFT JOIN insurance_schemes ON  estimated_bills.scheme_id = insurance_schemes.id WHERE estimated_bills.patient_id=".$pid;

        }
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $total = $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("ERROR: Failed to return total number of records");
        }

        $page = ($page > 0) ? $page : 0;
        $offset = ($page > 0) ? $pageSize * $page : 0;

        try {
            $pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;

            $sql .= " LIMIT $offset, $pageSize";
            //error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            $estimated_bills = [];

            require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $estimated_bill = (object)null;

                /*$patient = (new PatientDemographDAO())->getPatient($row['patient_ID'], TRUE, $pdo);
                if($patient !== null){*/
                $estimated_bill->isAdmitted = (new Manager())->isAdmitted($row['patient_ID']); //$patient->getFullname();
                $estimated_bill->escode = $row['ESCODE'];
                $estimated_bill->esid = $row['ESID'];
                $estimated_bill->Patient = $row['Fullname'];//$patient->getFullname();
                $estimated_bill->PatientID = $row['patient_ID'];
                $estimated_bill->created_on = $row['created_on'];
                $estimated_bill->pid = $row['PID'];
                $estimated_bill->period = $row['valid_date'];
                $estimated_bill->credit_limit = $row['credit_limit'];
                $estimated_bill->pay_type = $row['pay_type'];
                $estimated_bill->Scheme = $row['scheme_name'];
                $estimated_bill->total = $row['total'];
                $estimated_bill->created_by = $row['created_by'];
                $estimated_bill->status = $row['status'];
                $estimated_bill->narration = $row['narration'];

                $estimated_bills[] = $estimated_bill;
                /*} else {
                        error_log("NULL OBJECT FOR PATIENT: (".$row['patient_ID'].")");
                }*/

            }
        } catch (PDOException $e) {
            errorLog($e);
            $estimated_bills = [];
        }

        $results = (object)null;
        $results->data = $estimated_bills;
        $results->total = $total;
        $results->page = $page;

        return $results;

    }

    function AddEstimatedBill($esbill, $pdo = null)
    {

        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;


            $sql = "INSERT INTO estimated_bills (es_code, patient_id, valid_till, total_estimate, date_created, last_modified,inpatient_id,scheme_id,status,created_by,narration) VALUES ('".$esbill->getEsCode()."',".$esbill->getPatient()->getId().",'".$esbill->getValidTill()."',".$esbill->getTotalEstimate().",'".$esbill->getDateCreated()."','".$esbill->getLastModified()."',".$esbill->getInpatient().", ".$esbill->getScheme().",'".$esbill->getStatus()."',".$esbill->getCreatedBy()->getId().",'".escape($esbill->getNarration())."')";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $stmt = null;
        } catch (PDOException $e) {
            errorLog($e);
            $stmt = null;
            $esbill = null;
        }
        return $esbill;
    }

    function approveEstimatedBill($es_bill, $pdo = null)
    {
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $sql = "UPDATE estimated_bills SET status = '" . $es_bill->getStatus() . "' WHERE id = " . $es_bill->getId();

            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                return $es_bill;
            } else {
                return null;
            }
        } catch (PDOException $e) {
            errorLog($e);
            return null;
        }
    }

    function updateTotalEstimate($es_bill, $pdo = null)
    {
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $sql = "UPDATE estimated_bills SET total_estimate = '" . $es_bill->getTotalEstimate() . "', narration = '".escape($es_bill->getNarration())."', last_modified = '".$es_bill->getLastModified()."' WHERE id = " . $es_bill->getId();

            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                return $es_bill;
            } else {
                return null;
            }
        } catch (PDOException $e) {
            errorLog($e);
            return null;
        }
    }

}