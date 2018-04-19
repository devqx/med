<?php

/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 7/28/15
 * Time: 8:10 PM
 */

class PatientAllergens implements JsonSerializable
{
	private $id;
	private $active;
	private $patient;
	private $allergen;
	private $encounter;
	private $reaction;
	private $severity;
	private $notedBy;
	private $hospital;
	private $dateNoted;
	private $category;
	private $superGeneric;

	function __construct($id = null)
	{
		$this->id = $id;
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
	 * @return PatientAllergens
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getActive()
	{
		return $this->active;
	}
	
	/**
	 * @param mixed $active
	 *
	 * @return PatientAllergens
	 */
	public function setActive($active)
	{
		$this->active = $active;
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
	 * @return PatientAllergens
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAllergen()
	{
		return $this->allergen;
	}

	/**
	 * @param mixed $allergen
	 * @return PatientAllergens
	 */
	public function setAllergen($allergen)
	{
		$this->allergen = $allergen;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getReaction()
	{
		return $this->reaction;
	}

	/**
	 * @param mixed $reaction
	 * @return PatientAllergens
	 */
	public function setReaction($reaction)
	{
		$this->reaction = $reaction;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSeverity()
	{
		return $this->severity;
	}

	/**
	 * @param mixed $severity
	 * @return PatientAllergens
	 */
	public function setSeverity($severity)
	{
		$this->severity = $severity;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getNotedBy()
	{
		return $this->notedBy;
	}

	/**
	 * @param mixed $notedBy
	 * @return PatientAllergens
	 */
	public function setNotedBy($notedBy)
	{
		$this->notedBy = $notedBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getHospital()
	{
		return $this->hospital;
	}

	/**
	 * @param mixed $hospital
	 * @return PatientAllergens
	 */
	public function setHospital($hospital)
	{
		$this->hospital = $hospital;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDateNoted()
	{
		return $this->dateNoted;
	}

	/**
	 * @param mixed $dateNoted
	 * @return PatientAllergens
	 */
	public function setDateNoted($dateNoted)
	{
		$this->dateNoted = $dateNoted;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCategory()
	{
		return $this->category;
	}

	/**
	 * @param mixed $category
	 *
*@return PatientAllergens
	 */
	public function setCategory($category)
	{
		$this->category = $category;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSuperGeneric()
	{
		return $this->superGeneric;
	}

	/**
	 * @param mixed $superGeneric
	 *
*@return PatientAllergens
	 */
	public function setSuperGeneric($superGeneric)
	{
		$this->superGeneric = $superGeneric;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getEncounter()
	{
		return $this->encounter;
	}
	
	/**
	 * @param mixed $encounter
	 *
	 * @return PatientAllergens
	 */
	public function setEncounter($encounter)
	{
		$this->encounter = $encounter;
		return $this;
	}

	/**
	 * @return mixed
	 */

	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}

	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			@session_start();
			$pdo = $pdo == NULL ? (new MyDBConnector())->getPDO() : $pdo;
			$patientId = $this->getPatient() ? $this->getPatient()->getId() : 'NULL';
			$allergen_ = $this->getAllergen() ? quote_esc_str($this->getAllergen()) : 'NULL';
			$reaction = $this->getReaction() ? quote_esc_str($this->getReaction()) : 'NULL';
			$severity = $this->getSeverity() ? quote_esc_str($this->getSeverity()) : 'NULL';
			$noteBy = $this->getNotedBy() ? $this->getNotedBy()->getId() : $_SESSION['staffID'];
			$category_id = $this->getCategory() ? $this->getCategory()->getId() : 'NULL';
			$drug_super_gen_id = $this->getSuperGeneric() ? $this->getSuperGeneric()->getId() : 'NULL';
			$encounterId = $this->getEncounter() ? $this->getEncounter()->getId() : 'NULL';
			$sql = "INSERT INTO patient_allergen (patient_ID,allergen, reaction, severity, noted_by, hospid, category_id, drug_super_gen_id, encounter_id) VALUES ($patientId, $allergen_, $reaction, $severity, $noteBy, 1, $category_id, $drug_super_gen_id, $encounterId)";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function update($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			@session_start();
			$pdo = $pdo == NULL ? (new MyDBConnector())->getPDO() : $pdo;
			//error_log('.........'.var_export($this->getActive(), true));
			//error_log('.........'.var_export(is_null($this->getActive()), true));
			//$patientId = $this->getPatient() ? $this->getPatient()->getId() : 'NULL';
			$allergen_ = $this->getAllergen() ? quote_esc_str($this->getAllergen()) : 'NULL';
			$reaction = $this->getReaction() ? quote_esc_str($this->getReaction()) : 'NULL';
			$severity = $this->getSeverity() ? quote_esc_str($this->getSeverity()) : 'NULL';
			$noteBy = $this->getNotedBy() ? $this->getNotedBy()->getId() : $_SESSION['staffID'];
			$category_id = $this->getCategory() ? $this->getCategory()->getId() : 'NULL';
			$drug_super_gen_id = $this->getSuperGeneric() ? $this->getSuperGeneric()->getId() : 'NULL';
			$encounterId = $this->getEncounter() ? $this->getEncounter()->getId() : 'NULL';
			$active = var_export($this->getActive(), true);
			$sql = "UPDATE patient_allergen SET active=$active, allergen=$allergen_, reaction=$reaction, severity=$severity, noted_by=$noteBy, hospid=1, category_id=$category_id, drug_super_gen_id=$drug_super_gen_id, encounter_id=$encounterId WHERE id={$this->getId()}";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
		
}