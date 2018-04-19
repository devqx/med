<?php

/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 12/10/15
 * Time: 4:19 PM
 */
Class PatientAntenatalUsages implements JsonSerializable
{
	private $id;
	private $antenatal;
	private $patient;
	private $item;
	private $itemCode;
	private $type;
	private $usages;
	private $dateUsed;
	
	/**
	 * PatientAntenatalUsages constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null)
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
	 *
	 * @return PatientAntenatalUsages
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAntenatal()
	{
		return $this->antenatal;
	}
	
	/**
	 * @param mixed $antenatal
	 *
	 * @return PatientAntenatalUsages
	 */
	public function setAntenatal($antenatal)
	{
		$this->antenatal = $antenatal;
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
	 * @return PatientAntenatalUsages
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getItem()
	{
		return $this->item;
	}
	
	/**
	 * @param mixed $item
	 *
	 * @return PatientAntenatalUsages
	 */
	public function setItem($item)
	{
		$this->item = $item;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getItemCode()
	{
		return $this->itemCode;
	}
	
	/**
	 * @param mixed $itemCode
	 *
	 * @return PatientAntenatalUsages
	 */
	public function setItemCode($itemCode)
	{
		$this->itemCode = $itemCode;
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
	 * @return PatientAntenatalUsages
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getUsages()
	{
		return $this->usages;
	}
	
	/**
	 * @param mixed $usages
	 *
	 * @return PatientAntenatalUsages
	 */
	public function setUsages($usages)
	{
		$this->usages = $usages;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDateUsed()
	{
		return $this->dateUsed;
	}
	
	/**
	 * @param mixed $dateUsed
	 *
	 * @return PatientAntenatalUsages
	 */
	public function setDateUsed($dateUsed)
	{
		$this->dateUsed = $dateUsed;
		return $this;
	}
	
	
	
	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		$instanceID = $this->getAntenatal() ? $this->getAntenatal()->getId() : 'NULL';
		$patientId = $this->getPatient() ? $this->getPatient()->getId() : 'NULL';
		$itemId = $this->getItem();
		$itemCode = quote_esc_str($this->getItemCode());
		$type = quote_esc_str($this->getType());
		$usageCount = $this->getUsages();
		$date = $this->getDateUsed() ? quote_esc_str($this->getDateUsed()) : 'NOW()';
		
		try {
			$sql = "INSERT INTO patient_antenatal_usages SET aid=$instanceID, patient_id=$patientId, item_id=$itemId, item_code=$itemCode, item_type=$type, usages=$usageCount, date_used=$date";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}