<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/5/16
 * Time: 5:05 PM
 */
class PatientMedicalReportNote implements JsonSerializable
{
	private $id;
	private $patientMedicalReport;
	private $note;
	private $createUser;
	private $createDate;

	/**
	 * PatientMedicalReportNote constructor.
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }

	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
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
	 * @return PatientMedicalReportNote
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPatientMedicalReport()
	{
		return $this->patientMedicalReport;
	}

	/**
	 * @param mixed $patientMedicalReport
	 * @return PatientMedicalReportNote
	 */
	public function setPatientMedicalReport($patientMedicalReport)
	{
		$this->patientMedicalReport = $patientMedicalReport;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getNote()
	{
		return $this->note;
	}

	/**
	 * @param mixed $note
	 * @return PatientMedicalReportNote
	 */
	public function setNote($note)
	{
		$this->note = $note;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCreateUser()
	{
		return $this->createUser;
	}

	/**
	 * @param mixed $createUser
	 * @return PatientMedicalReportNote
	 */
	public function setCreateUser($createUser)
	{
		$this->createUser = $createUser;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCreateDate()
	{
		return $this->createDate;
	}

	/**
	 * @param mixed $createDate
	 * @return PatientMedicalReportNote
	 */
	public function setCreateDate($createDate)
	{
		$this->createDate = $createDate;
		return $this;
	}

	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

		$patientMedicalReportId = $this->getPatientMedicalReport()->getId();
		$note = !is_blank($this->getNote())? quote_esc_str($this->getNote()): "NULL";
		$create_uid = $this->getCreateUser() ? $this->getCreateUser()->getId() : $_SESSION['staffID'];
		$create_date = $this->getCreateDate() ? "'".$this->getCreateDate()."'": "NOW()";
		try {
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO(): $pdo;
			$sql = "INSERT INTO patient_medical_report_note (patient_medical_report_id, note, create_uid, create_date) VALUES ($patientMedicalReportId, $note, $create_uid, $create_date)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}

	function update($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

		$patientMedicalReportId = $this->getPatientMedicalReport()->getId();
		$note = !is_blank($this->getNote())? quote_esc_str($this->getNote()): "NULL";
		$create_uid = $this->getCreateUser() ? $this->getCreateUser()->getId() : $_SESSION['staffID'];
		$create_date = $this->getCreateDate() ? "'".$this->getCreateDate()."'": "NOW()";
		try {
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO(): $pdo;
			$sql = "UPDATE patient_medical_report_note SET patient_medical_report_id=$patientMedicalReportId, note=$note, create_uid=$create_uid, create_date=$create_date WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				return $this;
			}
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
}