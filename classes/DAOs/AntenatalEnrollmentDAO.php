<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AntenatalEnrollmentDAO
 *
 * @author pauldic
 */
class AntenatalEnrollmentDAO
{

	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AntenatalEnrollment.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bill.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AntenatalPackages.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalNoteDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackagesDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAntenatalUsagesDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackageItemsDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function create($ae, $pdo = null)
	{
		//$ae = new AntenatalEnrollment();
		$service_cent_id = (!is_blank($ae->getServiceCenter()) ? $ae->getServiceCenter()->getId() : "NULL");
		$obgyn_id = ($ae->getObgyn() ? $ae->getObgyn()->getId() : "NULL");
		$baby_father_name = (!is_blank($ae->getBabyFatherName()) ? quote_esc_str($ae->getBabyFatherName()) : "NULL");
		$baby_father_phone = (!is_blank($ae->getBabyFatherPhone()) ? quote_esc_str($ae->getBabyFatherPhone()) : "NULL");
		$baby_father_blood_group = (!is_blank($ae->getBabyFatherBloodGroup()) ? quote_esc_str($ae->getBabyFatherBloodGroup()) : "NULL");
		$package_id = (!is_blank($ae->getPackage()) ? $ae->getPackage()->getId() : "NULL");
		$lmp = !is_blank($ae->getLmpDate()) ? quote_esc_str($ae->getLmpDate()) : "NULL";
		$ed_date = !is_blank($ae->getEdDate()) ? quote_esc_str($ae->getEdDate()) : "NULL";
		$lmp_source = !is_blank($ae->getLmpSource()) ? quote_esc_str($ae->getLmpSource()) : "NULL";
		$package = (new AntenatalPackagesDAO())->get($package_id, $pdo);
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO enrollments_antenatal(patient_id, package_id, enrolled_at, enrolled_by, booking_indication, complication_note, obgyn_id, lmp_date, lmp_source, ed_date, baby_father_name, baby_father_phone, baby_father_blood_group, gravida, para, alive, abortions, service_center_id) VALUES (" . $ae->getPatient()->getId() . ", $package_id, " . $ae->getEnrolledAt()->getId() . ", " . $ae->getEnrolledBy()->getId() . ", '" . $ae->getBookingIndication() . "', '" . escape($ae->getComplicationNote()) . "', " . $obgyn_id . ", $lmp, $lmp_source, $ed_date, " . $baby_father_name . ", " . $baby_father_phone . ", $baby_father_blood_group, " . quote_esc_str($ae->getGravida()) . ", " . quote_esc_str($ae->getPara()) . ", " . quote_esc_str($ae->getAlive()) . ", " . quote_esc_str($ae->getAbortions()) . ",$service_cent_id)";

			$canCommit = !$pdo->inTransaction();
			try {
				$pdo->beginTransaction();
			} catch (PDOException $e) {
				//Transaction is already started
			}
			$pat = (new PatientDemographDAO())->getPatient($ae->getPatient()->getId(), FALSE, $pdo);

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$ae->setId($pdo->lastInsertId());

			$package_items = (new AntenatalPackageItemsDAO())->getItemsByPackage($ae->getPackage()->getId(), $pdo);
			foreach ($package_items as $k => $items) {
				$item = new PatientAntenatalUsages();
				$item->setPatient($ae->getPatient());
				$item->setAntenatal($ae);
				$item->setItem($items->getName());
				$item->setType($items->getType());
				$item->setUsages($items->getUsage());
				$add_package_items = (new PatientAntenatalUsagesDAO())->addItem($item, $pdo);
			}

			$bil = new Bill();
			$bil->setPatient($ae->getPatient());
			$bil->setDescription("Antenatal Enrollment charge: " . $package->getName());
			$amount = (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($package->getCode(), $pat->getScheme()->getId(), TRUE, FALSE, $pdo)->selling_price;

			$bil->setItem($package);
			$bil->setSource((new BillSourceDAO())->findSourceById(15, $pdo));
			$bil->setTransactionType("credit");
			$bil->setAmount($amount);
			$bil->setDiscounted(null);
			$bil->setDiscountedBy(null);
			$bil->setClinic($ae->getEnrolledAt());
			$bil->setBilledTo($pat->getScheme());
			$bil->setReferral(null);
			$bil->setCostCentre(null);

			$bill = (new BillDAO())->addBill($bil, 1, $pdo);

			if ($stmt->rowCount() > 0 && $bill != null) {
				if ($canCommit) {
					$pdo->commit();
				}
				return $ae;
			} else {
				$ae = null;
				if ($canCommit) {
					$pdo->rollBack();
				}
			}

			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$ae = null;
			$stmt = null;
		} catch (Exception $e) {
			errorLog($e);
			$ae = null;
			$stmt = null;
		}

		return $ae;
	}

