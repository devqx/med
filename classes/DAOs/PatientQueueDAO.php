<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PatientQueueDAO
 *
 * @author pauldic
 */
class PatientQueueDAO
{
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bill.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Lab.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffSpecialization.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Appointment.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .  '/classes/DAOs/AptClinicDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DepartmentDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAntenatalUsagesDAO.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function addPatientQueue($pq, $pdo = null)
    {
        /*try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;

            $queue = $this->inQueue($pq->getPatient()->getId(), $pq->getType(), $pq->getSpecialization(), FALSE, $pdo);
            if ($queue !== null) {
                return $queue;
            }
            $follow_up = ($pq->getFollowUp() == null || $pq->getFollowUp() == '') ? 0 : $pq->getFollowUp();
            $price = $pq->getAmount() ? $pq->getAmount() : 0;
            $sql = "INSERT INTO patient_queue (patient_id, type, sub_type, department_id, tag_no, specialization_id, follow_up, amount, clinic_id) VALUES " . "('" . $pq->getPatient()->getId() . "', '" . $pq->getType() . "', " . ($pq->getSubType() !== null ? "'" . $pq->getSubType() . "'" : "NULL") . ", " . ((is_null($pq->getDepartment()) || $pq->getDepartment() === null) ? 'NULL' : $pq->getDepartment()->getId()) . ", " . $this->generateTagNo($pq->getType(), $pdo) . ", " . ($pq->getSpecialization() !== null ? "'" . $pq->getSpecialization()->getId() . "'" : "NULL") . ", " . $follow_up . ", $price, 2)";

            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $pq->setId($pdo->lastInsertId());

            } else {
                $pq = null;
            }
        } catch (PDOException $e) {
            errorLog($e);
            $pq = null;
        }*/

        return $pq->add($pdo);
    }

