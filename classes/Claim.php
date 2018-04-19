<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/4/16
 * Time: 9:29 PM
 */
class Claim implements JsonSerializable
{
	private $id;
	private $createDate;
	private $createUser;
	private $encounter;
	private $reason;
	private $patient;
	private $scheme;
	private $lines;
	private $type;
	private $status;
	private $signature;
	private $totalCharge;
	private $totalPayment;
	private $balance;
	private $state;
	private $confirmedBy;
	private $confirmedDate;
	private $dateAdmitted;
	private $dateDischarged;
	
	/**
	 * Claim constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null)
	{
		$this->id = $id;
	}
	
	function jsonSerialize()
	{
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
	 * @return Claim
	 */
	public function setId($id)
	{
		$this->id = $id;
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
	 *
	 * @return Claim
	 */
	public function setCreateDate($createDate)
	{
		$this->createDate = $createDate;
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
	 *
	 * @return Claim
	 */
	public function setCreateUser($createUser)
	{
		$this->createUser = $createUser;
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
	 * @return Claim
	 */
	public function setEncounter($encounter)
	{
		$this->encounter = $encounter;
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
	 *
	 * @return Claim
	 */
	public function setReason($reason)
	{
		$this->reason = $reason;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getLines()
	{
		return $this->lines;
	}
	
	/**
	 * @param mixed $lines
	 *
	 * @return Claim
	 */
	public function setLines($lines)
	{
		$this->lines = $lines;
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
	 * @return Claim
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}
	
	// signature id
	
	public function getSignature()
	{
		return $this->signature;
	}
	
	public function setSignature($signature)
	{
		$this->signature = $signature;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getScheme()
	{
		return $this->scheme;
	}
	
	/**
	 * @param mixed $scheme
	 *
	 * @return Claim
	 */
	public function setScheme($scheme)
	{
		$this->scheme = $scheme;
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
	 * @return Claim
	 */
	public function setType($type)
	{
		$this->type = $type;
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
	 *
	 * @return Claim
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getTotalCharge()
	{
		return $this->totalCharge;
	}
	
	/**
	 * @param mixed $totalCharge
	 *
	 * @return Claim
	 */
	public function setTotalCharge($totalCharge)
	{
		$this->totalCharge = $totalCharge;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getTotalPayment()
	{
		return $this->totalPayment;
	}
	
	/**
	 * @param mixed $totalPayment
	 *
	 * @return Claim
	 */
	public function setTotalPayment($totalPayment)
	{
		$this->totalPayment = $totalPayment;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getBalance()
	{
		return $this->balance;
	}
	
	/**
	 * @param mixed $balance
	 *
	 * @return Claim
	 */
	public function setBalance($balance)
	{
		$this->balance = $balance;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getState()
	{
		return $this->state;
	}
	
	/**
	 * @param mixed $state
	 *
	 * @return Claim
	 */
	public function setState($state)
	{
		$this->state = $state;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getConfirmedBy()
	{
		return $this->confirmedBy;
	}
	
	/**
	 * @param mixed $confirmedBy
	 *
	 * @return Claim
	 */
	public function setConfirmedBy($confirmedBy)
	{
		$this->confirmedBy = $confirmedBy;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getConfirmedDate()
	{
		return $this->confirmedDate;
	}
	
	/**
	 * @param mixed $confirmedDate
	 *
	 * @return Claim
	 */
	public function setConfirmedDate($confirmedDate)
	{
		$this->confirmedDate = $confirmedDate;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDateAdmitted()
	{
		return $this->dateAdmitted;
	}
	
	/**
	 * @param mixed $dateAdmitted
	 *
	 * @return Claim
	 */
	public function setDateAdmitted($dateAdmitted)
	{
		$this->dateAdmitted = $dateAdmitted;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDateDischarged()
	{
		return $this->dateDischarged;
	}
	
	/**
	 * @param mixed $dateDischarged
	 *
	 * @return Claim
	 */
	public function setDateDischarged($dateDischarged)
	{
		$this->dateDischarged = $dateDischarged;
		return $this;
	}
	
	
	
	
	
	
	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$_lines = [];
		foreach ($this->getLines() as $line) {
			$_lines[] = $line;
		}
		$lines = implode(",", $_lines);
		try {
			$signature = $this->getSignature() ? $this->getSignature()->getId() : 'NULL';
			$encounterId = $this->getEncounter() ? $this->getEncounter()->getId() : 'NULL';
			$reason = !is_blank($this->getReason()) ? quote_esc_str($this->getReason()) : 'NULL';
			$date_admitted = !is_blank($this->getDateAdmitted()) ? quote_esc_str($this->getDateAdmitted()) : 'NULL';
			$date_discharged = !is_blank($this->getDateDischarged()) ? quote_esc_str($this->getDateDischarged()) : 'NULL';
			
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "INSERT INTO claim (create_date, create_user_id, encounter_id, line_ids, scheme_id, patient_id,signature_id, `type`, reason, date_admitted,date_discharged) VALUES (NOW(), " . $this->getCreateUser()->getId() . ", $encounterId, '$lines', " . $this->getScheme()->getId() . ", " . $this->getPatient()->getId() . ", NULL, '" . $this->getType() . "', $reason,$date_admitted,$date_discharged)";
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
		$_lines = [];
		foreach ($this->getLines() as $line) {
			$_lines[] = $line;
		}
		$lines = quote_esc_str(implode(",", $_lines));
		$encounterId = $this->getEncounter() ? $this->getEncounter()->getId() : 'NULL';
		$reason = !is_blank($this->getReason()) ? quote_esc_str($this->getReason()) : 'NULL';
		
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "UPDATE claim SET  create_user_id={$this->getCreateUser()->getId()}, encounter_id={$encounterId}, scheme_id={$this->getScheme()->getId()}, patient_id={$this->getPatient()->getId()}, total_charge=". $this->getTotalCharge() .", total_payment=". $this->getTotalPayment() .", balance=". $this->getBalance() .", `type`='{$this->getType()}', reason=$reason WHERE id={$this->getId()}";
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
	
	
	function updateCharge($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	
		$encounterId = $this->getEncounter() ? $this->getEncounter()->getId() : 'NULL';
		
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "UPDATE claim SET encounter_id={$encounterId}, total_charge=". $this->getTotalCharge() .",  balance=". $this->getBalance() ." WHERE id={$this->getId()}";
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
	
	function validateClaim($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$confirmed_by = $this->getConfirmedBy() ? $this->getConfirmedBy()->getId() : 'NULL';
		$confirmed_date = $this->getConfirmedDate() ? $this->getConfirmedDate() : 'NULL';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "UPDATE claim SET _state='". $this->getState() ."', confirmed_by='". $confirmed_by ."', confirmed_date='". $confirmed_date ."' WHERE id={$this->getId()}";
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
	
	function unlinkEncounter($pid, $encounter_id, $pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "UPDATE  encounter  SET  claimed = 0 WHERE patient_id='" . $pid . "' AND id='" . $encounter_id . "'";
			error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
		
	}
	
	function unlinkLines($pid, $item_id, $pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "UPDATE  bills SET claimed = 0 WHERE patient_id='" . $pid . "' AND  bill_id='" . $item_id . "' ";
			error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
		
	}
	
	
	function updateLineEncounter($pid, $encounter_id, $pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "UPDATE  encounter SET claimed = 1 WHERE patient_id='" . $pid . "' AND  id='" . $encounter_id . "' ";
			error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
		
	}
	
	function updateBillLine($pid, $item_id, $pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "UPDATE  bills SET claimed = 1 WHERE patient_id='" . $pid . "' AND  bill_id='" . $item_id . "' ";
			error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
		
	}
	
	
}