	function isEnrolled($pid, $pdo = null)
	{
		$status = FALSE;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM enrollments_antenatal WHERE patient_id =" . $pid . " AND active IS TRUE";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() >= 1) {
				$status = TRUE;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$status = FALSE;
		}
		return $status;
	}

	function get($id, $getFull = FALSE, $pdo = null)
	{
		$aEnroll = new AntenatalEnrollment();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM enrollments_antenatal WHERE id = " . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				if ($getFull) {
					$patient = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$at = (new ClinicDAO())->getClinic($row['enrolled_at'], FALSE, $pdo);
					$by = (new StaffDirectoryDAO())->getStaff($row['enrolled_by'], FALSE, $pdo);
					$obgyn = (new StaffDirectoryDAO())->getStaff($row['obgyn_id'], FALSE, $pdo);
				} else {
					$patient = new PatientDemograph($row["patient_id"]);
					$at = new Clinic($row['enrolled_at']);
					$by = new StaffDirectory($row['enrolled_by']);
					$obgyn = !is_blank($row['obgyn_id']) ? new StaffDirectory($row['obgyn_id']) : NULL;
				}
				$aEnroll->setId($row['id']);
				$aEnroll->setActive(boolval($row['active']));
				$aEnroll->setRequestCode($row['requestCode']);
				$aEnroll->setNotes((new AntenatalNoteDAO())->getInstanceNotes($row['id'], $pdo));
				$aEnroll->setPatient($patient);
				$aEnroll->setEnrolledAt($at);
				$aEnroll->setEnrolledOn($row['enrolled_on']);
				$aEnroll->setEnrolledBy($by);
				$aEnroll->setBookingIndication($row['booking_indication']);
				$aEnroll->setComplicationNote($row['complication_note']);
				$aEnroll->setObgyn($obgyn);
				$aEnroll->setLmpDate($row['lmp_date']);
				$aEnroll->setLmpSource($row['lmp_source']);
				$aEnroll->setEdDate($row['ed_date']);
				$aEnroll->setBabyFatherName($row['baby_father_name']);
				$aEnroll->setBabyFatherPhone($row['baby_father_phone']);
				$aEnroll->setBabyFatherBloodGroup($row['baby_father_blood_group']);
				$aEnroll->setGravida($row['gravida']); //convert to the object from the display dropdown
				$aEnroll->setPara($row['para']); //convert to the object from the display dropdown
				$aEnroll->setAlive($row['alive']); //convert to the object from the display dropdown
				$aEnroll->setAbortions($row['abortions']); //convert to the object from the display dropdown
				$aEnroll->setRecommendation($row['recommendation']); //convert to the object from the display dropdown

				$aEnroll->setDateClosed($row['date_closed']);
				$aEnroll->setCloseNote($row['close_note']);
				$aEnroll->setPackage((new AntenatalPackagesDAO())->get($row['package_id'], $pdo));

			} else {
				$aEnroll = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$aEnroll = null;
			$stmt = null;
		}
		return $aEnroll;
	}

	function getMin($id, $pdo = null)
	{
		$aEnroll = new AntenatalEnrollment();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM enrollments_antenatal WHERE id = " . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$aEnroll->setId((int)$row['id']);
				$aEnroll->setRequestCode($row['requestCode']);
				$aEnroll->setPatient((int)$row["patient_id"]);
				$aEnroll->setEnrolledOn($row['enrolled_on']);
				$aEnroll->setBookingIndication($row['booking_indication']);
				$aEnroll->setLmpDate($row['lmp_date']);
				$aEnroll->setLmpSource($row['lmp_source']);
				$aEnroll->setEdDate($row['ed_date']);
				$aEnroll->setBabyFatherName($row['baby_father_name']);
				$aEnroll->setGravida($row['gravida']); //convert to the object from the display dropdown
				$aEnroll->setPara($row['para']); //convert to the object from the display dropdown
				$aEnroll->setAlive($row['alive']); //convert to the object from the display dropdown
				$aEnroll->setAbortions($row['abortions']); //convert to the object from the display dropdown
				$aEnroll->setDateClosed($row['date_closed']);
				$aEnroll->setRecommendation($row['recommendation']);

			} else {
				$aEnroll = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$aEnroll = null;
			$stmt = null;
		}
		return $aEnroll;
	}

	function all($getFull = FALSE, $order = null, $pdo = null)
	{
		$aEnrolls = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM enrollments_antenatal ORDER BY enrolled_at";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$aEnrolls[] = $this->get($row['id'], $getFull, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$aEnrolls = [];
		}
		return $aEnrolls;
	}

	function allMin($pdo = null)
	{
		$aEnrolls = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM enrollments_antenatal WHERE date_closed IS NULL ORDER BY enrolled_at";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$aEnrolls[] = $this->getMin($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$aEnrolls = [];
		}
		return $aEnrolls;
	}

	public function getActiveInstance($pid, $getFull = FALSE, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM enrollments_antenatal WHERE patient_id = " . $pid . " AND active IS TRUE LIMIT 1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return $this->get($row['id'], $getFull, $pdo);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	public function getPatientInstances($pid, $getFull = FALSE, $pdo = null)
	{
		$instances = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM enrollments_antenatal WHERE patient_id =" . $pid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$instances[] = $this->get($row['id'], $getFull, $pdo);
			}
		} catch (PDOException $e) {
			return [];
		}
		return $instances;
	}

	function closeInstance($instance, $pdo = null)
	{
		$closeNote = escape($instance->getCloseNote());
		$closedBy = $_SESSION['staffID'];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE enrollments_antenatal SET active = FALSE, date_closed = NOW(), close_note = '$closeNote', closed_by=$closedBy WHERE id =" . $instance->getId() . " AND active IS TRUE";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() === 1) {
				return TRUE;
			}
			return FALSE;
		} catch (PDOException $e) {
			errorLog($e);
			return FALSE;
		}
	}
}