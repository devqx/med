<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PaymentMethod
 *
 * @author pauldic
 */
class PaymentMethod implements JsonSerializable
{
	private $id;
	private $name;
	private $type;
	private $ledgerId;
	private $clinic;

	/**
	 * PaymentMethod constructor.
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
	 * @return PaymentMethod
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param mixed $name
	 * @return PaymentMethod
	 */
	public function setName($name)
	{
		$this->name = $name;
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
	 * @return PaymentMethod
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLedgerId()
	{
		return $this->ledgerId;
	}

	/**
	 * @param mixed $ledgerId
	 * @return PaymentMethod
	 */
	public function setLedgerId($ledgerId)
	{
		$this->ledgerId = $ledgerId;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getClinic()
	{
		return $this->clinic;
	}

	/**
	 * @param mixed $clinic
	 * @return PaymentMethod
	 */
	public function setClinic($clinic)
	{
		$this->clinic = $clinic;
		return $this;
	}

	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}

	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$ledgerId = $this->getLedgerId() ? quote_esc_str($this->getLedgerId()) : "NULL";
			$name = !is_blank($this->getName()) ? quote_esc_str($this->getName()) : "NULL";
			$type = !is_blank($this->getType()) ? quote_esc_str($this->getType()) : "NULL";

			$sql = "INSERT INTO payment_methods SET ledger_id=$ledgerId, `name` = $name, type = $type, hospid=1";//quirk to quickly not bother about setting clinic
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				$stmt = null;
				$this->setId($pdo->lastInsertId());
				$this->setClinic(new Clinic(1));
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
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$ledgerId = $this->getLedgerId() ? quote_esc_str($this->getLedgerId()) : "NULL";
			$name = !is_blank($this->getName()) ? quote_esc_str($this->getName()) : "NULL";
			$type = !is_blank($this->getType()) ? quote_esc_str($this->getType()) : "NULL";

			$sql = "UPDATE payment_methods SET `name` = $name, type = $type, ledger_id=$ledgerId WHERE id=" . $this->getId();
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

}
