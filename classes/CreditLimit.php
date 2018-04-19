<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/29/14
 * Time: 3:37 PM
 */

class CreditLimit implements JsonSerializable {
  private $id;
  private $patient;
  private $amount;

  private $expiration;
  private $setBy;
  private $date;

  private $reason;

	/**
	 * CreditLimit constructor.
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }

	/**
	 * @return null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param null $id
	 * @return CreditLimit
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
	 * @return CreditLimit
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
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
	 * @return CreditLimit
	 */
	public function setAmount($amount)
	{
		$this->amount = $amount;
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
	 * @return CreditLimit
	 */
	public function setExpiration($expiration)
	{
		$this->expiration = $expiration;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSetBy()
	{
		return $this->setBy;
	}

	/**
	 * @param mixed $setBy
	 * @return CreditLimit
	 */
	public function setSetBy($setBy)
	{
		$this->setBy = $setBy;
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
	 * @return CreditLimit
	 */
	public function setReason($reason)
	{
		$this->reason = $reason;
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
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @param mixed $date
	 * @return CreditLimit
	 */
	public function setDate($date)
	{
		$this->date = $date;
		return $this;
	}

	public function update($pdo=null){
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		try {
			$pdo = $pdo == NULL ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "UPDATE `credit_limit` SET `patient_id`={$this->getPatient()->getId()},`amount`='{$this->getAmount()}',`expiration`='{$this->getExpiration()}',`reason`='".escape($this->getReason())."',`set_by`=".($this->getSetBy() ? $this->getSetBy()->getId():"NULL")." WHERE id={$this->getId()}";
			//sleep(0.01);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()>=0){
				return $this;
			}

			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
} 