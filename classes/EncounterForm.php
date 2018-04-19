<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/16/17
 * Time: 10:17 AM
 */
class EncounterForm implements JsonSerializable
{
	private $id;
	private $encounter;
	private $form;
	private $dateAdded;
	private $createUser;
	
	/**
	 * EncounterForm constructor.
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
	 * @return EncounterForm
	 */
	public function setId($id)
	{
		$this->id = $id;
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
	 * @return EncounterForm
	 */
	public function setEncounter($encounter)
	{
		$this->encounter = $encounter;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getForm()
	{
		return $this->form;
	}
	
	/**
	 * @param mixed $form
	 *
	 * @return EncounterForm
	 */
	public function setForm($form)
	{
		$this->form = $form;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDateAdded()
	{
		return $this->dateAdded;
	}
	
	/**
	 * @param mixed $dateAdded
	 *
	 * @return EncounterForm
	 */
	public function setDateAdded($dateAdded)
	{
		$this->dateAdded = $dateAdded;
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
	 * @return EncounterForm
	 */
	public function setCreateUser($createUser)
	{
		$this->createUser = $createUser;
		return $this;
	}
	
	function add($pdo = null)
	{
		@session_start();
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$encounterId = $this->getEncounter() ? $this->getEncounter()->getId() : 'NULL';
		$formId = $this->getForm() ? $this->getForm()->getId() : 'NULL';
		$timeAdded = !is_blank($this->getDateAdded()) ? quote_esc_str($this->getDateAdded()) : 'NOW()';
		$userId = $this->getCreateUser() ? $this->getCreateUser()->getId() : $_SESSION['staffID'];
		
		try {
			$pdo = $pdo === null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "INSERT IGNORE INTO encounter_form SET encounter_id=$encounterId, form_id=$formId, time_added=$timeAdded, user_id=$userId";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
}