<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/7/16
 * Time: 11:32 AM
 */
class Signature implements JsonSerializable
{
	private $id;
	private $patient;
	private $date;
	private $active;
	private $blob;
	
	/**
	 * Signature constructor.
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
	 * @return Signature
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
	 * @return Signature
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
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
	 *
	 * @return Signature
	 */
	public function setDate($date)
	{
		$this->date = $date;
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
	 * @return Signature
	 */
	public function setActive($active)
	{
		$this->active = $active;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getBlob()
	{
		return $this->blob;
	}
	
	/**
	 * @param mixed $blob
	 *
	 * @return Signature
	 */
	public function setBlob($blob)
	{
		$this->blob = $blob;
		return $this;
	}
	
	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$patient = $this->getPatient() ? $this->getPatient()->getId() : 'NULL';
			$dateAdded = !is_blank($this->getDate()) ? quote_esc_str($this->getDate()) : 'NOW()';
			$blob = !is_blank($this->getBlob()) ? ($this->getBlob()) : 'null';
			//(! get_magic_quotes_gpc ()) ? addslashes ($refinedBlob) : $refinedBlob
			$active = $this->getActive() ? var_export($this->getActive(), true) : 'TRUE';
			
			$sql = "INSERT INTO signature (patient_id, date_added, signature, active) VALUES (?, $dateAdded, ?, $active) ON DUPLICATE KEY UPDATE signature = ?, `active` = TRUE";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->bindParam(1, $patient, PDO::PARAM_INT);
			$stmt->bindValue(2, bin2hex($blob), PDO::PARAM_LOB);
			$stmt->bindValue(3, bin2hex($blob), PDO::PARAM_LOB);
			//error_log($sql);
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
	
	function update($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$patient = $this->getPatient() ? $this->getPatient()->getId() : 'NULL';
			$dateAdded = !is_blank($this->getDate()) ? quote_esc_str($this->getDate()) : 'NOW()';
			$blob = !is_blank($this->getBlob()) ? ($this->getBlob()) : 'null';
			$active = var_export($this->getActive(), true);
			
			$sql = "UPDATE signature SET patient_id=?, date_added=$dateAdded, active=$active WHERE id={$this->getId()}";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->bindParam(1, $patient, PDO::PARAM_INT);
			//$stmt->bindValue(2, bin2hex($blob), PDO::PARAM_LOB);
			//error_log($sql);
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
	
	public function MarkUsedSignature($id, $pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			
			$sql = "UPDATE signature SET active=0 WHERE id=$id";
			
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
