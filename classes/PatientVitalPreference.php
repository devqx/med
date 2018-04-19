<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/26/17
 * Time: 2:44 PM
 */
class PatientVitalPreference implements JsonSerializable
{
	private $id;
	private $patient;
	private $type;
	
	/**
	 * PatientVitalPreference constructor.
	 *
	 * @param $id
	 * @param $type
	 */
	public function __construct($id=null, $type = null)
	{
		$this->id = $id;
		$this->type = $type;
	}
	
	/**
	 * PatientVitalPreference constructor.
	 *
	 * @param $id
	 */
	
	
	/**
	 * @return null
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * @param null $id
	 *
	 * @return PatientVitalPreference
	 */
	public function setId($id)
	{
		$this->id = $id;
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
	 *
	 * @return PatientVitalPreference
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * @param mixed $type
	 *
	 * @return PatientVitalPreference
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
	
	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}
	
	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$patientId = $this->getPatient()->getId();
			$type = quote_esc_str($this->getType());
			$sql = "INSERT IGNORE INTO patient_vital_preference (patient_id, type) VALUES ($patientId, $type)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()==1){
				return $this->setId($pdo->lastInsertId());
			} else if($stmt->rowCount()==0) {
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function delete($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$patientId = $this->getPatient()->getId();
			$type = quote_esc_str($this->getType());
			$sql = "DELETE FROM patient_vital_preference WHERE patient_id=$patientId AND type=$type";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()==1){
				return true;
			} else if($stmt->rowCount()==0) {
				return false;
			}
			return false;
		}catch (PDOException $e){
			errorLog($e);
			return false;
		}
	}
	
}