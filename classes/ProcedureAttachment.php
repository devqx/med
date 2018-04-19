<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/26/16
 * Time: 6:58 AM
 */
class ProcedureAttachment implements JsonSerializable
{
	private $id;
	private $patientProcedure;
	private $url;
	private $mimeType;
	private $description;
	private $uploadDate;
	private $uploadBy;
	
	/**
	 * ProcedureAttachment constructor.
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
	 * @return ProcedureAttachment
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getPatientProcedure()
	{
		return $this->patientProcedure;
	}
	
	/**
	 * @param mixed $patientProcedure
	 *
	 * @return ProcedureAttachment
	 */
	public function setPatientProcedure($patientProcedure)
	{
		$this->patientProcedure = $patientProcedure;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getUrl()
	{
		return $this->url;
	}
	
	/**
	 * @param mixed $url
	 *
	 * @return ProcedureAttachment
	 */
	public function setUrl($url)
	{
		$this->url = $url;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getMimeType()
	{
		return $this->mimeType;
	}
	
	/**
	 * @param mixed $mimeType
	 *
	 * @return ProcedureAttachment
	 */
	public function setMimeType($mimeType)
	{
		$this->mimeType = $mimeType;
		return $this;
	}
	
	
	/**
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->description;
	}
	
	/**
	 * @param mixed $description
	 *
	 * @return ProcedureAttachment
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}
	/**
	 * @return mixed
	 */
	public function getUploadDate()
	{
		return $this->uploadDate;
	}
	
	/**
	 * @param mixed $uploadDate
	 *
	 * @return ProcedureAttachment
	 */
	public function setUploadDate($uploadDate)
	{
		$this->uploadDate = $uploadDate;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getUploadBy()
	{
		return $this->uploadBy;
	}
	
	/**
	 * @param mixed $uploadBy
	 *
	 * @return ProcedureAttachment
	 */
	public function setUploadBy($uploadBy)
	{
		$this->uploadBy = $uploadBy;
		return $this;
	}
	
	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			
			$procedure = $this->getPatientProcedure() ? $this->getPatientProcedure()->getId() : 'NULL';
			$uploadDate = !is_blank($this->getUploadDate()) ? quote_esc_str($this->getUploadDate()) : 'NOW()';
			$uploadBy = $this->getUploadBy() ? $this->getUploadBy()->getId() : $_SESSION['staffId'];
			$url = !is_blank($this->getUrl()) ? quote_esc_str($this->getUrl()) : 'NULL';
			$mimeType = !is_blank($this->getMimeType()) ? quote_esc_str($this->getMimeType()) : 'NULL';
			$description = !is_blank($this->getDescription()) ? quote_esc_str($this->getDescription()) : 'NULL';
			
			$sql = "INSERT INTO procedure_attachment (patient_procedure_id, time_entered, entered_by, description, url, mimetype) VALUES ($procedure, $uploadDate, $uploadBy, $description, $url, $mimeType)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
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