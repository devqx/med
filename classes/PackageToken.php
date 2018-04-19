<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/13/16
 * Time: 9:48 AM
 */
class PackageToken implements JsonSerializable
{
	private $id;
	private $patient;
	private $itemCode;
	private $originalQuantity;
	private $remainingQuantity;
	
	/**
	 * PackageTokens constructor.
	 *
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
	 *
	 * @return PackageToken
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
	 * @return PackageToken
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
	 * @return PackageToken
	 */
	public function setItemCode($itemCode)
	{
		$this->itemCode = $itemCode;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getOriginalQuantity()
	{
		return $this->originalQuantity;
	}
	
	/**
	 * @param mixed $originalQuantity
	 *
	 * @return PackageToken
	 */
	public function setOriginalQuantity($originalQuantity)
	{
		$this->originalQuantity = $originalQuantity;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getRemainingQuantity()
	{
		return $this->remainingQuantity;
	}
	
	/**
	 * @param mixed $remainingQuantity
	 *
	 * @return PackageToken
	 */
	public function setRemainingQuantity($remainingQuantity)
	{
		$this->remainingQuantity = $remainingQuantity;
		return $this;
	}
	
	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$itemCode = !is_blank($this->getItemCode()) ? quote_esc_str($this->getItemCode()) : 'NULL';
			$patientId = $this->getPatient() ? $this->getPatient()->getId() : 'NULL';
			$originalQty = $this->getOriginalQuantity() ? $this->getOriginalQuantity() : 0;
			$quantityRemaining = $this->getRemainingQuantity() ? $this->getRemainingQuantity() : 0;
			
			$sql = "INSERT INTO package_token SET item_code=$itemCode, patient_id=$patientId, original_quantity=$originalQty, quantity_left=$quantityRemaining";
			//error_log($sql);
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
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$itemCode = !is_blank($this->getItemCode()) ? quote_esc_str($this->getItemCode()) : 'NULL';
			$patientId = $this->getPatient() ? $this->getPatient()->getId() : 'NULL';
			$originalQty = $this->getOriginalQuantity() ? $this->getOriginalQuantity() : 0;
			$quantityRemaining = $this->getRemainingQuantity() ? $this->getRemainingQuantity() : 0;
			
			$sql = "UPDATE package_token SET item_code=$itemCode, patient_id=$patientId, original_quantity=$originalQty, quantity_left=$quantityRemaining WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return $this;
			}
			return null;
		} catch (PDOException $exception) {
			errorLog($exception);
			return null;
		}
	}
}