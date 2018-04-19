<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/21/18
 * Time: 11:58 AM
 */

class ClaimLines implements JsonSerializable
{
	
	private $id;
	private $billLine;
	private $claim;
	private $amount;
	private $represented;
	
	/**
	 * ClaimLines constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
	
	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * @param mixed $id
	 *
	 * @return ClaimLines
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getBillLine()
	{
		return $this->billLine;
	}
	
	/**
	 * @param mixed $billLine
	 *
	 * @return ClaimLines
	 */
	public function setBillLine($billLine)
	{
		$this->billLine = $billLine;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getClaim()
	{
		return $this->claim;
	}
	
	/**
	 * @param mixed $claim
	 *
	 * @return ClaimLines
	 */
	public function setClaim($claim)
	{
		$this->claim = $claim;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAmount()
	{
		return $this->amount;
	}
	
	/**
	 * @param mixed $amount
	 *
	 * @return ClaimLines
	 */
	public function setAmount($amount)
	{
		$this->amount = $amount;
		return $this;
	}

	
	public function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}
	
	/**
	 * @return mixed
	 */
	public function getRepresented()
	{
		return $this->represented;
	}
	
	/**
	 * @param mixed $represented
	 *
	 * @return ClaimLines
	 */
	public function setRepresented($represented)
	{ //
		$this->represented = $represented;
		return $this;
	}
	
	
	
	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		
		try {
			 $claimId = $this->getClaim() ? $this->getClaim()->getId() : "NULL";
			 $lineId = $this->getBillLine() ? $this->getBillLine()->getId() : "NULL";
			 $amount = $this->getAmount() ? $this->getAmount() : '0';
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "INSERT INTO claim_bill_lines (bill_line_id,claim_id, amount) VALUES ($lineId,$claimId,$amount)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
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
	
		$claimId = $this->getClaim() ? $this->getClaim()->getId() : "NULL";
		$lineId = $this->getBillLine() ? $this->getBillLine()->getId() : "NULL";
		$amount = $this->getAmount() ? $this->getAmount() : '0';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "UPDATE claim_bill_lines SET claim_id=$claimId, bill_line_id=$lineId, amount=$amount WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
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