<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/26/16
 * Time: 5:55 PM
 */
class DrugRequisitionLine implements JsonSerializable
{
	private $id;
	private $requisition;
	private $drug;
	private $itemCode;
	private $quantity;
	private $batchName;
	private $expiration;

	/**
	 * DrugRequisitionLine constructor.
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
	 * @return DrugRequisitionLine
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRequisition()
	{
		return $this->requisition;
	}

	/**
	 * @param mixed $requisition
	 * @return DrugRequisitionLine
	 */
	public function setRequisition($requisition)
	{
		$this->requisition = $requisition;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDrug()
	{
		return $this->drug;
	}

	/**
	 * @param mixed $drug
	 * @return DrugRequisitionLine
	 */
	public function setDrug($drug)
	{
		$this->drug = $drug;
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
	 * @return DrugRequisitionLine
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
	 * @return DrugRequisitionLine
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBatchName()
	{
		return $this->batchName;
	}

	/**
	 * @param mixed $batchName
	 * @return DrugRequisitionLine
	 */
	public function setBatchName($batchName)
	{
		$this->batchName = $batchName;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getExpiration()
	{
		return $this->expiration;
	}

	/**
	 * @param mixed $expiration
	 * @return DrugRequisitionLine
	 */
	public function setExpiration($expiration)
	{
		$this->expiration = $expiration;
		return $this;
	}

	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$requisition = $this->getRequisition() ? $this->getRequisition()->getId() : "NULL";
		$drug = $this->getDrug() ? $this->getDrug()->getId() : "NULL";
		$itemCode = $this->getItemCode() ? quote_esc_str($this->getItemCode()):"NULL";
		$quantity = $this->getQuantity() ? $this->getQuantity() : "NULL";
		$batchName = $this->getBatchName() ? quote_esc_str($this->getBatchName()) : "NULL";
		$expirationDate = $this->getExpiration() ? quote_esc_str($this->getExpiration()) : "NULL";

		$sql = "INSERT INTO drug_requisition_line SET requisition_id=$requisition, drug_id=$drug, item_code=$itemCode, quantity=$quantity,batch_name=$batchName,expiration_date=$expirationDate";
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				return $this;
			}
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}


}