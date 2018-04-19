<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/13/16
 * Time: 10:17 AM
 */
class PackageTokenUsage implements JsonSerializable
{
	private $id;
	private $patient;
	private $itemCode;
	private $quantity;
	private $usedDate;
	private $responsible;
	
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
	 * @return PackageTokenUsage
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
	 * @return PackageTokenUsage
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
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
	 * @return PackageTokenUsage
	 */
	public function setItemCode($itemCode)
	{
		$this->itemCode = $itemCode;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}
	
	/**
	 * @param mixed $quantity
	 *
	 * @return PackageTokenUsage
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getUsedDate()
	{
		return $this->usedDate;
	}
	
	/**
	 * @param mixed $usedDate
	 *
	 * @return PackageTokenUsage
	 */
	public function setUsedDate($usedDate)
	{
		$this->usedDate = $usedDate;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getResponsible()
	{
		return $this->responsible;
	}
	
	/**
	 * @param mixed $responsible
	 *
	 * @return PackageTokenUsage
	 */
	public function setResponsible($responsible)
	{
		$this->responsible = $responsible;
		return $this;
	}
	
	/**
	 * PackageTokenUsage constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}
	
	function add($pdo=null){
		@session_start();
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$itemCode = !is_blank($this->getItemCode()) ? quote_esc_str($this->getItemCode()) : 'NULL';
			$patientId = $this->getPatient() ? $this->getPatient()->getId() : 'NULL';
			$qty = $this->getQuantity() ? $this->getQuantity() : 0;
			$use_date = $this->getUsedDate() ? quote_esc_str($this->getUsedDate()) : 'NOW()';
			$responsible = $this->getResponsible() ? $this->getResponsible()->getId() : $_SESSION['staffID'];
			
			$sql = "INSERT INTO package_token_usage SET patient_id=$patientId, item_code=$itemCode, quantity=$qty, use_date=$use_date, responsible_id=$responsible";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $exception) {
			errorLog($exception);
			return null;
		}
	}
	function update($pdo=null){
		@session_start();
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$itemCode = !is_blank($this->getItemCode()) ? quote_esc_str($this->getItemCode()) : 'NULL';
			$patientId = $this->getPatient() ? $this->getPatient()->getId() : 'NULL';
			$qty = $this->getQuantity() ? $this->getQuantity() : 0;
			$use_date = $this->getUsedDate() ? quote_esc_str($this->getUsedDate()) : 'NOW()';
			$responsible = $this->getResponsible() ? $this->getResponsible()->getId() : $_SESSION['staffID'];
			
			$sql = "UPDATE package_token_usage SET patient_id=$patientId, item_code=$itemCode, quantity=$qty, use_date=$use_date, responsible_id=$responsible WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $exception) {
			errorLog($exception);
			return null;
		}
	}
}