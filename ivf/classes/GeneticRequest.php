<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/13/16
 * Time: 10:30 AM
 */
class GeneticRequest implements JsonSerializable
{
	private $id;
	private $requestCode;
	private $femalePatient;
	private $malePatient;
	private $referral;
	private $requestDate;
	private $user;
	private $reason;// also Request Note
	private $specimenType;
	private $lab;
	private $specimenReceiveDate;
	private $specimenReceiveBy;
	private $billing; //ignore this property for this now
	private $status;
	private $result;
	private $qualityControls;
	private $reagents;

	/**
	 * GeneticRequest constructor.
	 * @param $id
	 */
	public function __construct($id = null)
	{
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param mixed $user
	 * @return GeneticRequest
	 */
	public function setUser($user)
	{
		$this->user = $user;
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
	public function getRequestCode()
	{
		return $this->requestCode;
	}

	/**
	 * @param mixed $requestCode
	 * @return GeneticRequest
	 */
	public function setRequestCode($requestCode)
	{
		$this->requestCode = $requestCode;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBilling()
	{
		return $this->billing;
	}

	/**
	 * @param mixed $billing
	 * @return GeneticRequest
	 */
	public function setBilling($billing)
	{
		$this->billing = $billing;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getReagents()
	{
		return $this->reagents;
	}
	
	/**
	 * @param mixed $reagents
	 *
	 * @return GeneticRequest
	 */
	public function setReagents($reagents)
	{
		$this->reagents = $reagents;
		return $this;
	}
	
	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT']. '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT']. '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT']. '/ivf/classes/QualityControlType.php';
		require_once $_SERVER['DOCUMENT_ROOT']. '/ivf/classes/QualityControl.php';
		if(!isset($_SESSION)){@session_start();}
		$request = $this;
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$pdo->beginTransaction();
			$female_patient_id = $request->getFemalePatient() ? $request->getFemalePatient()->getId() : "NULL";
			$male_patient_id = $request->getMalePatient() ? $request->getMalePatient()->getId() : "NULL";
			$referral_id = $request->getReferral() ? $request->getReferral()->getId() : "NULL";
			$request_date = $request->getRequestDate() ? quote_esc_str($request->getRequestDate()) : "NOW()";
			$user = $request->getUser() ? $request->getUser()->getId() : $_SESSION['staffID'];
			$reason = $request->getReason() ? quote_esc_str($request->getReason()) : "NULL";
			$genetic_lab_id = $request->getLab() ? $request->getLab()->getId() : "NULL";
			$genetic_specimen_id = $request->getSpecimenType() ? $request->getSpecimenType()->getId() : "NULL";
			$specimen_received_on = $request->getSpecimenReceiveDate() ? quote_esc_str($request->getSpecimenReceiveDate()) : "NULL";
			$specimen_received_by = $request->getSpecimenReceiveBy() ? $request->getSpecimenReceiveBy()->getId() : "NULL";

			$sql = "INSERT INTO genetic_lab_request (female_patient_id, male_patient_id, referral_id, request_date, user_id, reason, genetic_lab_id, genetic_specimen_id, specimen_received_on, specimen_received_by) VALUES 
($female_patient_id, $male_patient_id, $referral_id, $request_date, $user, $reason, $genetic_lab_id, $genetic_specimen_id, $specimen_received_on, $specimen_received_by)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$request->setId($pdo->lastInsertId());
				$qualityCtrlIds = $request->getLab()->getQualityControls();
				foreach ($qualityCtrlIds as $qualityCtrlId){
					$GQC = (new QualityControl())->setType( new QualityControlType($qualityCtrlId))->setRequest($request)->add($pdo);
					if($GQC instanceof QualityControl){

					} else {
						$pdo->rollBack();
						return null;
					}
				}

				$pdo->commit();
				return $request;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	/**
	 * @return mixed
	 */
	public function getFemalePatient()
	{
		return $this->femalePatient;
	}

	/**
	 * @param mixed $femalePatient
	 * @return GeneticRequest
	 */
	public function setFemalePatient($femalePatient)
	{
		$this->femalePatient = $femalePatient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMalePatient()
	{
		return $this->malePatient;
	}

	/**
	 * @param mixed $malePatient
	 * @return GeneticRequest
	 */
	public function setMalePatient($malePatient)
	{
		$this->malePatient = $malePatient;
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
	 * @return GeneticRequest
	 */
	public function setReferral($referral)
	{
		$this->referral = $referral;
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
	 * @return GeneticRequest
	 */
	public function setRequestDate($requestDate)
	{
		$this->requestDate = $requestDate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getReason()
	{
		return $this->reason;
	}

	/**
	 * @param mixed $reason
	 * @return GeneticRequest
	 */
	public function setReason($reason)
	{
		$this->reason = $reason;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLab()
	{
		return $this->lab;
	}

	/**
	 * @param mixed $lab
	 * @return GeneticRequest
	 */
	public function setLab($lab)
	{
		$this->lab = $lab;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSpecimenType()
	{
		return $this->specimenType;
	}

	/**
	 * @param mixed $specimenType
	 * @return GeneticRequest
	 */
	public function setSpecimenType($specimenType)
	{
		$this->specimenType = $specimenType;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSpecimenReceiveDate()
	{
		return $this->specimenReceiveDate;
	}

	/**
	 * @param mixed $specimenReceiveDate
	 * @return GeneticRequest
	 */
	public function setSpecimenReceiveDate($specimenReceiveDate)
	{
		$this->specimenReceiveDate = $specimenReceiveDate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSpecimenReceiveBy()
	{
		return $this->specimenReceiveBy;
	}

	/**
	 * @param mixed $specimenReceiveBy
	 * @return GeneticRequest
	 */
	public function setSpecimenReceiveBy($specimenReceiveBy)
	{
		$this->specimenReceiveBy = $specimenReceiveBy;
		return $this;
	}

	function update($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT']. '/Connections/MyDBConnector.php';
		$request = $this;
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$female_patient_id = $request->getFemalePatient() ? $request->getFemalePatient()->getId() : "NULL";
			$male_patient_id = $request->getMalePatient() ? $request->getMalePatient()->getId() : "NULL";
			$referral_id = $request->getReferral() ? $request->getReferral()->getId() : "NULL";
			$request_date = $request->getRequestDate() ? quote_esc_str($request->getRequestDate()) : "NOW()";
			$reason = $request->getReason() ? quote_esc_str($request->getReason()) : "NULL";
			$genetic_lab_id = $request->getLab() ? $request->getLab()->getId() : "NULL";
			$genetic_specimen_id = $request->getSpecimenType() ? $request->getSpecimenType()->getId() : "NULL";
			$specimen_received_on = $request->getSpecimenReceiveDate() ? quote_esc_str($request->getSpecimenReceiveDate()) : "NULL";
			$specimen_received_by = $request->getSpecimenReceiveBy() ? $request->getSpecimenReceiveBy()->getId() : "NULL";
			$status = quote_esc_str($this->getStatus());

			$sql = "UPDATE genetic_lab_request SET female_patient_id=$female_patient_id, male_patient_id=$male_patient_id, referral_id=$referral_id, request_date=$request_date, reason=$reason, genetic_lab_id=$genetic_lab_id, genetic_specimen_id=$genetic_specimen_id, specimen_received_on=$specimen_received_on, specimen_received_by=$specimen_received_by, `status`=$status WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$request->setId($pdo->lastInsertId());
				return $request;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	/**
	 * @return mixed
	 */
	public function getQualityControls()
	{
		return $this->qualityControls;
	}

	/**
	 * @param mixed $qualityControls
	 * @return GeneticRequest
	 */
	public function setQualityControls($qualityControls)
	{
		$this->qualityControls = $qualityControls;
		return $this;
	}

	/**
	 * @return null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param null $id
	 * @return GeneticRequest
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param mixed $status
	 * @return GeneticRequest
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * @param mixed $result
	 * @return GeneticRequest
	 */
	public function setResult($result)
	{
		$this->result = $result;
		return $this;
	}

}