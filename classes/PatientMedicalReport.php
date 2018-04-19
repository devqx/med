<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/3/16
 * Time: 1:07 PM
 */
class PatientMedicalReport implements JsonSerializable
{
	private $id;
	private $requestCode;
	private $patient;
	private $exam;
	private $requestNote;
	private $requestBy;
	private $requestDate;
	private $approved;
	private $approvedBy;
	private $approvedDate;
	private $dateLastModified;
	private $cancelled;
	private $cancelledDate;
	private $cancelledBy;
	private $referral;
	private $notes;
	private $labs;
	private $imagings;
	private $procedures;
	private $notesCount;
	private $serviceCenter;
	private $bill = [];

	/**
	 * PatientMedicalReport constructor.
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }

	/**
	 * @return null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param null $id
	 * @return PatientMedicalReport
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRequestCode()
	{
		return $this->requestCode;
	}

	/**
	 * @param mixed $requestCode
	 * @return PatientMedicalReport
	 */
	public function setRequestCode($requestCode)
	{
		$this->requestCode = $requestCode;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPatient()
	{
		return $this->patient;
	}

	/**
	 * @param mixed $patient
	 * @return PatientMedicalReport
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getExam()
	{
		return $this->exam;
	}

	/**
	 * @param mixed $exam
	 * @return PatientMedicalReport
	 */
	public function setExam($exam)
	{
		$this->exam = $exam;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRequestNote()
	{
		return $this->requestNote;
	}

	/**
	 * @param mixed $requestNote
	 * @return PatientMedicalReport
	 */
	public function setRequestNote($requestNote)
	{
		$this->requestNote = $requestNote;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRequestBy()
	{
		return $this->requestBy;
	}

	/**
	 * @param mixed $requestBy
	 * @return PatientMedicalReport
	 */
	public function setRequestBy($requestBy)
	{
		$this->requestBy = $requestBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRequestDate()
	{
		return $this->requestDate;
	}

	/**
	 * @param mixed $requestDate
	 * @return PatientMedicalReport
	 */
	public function setRequestDate($requestDate)
	{
		$this->requestDate = $requestDate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getApproved()
	{
		return $this->approved;
	}

	/**
	 * @param mixed $approved
	 * @return PatientMedicalReport
	 */
	public function setApproved($approved)
	{
		$this->approved = $approved;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getApprovedBy()
	{
		return $this->approvedBy;
	}

	/**
	 * @param mixed $approvedBy
	 * @return PatientMedicalReport
	 */
	public function setApprovedBy($approvedBy)
	{
		$this->approvedBy = $approvedBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getApprovedDate()
	{
		return $this->approvedDate;
	}

	/**
	 * @param mixed $approvedDate
	 * @return PatientMedicalReport
	 */
	public function setApprovedDate($approvedDate)
	{
		$this->approvedDate = $approvedDate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDateLastModified()
	{
		return $this->dateLastModified;
	}

	/**
	 * @param mixed $dateLastModified
	 * @return PatientMedicalReport
	 */
	public function setDateLastModified($dateLastModified)
	{
		$this->dateLastModified = $dateLastModified;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCancelled()
	{
		return $this->cancelled;
	}

	/**
	 * @param mixed $cancelled
	 * @return PatientMedicalReport
	 */
	public function setCancelled($cancelled)
	{
		$this->cancelled = $cancelled;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCancelledDate()
	{
		return $this->cancelledDate;
	}

	/**
	 * @param mixed $cancelledDate
	 * @return PatientMedicalReport
	 */
	public function setCancelledDate($cancelledDate)
	{
		$this->cancelledDate = $cancelledDate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCancelledBy()
	{
		return $this->cancelledBy;
	}

	/**
	 * @param mixed $cancelledBy
	 * @return PatientMedicalReport
	 */
	public function setCancelledBy($cancelledBy)
	{
		$this->cancelledBy = $cancelledBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getReferral()
	{
		return $this->referral;
	}

	/**
	 * @param mixed $referral
	 * @return PatientMedicalReport
	 */
	public function setReferral($referral)
	{
		$this->referral = $referral;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getNotes()
	{
		return $this->notes;
	}

	/**
	 * @param mixed $notes
	 * @return PatientMedicalReport
	 */
	public function setNotes($notes)
	{
		$this->notes = $notes;
		return $this;
	}

	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}

	/**
	 * @return mixed
	 */
	public function getLabs()
	{
		return $this->labs;
	}

	/**
	 * @param mixed $labs
	 * @return PatientMedicalReport
	 */
	public function setLabs($labs)
	{
		$this->labs = $labs;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getImagings()
	{
		return $this->imagings;
	}

	/**
	 * @param mixed $imagings
	 * @return PatientMedicalReport
	 */
	public function setImagings($imagings)
	{
		$this->imagings = $imagings;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getProcedures()
	{
		return $this->procedures;
	}

	/**
	 * @param mixed $procedures
	 * @return PatientMedicalReport
	 */
	public function setProcedures($procedures)
	{
		$this->procedures = $procedures;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getNotesCount()
	{
		return $this->notesCount;
	}

	/**
	 * @param mixed $notesCount
	 * @return PatientMedicalReport
	 */
	public function setNotesCount($notesCount)
	{
		$this->notesCount = $notesCount;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getServiceCenter()
	{
		return $this->serviceCenter;
	}
	
	/**
	 * @param mixed $serviceCenter
	 *
	 * @return PatientMedicalReport
	 */
	public function setServiceCenter($serviceCenter)
	{
		$this->serviceCenter = $serviceCenter;
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getBill(): array
	{
		return $this->bill;
	}
	
	/**
	 * @param array $bill
	 *
	 * @return PatientMedicalReport
	 */
	public function setBill(array $bill=null): PatientMedicalReport
	{
		$this->bill = $bill;
		return $this;
	}
	
	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabGroup.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientScan.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
		$patient_id = $this->getPatient()->getId();
		$exam_id = $this->getExam()->getId();
		$request_note = !is_blank($this->getRequestNote())? quote_esc_str($this->getRequestNote()): "NULL";
		$requested_by_id = $this->getRequestBy() ? $this->getRequestBy()->getId() : $_SESSION['staffID'];
		$request_date = $this->getRequestDate() ? quote_esc_str($this->getRequestDate()): "NOW()";
		$referral_id = $this->getReferral() ? $this->getReferral()->getId() : "NULL";
		//$service_center_id = $this->getServiceCenter() ? $this->getServiceCenter()->getId() : "NULL";

		try {
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO(): $pdo;
			$pdo->beginTransaction();

			$medicalExam = (new MedicalExamDAO())->get($this->getExam()->getId(), $pdo);
			$labs =   $medicalExam->getLabs();
			$labRequestNo = [];
			if(count($labs) > 0){
				$request = new LabGroup();
				$request->setPatient($this->getPatient());
				$request->setInPatient(null);
				$request->setRequestedBy($this->getRequestBy());
				$request->setPreferredSpecimens(array());

				$lab_data = array();
				foreach ($labs as $l) {
					$lab_data[] = (new LabDAO())->getLab($l->getId(), FALSE, $pdo);
				}
				$request->setRequestData($lab_data);
				$request->setServiceCentre((new ServiceCenterDAO())->all('Lab', NULL, $pdo)[0]);
				$request->setReferral($this->getReferral());

				$data = (new PatientLabDAO())->newPatientLabRequest($request, true, $pdo);
				if($data === null){
					$pdo->rollBack();
					return null;
				}else {
					$labRequestNo[] = $data->getId();
				}
				$labRequestNo = quote_esc_str(implode(",", $labRequestNo));
			} else {
				$labRequestNo = "NULL";
			}

			$imagingRequest=[];
			$imagings = $medicalExam->getImagings();
			if(count($imagings) > 0){
				$scan = new PatientScan();
				$scan->setPatient( $this->getPatient() );

				foreach($imagings as $s){
					$scan_ids = [];
					$scan_ids[] = (new ScanDAO())->getScan($s->getId(), $pdo);

					$scan->setScan($scan_ids);
					$scan->setRequestDate(date("Y-m-d H:i:s"));
					$scan->setReferral( $this->getReferral() );
					$scan->setRequestNote($this->getRequestNote());

					$scan->setRequestedBy( (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], FALSE, $pdo) );

					$newScan = (new PatientScanDAO())->addScan($scan, true, $pdo);
					if($newScan === null){
						$pdo->rollBack();
						return null;
					}else {
						$imagingRequest[] = $newScan->getId();
					}

				}

				$imagingRequest = quote_esc_str(implode(",", $imagingRequest));
			} else {
				$imagingRequest = "NULL";
			}

			$procedures = $medicalExam->getProcedures();
			if(count($procedures) > 0){
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientProcedure.php';
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureDAO.php';
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
				$procedureRequest = [];
				foreach ($procedures as $procedure) {
					$p_procedure = new PatientProcedure();

					$p_procedure->setServiceCentre( (new ServiceCenterDAO())->all('Procedure',null, $pdo)[0] );
					$p_procedure->setProcedure($procedure);
					$p_procedure->setRequestDate($this->getRequestDate());
					$p_procedure->setPatient($this->getPatient());
					$p_procedure->setReferral($this->getReferral());

					$p_procedure->setConditions([]);

					$p_procedure->setTimeStart(null);
					$p_procedure->setTimeStop(null);

					$p_procedure->setHasAnesthesiologist(false);
					$p_procedure->setHasSurgeon(false);
					$p_procedure->setBilled(true);

					$p_procedure->setRequestedBy($this->getRequestBy());

					$new = (new PatientProcedureDAO())->add($p_procedure, true, $pdo);
					if($new === null){
						$pdo->rollBack();
						return null;
					}else {
						$procedureRequest[] = $new->getId();
					}
				}
				unset($procedure);
				$procedureRequest = quote_esc_str(implode(",",$procedureRequest));
			} else {
				$procedureRequest = "NULL";
			}
			
			$amount = (new InsuranceItemsCostDAO())->getItemPriceByCode($this->getExam()->getCode(), $this->getPatient()->getId(), TRUE, $pdo);
			
			$bil = new Bill();
			$bil->setPatient($this->getPatient());
			$bil->setDescription("".$this->getExam()->getName());
			
			$bil->setItem($this->getExam());
			$bil->setSource( (new BillSourceDAO())->findSourceById(12, $pdo) );
			$bil->setTransactionType("credit");
			$bil->setAmount($amount);
			$bil->setInPatient(null);
			$bil->setDiscounted(NULL);
			$bil->setDiscountedBy(NULL);
			$bil->setClinic(new Clinic(1));
			$bil->setBilledTo( (new InsuranceDAO())->getInsurance($this->getPatient()->getId(), FALSE, $pdo)->getScheme());
			$bil->setReferral($this->getReferral());
			$bil->setCostCentre( $this->getServiceCenter()->getCostCentre() );
			
			$bill = (new BillDAO())->addBill($bil, 1, $pdo, NULL);
			
			$billLineId = $bill != null && $bill->getId() ? (is_array($bill->getId()) ? "'".implode(",", $bill->getId())."'" : $bill->getId()) : "NULL";
			
			$sql = "INSERT INTO patient_medical_report (patient_id, exam_id, request_note, requested_by_id, request_date, referral_id, labs, imagings, procedures, bill_line_id) VALUES ($patient_id, $exam_id, $request_note, $requested_by_id, $request_date, $referral_id, $labRequestNo, $imagingRequest, $procedureRequest, $billLineId)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$this->setId($pdo->lastInsertId());
				$pdo->commit();
				return $this;
			}
			
			$pdo->rollBack();
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}

	public function update($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$requestCode = quote_esc_str($this->getRequestCode());
		$patient_id = $this->getPatient()->getId();
		$exam_id = $this->getExam()->getId();
		$request_note = quote_esc_str($this->getRequestNote());
		$requested_by_id = $this->getRequestBy()->getId();
		$request_date = quote_esc_str($this->getRequestDate());
		$approved = var_export((bool)$this->getApproved(), true);
		$approved_by_id = $this->getApprovedBy() ? $this->getApprovedBy()->getId() : "NULL";
		$approved_date = $this->getApprovedDate() ? quote_esc_str($this->getApprovedDate()) : "NULL";
		$cancelled = var_export((bool)$this->getCancelled(), true);
		$cancel_date = $this->getCancelledDate() ? quote_esc_str($this->getCancelledDate()) : "NOW()";
		$canceled_by_id = $this->getCancelledBy()? $this->getCancelledBy()->getId(): "NULL";
		$referral_id = $this->getReferral() ? $this->getReferral()->getId() : "NULL";
		try {
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO(): $pdo;
			$sql = "UPDATE `patient_medical_report` SET `requestCode`=$requestCode,`patient_id`=$patient_id,`exam_id`=$exam_id,`request_note`=$request_note,`requested_by_id`=$requested_by_id,`request_date`=$request_date,`approved`=$approved,`approved_by_id`=$approved_by_id,`approved_date`=$approved_date,`cancelled`=$cancelled,`cancel_date`=$cancel_date,`canceled_by_id`=$canceled_by_id,`referral_id`=$referral_id WHERE id=".$this->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() >= 0){
				return $this;
			}
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
}