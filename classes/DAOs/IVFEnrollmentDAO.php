<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/17/16
 * Time: 5:05 PM
 */
class IVFEnrollmentDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/IVFEnrollment.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Package.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/IVFProtocolDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bill.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AntenatalPackages.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PackageDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalNoteDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackagesDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAntenatalUsagesDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackageItemsDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($id, $full = FALSE, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM enrollments_ivf WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				if ($full) {
					$package = (new PackageDAO())->get($row['package_id'], $pdo);
					$enrolledBy = (new StaffDirectoryDAO())->getStaff($row['enrolled_by_id'], FALSE, $pdo);
					$closedBy = (new StaffDirectoryDAO())->getStaff($row['closed_by'], FALSE, $pdo);
					$patient = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$husband = (new PatientDemographDAO())->getPatient($row['husband_id'], FALSE, $pdo);
				} else {
					$package = new Package($row['package_id']);
					$enrolledBy = new StaffDirectory($row['enrolled_by_id']);
					$closedBy = new StaffDirectory($row['closed_by']);
					$patient = new PatientDemograph($row['patient_id']);
					$husband = new PatientDemograph($row['husband_id']);
				}
				$hormone = array('fsh' => $row['hormone_fsh'], 'lh' => $row['hormone_lh'], 'prol' => $row['hormone_prol'], 'amh' => $row['hormone_amh']);
				$sfa = array('count' => $row['sfa_count'], 'motility' => $row['sfa_motility'], 'morphology' => $row['sfa_morphology']);
				$serology = array('hiv' => $row['serology_hiv'], 'hep_b' => $row['serology_hep_b'], 'hep_c' => $row['serology_hep_c'], 'vdrl' => $row['serology_vdrl'], 'chlamydia' => $row['serology_chlamydia']);
				$stimulation = array('cycle' => $row['stimulation_cycle'], 'lmp_date' => $row['stimulation_lmp_date'], 'method' => (new IVFProtocolDAO())->get($row['stimulation_method'], $pdo), 'fsh' => $row['stimulation_fsh'], 'hmg' => $row['stimulation_hmg']);
				$husbandHormone = array('fsh'=> $row['husband_hormone_fsh'], 'lh'=> $row['husband_hormone_lh'], 'prol'=>$row['husband_hormone_prol'], 'testosterone'=> $row['husband_hormone_testosterone']);
				$husbandSerology = array('hiv' => $row['husband_serology_hiv'], 'hep_b' => $row['husband_serology_hep_b'], 'hep_c' => $row['husband_serology_hep_c'], 'vdrl' => $row['husband_serology_vdrl'], 'rbs' => $row['husband_serology_rbs'], 'fbs' => $row['husband_serology_fbs']);
				return (new IVFEnrollment($row['id']))->setActive((bool)$row['active'])
					->setPatient($patient)->setHusband($husband)
					->setFileNo($row['ivf_file_no'])->setDateEnrolled($row['date_enrolled'])
					->setEnrolledBy($enrolledBy)->setIndication($row['indication'])
					->setHormone($hormone)->setSfa($sfa)
					->setSerology($serology)
					->setStimulation($stimulation)->setPackage($package)
					->setClosedBy($closedBy)->setClosedDate($row['closed_on'])
					->setHusbandHormone($husbandHormone)
					->setHusbandSerology($husbandSerology);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function getActiveInstance($pid, $full, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM enrollments_ivf WHERE patient_id=$pid AND active IS TRUE";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return $this->get($row['id'], $full, $pdo);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function getInstances($pid, $full, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM enrollments_ivf WHERE patient_id=$pid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$data = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $full, $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
}