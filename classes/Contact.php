<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/20/16
 * Time: 12:00 PM
 */
class Contact implements JsonSerializable
{
	private $id;
	private $patient;
	private $country;
	private $phone;
	private $type;
	private $primary;
	private $relation;
	
	/**
	 * Contact constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}
	
	function __toString()
	{
		// Implement __toString() method.
		return '('.$this->getCountry()->dialing_code.')'.$this->getPhone(). ($this->getPrimary() ? ' (Primary)':'');
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
	 * @return Contact
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
	 * @return Contact
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCountry()
	{
		return $this->country;
	}
	
	/**
	 * @param mixed $country
	 *
	 * @return Contact
	 */
	public function setCountry($country)
	{
		$this->country = $country;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getPhone()
	{
		return $this->phone;
	}
	
	/**
	 * @param mixed $phone
	 *
	 * @return Contact
	 */
	public function setPhone($phone)
	{
		$this->phone = $phone;
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
	 * @return Contact
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getPrimary()
	{
		return $this->primary;
	}
	
	/**
	 * @param mixed $primary
	 *
	 * @return Contact
	 */
	public function setPrimary($primary)
	{
		$this->primary = $primary;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getRelation()
	{
		return $this->relation;
	}
	
	/**
	 * @param mixed $relation
	 *
	 * @return Contact
	 */
	public function setRelation($relation)
	{
		$this->relation = $relation;
		return $this;
	}
	
	function add($pdo=null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try
		{
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$patientId = $this->getPatient() ? $this->getPatient()->getId() : 'null';
			$country = $this->getCountry() ? $this->getCountry()->id : 'null';
			$phone = !is_blank($this->getPhone()) ? quote_esc_str($this->getPhone()) : 'null';
			$type = !is_blank($this->getType()) ? quote_esc_str($this->getType()) : 'null';
			$primary = $this->getPrimary() ? var_export($this->getPrimary(), true) : var_export(false, true);
			$relation = !is_blank($this->getRelation()) ? quote_esc_str($this->getRelation()) : quote_esc_str('self');
			$sql = "INSERT INTO contact (patient_id, country_id, phone, type, `primary`, relation) VALUES ($patientId, $country, $phone, $type, $primary, $relation)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e)
		{
			errorLog($e);
			return null;
		}
	}
	
	function update($pdo=null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try
		{
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$patientId = $this->getPatient() ? $this->getPatient()->getId() : 'null';
			$country = $this->getCountry() ? $this->getCountry()->id : 'null';
			$phone = !is_blank($this->getPhone()) ? quote_esc_str($this->getPhone()) : 'null';
			$type = !is_blank($this->getType()) ? quote_esc_str($this->getType()) : 'null';
			$primary = $this->getPrimary() ? var_export($this->getPrimary(), true) : var_export(false, true);
			$sql = "UPDATE contact SET patient_id=$patientId, country_id=$country, phone=$phone, type=$type, `primary`=$primary WHERE id = {$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() >= 0){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e)
		{
			errorLog($e);
			return null;
		}
	}
}