<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/17/16
 * Time: 12:34 PM
 */
class ReagentUsed implements JsonSerializable
{
	private $id;
	private $reagent;
	private $request;
	private $lotNumber;
	private $user;
	private $date;
	
	/**
	 * ReagentUsed constructor.
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
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * @param mixed $id
	 *
	 * @return ReagentUsed
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getReagent()
	{
		return $this->reagent;
	}
	
	/**
	 * @param mixed $reagent
	 *
	 * @return ReagentUsed
	 */
	public function setReagent($reagent)
	{
		$this->reagent = $reagent;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getRequest()
	{
		return $this->request;
	}
	
	/**
	 * @param mixed $request
	 *
	 * @return ReagentUsed
	 */
	public function setRequest($request)
	{
		$this->request = $request;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getLotNumber()
	{
		return $this->lotNumber;
	}
	
	/**
	 * @param mixed $lotNumber
	 *
	 * @return ReagentUsed
	 */
	public function setLotNumber($lotNumber)
	{
		$this->lotNumber = $lotNumber;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getUser()
	{
		return $this->user;
	}
	
	/**
	 * @param mixed $user
	 *
	 * @return ReagentUsed
	 */
	public function setUser($user)
	{
		$this->user = $user;
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
	 * @return ReagentUsed
	 */
	public function setDate($date)
	{
		$this->date = $date;
		return $this;
	}
	
	public function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/Reagent.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/GeneticRequest.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticRequestDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO(): $pdo;
			$request = $this->getRequest() ? $this->getRequest()->getId() : "NULL";
			$reagent = $this->getReagent() ? $this->getReagent()->getId() : "NULL";
			$lotNumber = !is_blank($this->getLotNumber()) ? quote_esc_str($this->getLotNumber()) : "NULL";
			$date = $this->getDate() ? quote_esc_str($this->getDate()) : "NOW()";
			$user = $this->getUser() ? $this->getUser()->getId() : $_SESSION['staffID'];
			
			$sql = "INSERT INTO genetic_request_reagent (request_id, reagent_id, lot_number, date_used, user_id) VALUES ($request, $reagent, $lotNumber, $date, $user)";
			//error_log($sql  );
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
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