    function counter($pdo = null)
    {
        $extra = "";
        if (MainConfig::$approvedQueueDailyOnly) {
            $extra = " AND DATE(approved_time) = DATE(NOW())";
        }

        $count = (object)null;

        $count->mail = 0;
        $count->notification = 0;
        $count->queue = 0;
        $count->aqueue = 0;
        $count->appointment = 0;
        $count->referral = 0;
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT a.queue, b.aQueue, c.ap, d.rfQ FROM (SELECT COUNT(*) as queue FROM patient_queue LEFT JOIN patient_demograph ON patient_demograph.patient_ID=patient_queue.patient_id WHERE patient_demograph.active=TRUE AND DATE(entry_time) = DATE(NOW()) AND `status` IN ('Active', 'Blocked')) a JOIN (SELECT COUNT(*) AS aQueue FROM approved_queue WHERE queue_read IS FALSE $extra) b JOIN (SELECT COUNT(*) as ap FROM appointment LEFT JOIN appointment_group ag ON group_id = ag.id LEFT JOIN patient_demograph d ON d.patient_ID=ag.patient_id WHERE d.active IS TRUE AND DATE(start_time) = DATE(NOW()) AND `status` IN ('Active', 'Scheduled')) c JOIN (SELECT COUNT(*) AS rfQ FROM referrals_queue rr LEFT JOIN patient_demograph pd ON pd.patient_ID=rr.patient_id WHERE pd.active IS TRUE AND rr.acknowledged IS FALSE) d ON 1=1";
            //" #AND department_id = ".$department_id." #GROUP BY patient_id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {

                $count->mail = 0;
                $count->notification = 0;
                $count->queue = (int)$row['queue'];
                $count->aqueue = (int)$row['aQueue'];
                $count->appointment = (int)$row['ap'];
                $count->referral = (int)$row['rfQ'];
            }

        } catch (PDOException $e) {
            errorLog($e);
        }
        return $count;
    }

    function countQueueByDate($start, $end, $status = ['active'], $department_id, $pdo = null)
    {
        $count = 0;
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            //todo this query is a little slow and should be boosted
            $sql = "SELECT COUNT(*) AS x FROM patient_queue LEFT JOIN patient_demograph ON patient_demograph.patient_ID=patient_queue.patient_id WHERE patient_demograph.active=TRUE AND Date(entry_time) BETWEEN '" . $start . "' AND '" . $end . "' AND `status` IN ('" . implode("', '", $status) . "') #AND department_id = " . $department_id . " #GROUP BY patient_id";
            // error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $count = $row['x'];
            }
            $stmt = null;
        } catch (PDOException $e) {
            $stmt = null;
            $count = 0;
        }
        return $count;
    }

    function inQueue($pid, $type, $specialization=null, $getFull = FALSE, $pdo = null)
    {
        $queue = new PatientQueue();
        $specializationId = $specialization == null ? " IS NULL" : "=".$specialization->getId();
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM patient_queue WHERE patient_id=" . $pid . " AND Date(entry_time) = Date(NOW()) AND type='" . $type . "' AND specialization_id {$specializationId} AND status ='Active'";
            //error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $queue->setId($row['id']);
                if ($getFull) {
                    $pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
                    $spe = (new StaffSpecializationDAO())->get($row['specialization_id'], $pdo);
                    $blockedBy = (new StaffDirectoryDAO())->getStaff($row['blocked_by'], FALSE, $pdo);
                    $seenBy = (new StaffDirectoryDAO())->getStaff($row['seen_by'], FALSE, $pdo);
                } else {
                    $pat = new PatientDemograph($row['patient_id']);
                    $spe = new StaffSpecialization($row['specialization_id']);
                    $blockedBy = new StaffDirectory($row['blocked_by']);
                    $seenBy = new StaffDirectory($row['seen_by']);
                }
                $queue->setPatient($pat);
                $queue->setType($row['type']);
                $queue->setSubType($row['sub_type']);
                $queue->setEntryTime($row['entry_time']);
                $queue->setAttendedTime($row['attended_time']);
                $queue->setTagNo($row['tag_no']);
                $queue->setBlockedBy($blockedBy);
                $queue->setSeenBy($seenBy);
                $queue->setSpecialization($spe);
                $queue->setDepartment((new DepartmentDAO())->get($row['department_id'], $pdo));
                $queue->setStatus($row['status']);
                $queue->setFollowUp($row['follow_up']);
            } else {
                $queue = null;
            }
            $stmt = null;
        } catch (PDOException $e) {
            $queue = null;
        }
        return $queue;
    }

    function getPatientQueue($qid, $getFull = FALSE, $pdo = null)
    {
        $queue = new PatientQueue();
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM patient_queue WHERE id=" . $qid;
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $queue->setId($row['id']);
                if ($getFull) {
                    $pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
                    $spe = (new StaffSpecializationDAO())->get($row['specialization_id'], $pdo);
                    $blockedBy = (new StaffDirectoryDAO())->getStaff($row['blocked_by'], FALSE, $pdo);
                    $seenBy = (new StaffDirectoryDAO())->getStaff($row['seen_by'], FALSE, $pdo);
                } else {
                    $pat = new PatientDemograph($row['patient_id']);
                    $spe = new StaffSpecialization($row['specialization_id']);
                    $blockedBy = new StaffDirectory($row['blocked_by']);
                    $seenBy = new StaffDirectory($row['seen_by']);
                }
                $queue->setPatient($pat);
                $queue->setType($row['type']);
                $queue->setSubType($row['sub_type']);
                $queue->setEntryTime($row['entry_time']);
                $queue->setAttendedTime($row['attended_time']);
                $queue->setTagNo($row['tag_no']);
                $queue->setBlockedBy($blockedBy);
                $queue->setSeenBy($seenBy);
                $queue->setSpecialization($spe);
                $queue->setStatus($row['status']);
                $queue->setClinic((new AptClinicDAO())->get($row['clinic_id'], $pdo));
                $queue->setDepartment((new DepartmentDAO())->get($row['department_id'], $pdo));
                $queue->setFollowUp($row['follow_up']);
            } else {
                $queue = null;
            }

            $stmt = null;
        } catch (PDOException $e) {
            $queue = null;
        }
        return $queue;
    }

    function getPatientQueues($start = null, $end = null, $types = [], $getFull = FALSE, $pdo = null)
    {
        $queues = array();
        $filter = (($start === null && $end === null) ? (sizeof($types) === 0 ? "" : "WHERE (type='" . implode($types, "' or type='") . "')") : ("WHERE (Date(entry_time) BETWEEN '" . $start . "' AND '" . $end . "') " . (sizeof($types) === 0 ? "" : " AND (type='" . implode($types, "' or type='") . "')")));
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM patient_queue $filter";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $queue = new PatientQueue();
                $queue->setId($row['id']);
                if ($getFull) {
                    $pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
                    $spe = (new StaffSpecializationDAO())->get($row['specialization_id'], $pdo);
                    $blockedBy = (new StaffDirectoryDAO())->getStaff($row['blocked_by'], FALSE, $pdo);
                    $seenBy = (new StaffDirectoryDAO())->getStaff($row['seen_by'], FALSE, $pdo);
                } else {
                    $pat = new PatientDemograph($row['patient_id']);
                    $spe = new StaffSpecialization($row['specialization_id']);
                    $blockedBy = new StaffDirectory($row['blocked_by']);
                    $seenBy = new StaffDirectory($row['seen_by']);
                }
                $queue->setPatient($pat);
                $queue->setType($row['type']);
                $queue->setSubType($row['sub_type']);
                $queue->setEntryTime($row['entry_time']);
                $queue->setAttendedTime($row['attended_time']);
                $queue->setTagNo($row['tag_no']);
                $queue->setBlockedBy($blockedBy);
                $queue->setSeenBy($seenBy);
                $queue->setSpecialization($spe);
                $queue->setStatus($row['status']);
                $queue->setDepartment((new DepartmentDAO())->get($row['department_id'], $pdo));
                $queue->setFollowUp($row['follow_up']);
                $queues[] = $queue;
            }
            $stmt = null;
        } catch (PDOException $e) {
            $queues = array();
        }
        return $queues;
    }

    /**
     * Type and Status accepts the default value of [] which translates to the *
     * @param null $start
     * @param null $end
     * @param array $types
     * @param array $status
     * @param bool $getFull
     * @param int $page
     * @param int $pageSize
     * @param null $Dept
     * @param null $sp
     * @param null $patId
     * @param null $pdo
     * @return mixed
     */
    function getPatientQueueFiltered($start = null, $end = null, $types = [], $status = [], $getFull = FALSE, $page = 0, $pageSize = 10, $Dept = null, $sp = null, $patId = null, $imgSubType = null, $pdo = null)
    {
        $queues = array();
        $start = ($start === null) ? date("Y-m-d") : $start;
        $end = ($end === null) ? date("Y-m-d") : $end;
        $types = (sizeof($types) === 0) ? getTypeOptions('type', 'patient_queue', $pdo) : $types;


        $TYPE_STR = ($types[0] === '' || $types[0] === '_' || $types[0] === null) ? '' : "AND q.type IN ('" . implode("', '", $types) . "')";

        $specialty = ($sp != null && $sp != '_') ? " AND q.specialization_id IN (" . implode(",", $sp) . ")" : '';
        $Patient = ($patId != null && $patId != '') ? ' AND q.patient_id = ' . $patId : '';

        $status = (sizeof($status) === 0) ? getTypeOptions('status', 'patient_queue', $pdo) : $status;
        $dept = ($Dept !== null && $Dept !== "" && $Dept !== "_") ? " AND q.department_id=" . $Dept : "";

        $imgSub = ($imgSubType !== null) ? ' AND q.sub_type="' . escape($imgSubType) . '"' : "";
        $sql = "SELECT QUEUE_TRIAGED(q.encounter_id) AS triaged, q.*, d.active AS patientActive, CONCAT_WS(' ', d.fname, d.mname, d.lname) AS patientName, sc.scheme_name, b.icon, dept.name AS departmentName, cs.staff_type AS specializationName, CONCAT_WS(' ', s1.firstname, s1.lastname) AS blockedBy, CONCAT_WS(' ', s2.firstname, s2.lastname) AS seenBy FROM patient_queue q LEFT JOIN  patient_demograph d ON q.patient_id=d.patient_ID LEFT JOIN staff_directory s1 ON s1.staffId=q.blocked_by LEFT JOIN staff_directory s2 ON s2.staffId=q.seen_by LEFT JOIN departments dept ON dept.id=q.department_id LEFT JOIN insurance i ON i.patient_id=d.patient_ID LEFT JOIN insurance_schemes sc ON i.insurance_scheme=sc.id LEFT JOIN badge b ON b.id=sc.badge_id LEFT JOIN staff_specialization cs ON cs.id=q.specialization_id WHERE d.active IS TRUE AND DATE(entry_time) BETWEEN '$start' AND '$end' AND q.status IN ('" . implode("', '", $status) . "') {$TYPE_STR}{$specialty}{$dept}{$Patient}{$imgSub} ORDER BY entry_time";
        $total = 0;
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $total = $stmt->rowCount();
        } catch (PDOException $e) {
            errorLog($e);
        }

        $page = ($page > 0) ? $page : 0;
        $offset = ($page > 0) ? $pageSize * $page : 0;

        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $sql .= " LIMIT $offset, $pageSize";
            //error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {

                $queue = (object)null; //new PatientQueue($row['id']);
                $queue->Id = $row['id'];
                $pid = $row['patient_id'];
                $Outstanding = 0;
                try {
                    $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
                    $outstanding = "select (t1.payments + t2.charges - t3.amount) AS outstanding FROM (SELECT COALESCE(SUM(amount),0) AS payments FROM bills b LEFT JOIN insurance_schemes ON insurance_schemes.id = b.billed_to WHERE patient_id = $pid AND (transaction_type = 'debit' OR transaction_type = 'discount' OR transaction_type = 'reversal' OR transaction_type = 'write-off' OR transaction_type = 'transfer-debit') AND cancelled_on IS NULL AND insurance_schemes.pay_type = 'self') t1 join (SELECT COALESCE(SUM(amount),0) AS charges FROM bills b LEFT JOIN insurance_schemes ON insurance_schemes.id = b.billed_to WHERE patient_id = $pid AND (transaction_type = 'credit' OR transaction_type = 'refund' OR transaction_type = 'transfer-credit') AND insurance_schemes.pay_type = 'self' AND cancelled_on IS NULL) t2 join (SELECT amount FROM credit_limit WHERE patient_id=$pid) t3";
                    $stmt_ = $pdo->prepare($outstanding, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                    $stmt_->execute();
                    $res = $stmt_->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
                    $Outstanding = $res['outstanding'];
                } catch (PDOException $e) {
                    errorLog($e);
                }
                $queue->PatientId = $row['patient_id'];
                $queue->Coverage = $row['scheme_name'];
                $queue->BadgeIcon = $row['icon'];
                $queue->PatientActive = $row['patientActive'];
                $queue->PatientName = $row['patientName'];
                $queue->Type = $row['type'];
                $queue->SubType = $row['sub_type'];
                $queue->EntryTime = ($row['entry_time']);
                $queue->AttendedTime = ($row['attended_time']);
                $queue->TagNo = ($row['tag_no']);
                $queue->BlockedBy = $row['blockedBy'];
                $queue->SeenBy = $row['seenBy'];
                $queue->Specialization = $row['specializationName'];
                $queue->DepartmentId = $row['department_id'];
                $queue->DepartmentName = $row['departmentName'];
                $queue->Status = $row['status'];
                $queue->FollowUp = (bool)$row['follow_up'];
                $queue->Review = (bool)$row['review'];
                $queue->Triaged = (bool)$row['triaged'];
                $queue->Outstanding = $Outstanding;
                $queues[] = $queue;
            }
            $stmt = null;
        } catch (PDOException $e) {
            $queues = array();
        }

        $results = (object)null;
        $results->data = $queues;
        $results->total = $total;
        $results->page = $page;

        return $results;
    }

    function getPatientsInQueue($start = null, $end = null, $types = [], $status = [], $getFull = FALSE, $page = 0, $pageSize = 10, $Dept = null, $sp = null, $patFilter = null, $pdo = null)
    {
        $patients = array();
        $start = ($start === null) ? date("Y-m-d") : $start;
        $end = ($end === null) ? date("Y-m-d") : $end;
        $types = (sizeof($types) === 0) ? getTypeOptions('type', 'patient_queue', $pdo) : $types;

        $TYPE_STR = ($types[0] === '' || $types[0] === '_' || $types[0] === null) ? '' : "AND type IN ('" . implode("', '", $types) . "')";

        $specialty = ($sp != null && $sp != '_') ? ' AND specialization_id = ' . $sp : '';

        $status = (sizeof($status) === 0) ? getTypeOptions('status', 'patient_queue', $pdo) : $status;
        $dept = ($Dept !== null && $Dept !== "" && $Dept !== "_") ? " AND department_id=" . $Dept : "";
        $sql = "SELECT q.patient_id FROM patient_queue q WHERE DATE(entry_time) BETWEEN '$start' AND '$end' AND status IN ('" . implode("', '", $status) . "')${TYPE_STR}${specialty}${dept} ORDER BY entry_time";

        $SQL = "SELECT d.*, d.patient_ID AS patientId FROM patient_demograph d WHERE d.patient_ID IN ($sql) AND (d.fname LIKE '%$patFilter%' OR d.patient_ID LIKE '%$patFilter%' OR d.lname LIKE '%$patFilter%')";
        //return ($SQL);
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $stmt = $pdo->prepare($SQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
//                $patients[] = (new PatientDemographDAO())->getPatient($row['patient_ID'], FALSE, $pdo, FALSE);
                $patients[] = $row;
            }
            return $patients;
        } catch (PDOException $e) {
            errorLog($e);
            return [];
        }
    }

    function getApproximateQueueItem($start, $type, $sp = null, $patient=null, $pdo = null)
    {
        $specialization = $sp == null ? 'NULL' : $sp->getId();
        $patientId = $patient==null ? 'NULL' : $patient->getId();
        $sql = "SELECT * FROM patient_queue WHERE DATE(entry_time)=DATE('$start') AND specialization_id=$specialization AND patient_id=$patientId AND type='$type'";
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                return $this->getPatientQueue($row['id'], TRUE, $pdo);
            }
            return null;
        } catch (PDOException $e) {
            errorLog($e);
            return null;
        }
    }

    function changeQueueStatus($queue, $pdo = null)
    {
        $status = TRUE;

        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $oldStatus = (new PatientQueueDAO())->getPatientQueue($queue->getId(), FALSE, $pdo)->getStatus();
            if ($oldStatus !== $queue->getStatus()) {
                $sql = "UPDATE patient_queue SET `status` = '" . $queue->getStatus() . "'";
                if ($queue->getStatus() === "Attended") {
                    $sql .= ", attended_time='" . date("Y-m-d H:i:s") . "', seen_by='" . $queue->getSeenBy()->getId() . "', amount=" . $queue->getAmount();
                } else if ($queue->getStatus() === "Blocked") {
                    $sql .= ", blocked_by='" . $queue->getBlockedBy()->getId() . "'";
                }
                $sql .= " WHERE id=" . $queue->getId();
                $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $stmt->execute();
                $stmt = null;
            } else {
                return true;
            }
        } catch (PDOException $e) {
            errorLog($e);
            $status = FALSE;
        }
        return $status;
    }

    function cancelQueue($qid = null, $pid = null, $pdo = null)
    {
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $sql = "UPDATE patient_queue SET `status` = 'Cancelled' WHERE " . ($qid === null ? "patient_id=$pid AND status='Active'" : "id=$qid");
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $status = TRUE;
            $stmt = null;
        } catch (PDOException $e) {
            errorLog($e);
            $status = FALSE;
        }
        return $status;
    }

    function generateTagNo($cid, $pdo)
    {
        $counter = 0;
        try {
            $sql = "SELECT count(*) AS x FROM patient_queue WHERE clinic_id='" . $cid . "' AND DATE(entry_time) = Date(NOW())";
//          error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $counter = $row['x'];
            }
            $stmt = null;
        } catch (PDOException $e) {
            $stmt = null;
//            error_log(print_r($e, TRUE));
            throw new PDOException("Unable to generate Tag no", 001, $e);
        }
        return ($counter + 1);
    }

    function getSubTypes($pdo = null)
    {
        $subTypes = array();
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT DISTINCT(sub_type) AS sub_type FROM patient_queue WHERE sub_type IS NOT NULL";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $subTypes[] = $row['sub_type'];
            }
            $stmt = null;
        } catch (PDOException $e) {
            $subTypes = array();
        }
        return $subTypes;
    }
}

