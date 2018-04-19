<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/6/17
 * Time: 10:54 AM
 */

class EmbryoAssessment implements JsonSerializable
{
	private $id;
	private $instance;
	private $createDate;
	private $createUser;
	private $day;
	private $comment;
	private $witnesses;
	private $data;
	
	/**
	 * EmbryoAssessment constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
	
	public function jsonSerialize()
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
	 * @return EmbryoAssessment
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getInstance()
	{
		return $this->instance;
	}
	
	/**
	 * @param mixed $instance
	 *
	 * @return EmbryoAssessment
	 */
	public function setInstance($instance)
	{
		$this->instance = $instance;
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
	 * @return EmbryoAssessment
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
	 * @return EmbryoAssessment
	 */
	public function setCreateUser($createUser)
	{
		$this->createUser = $createUser;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDay()
	{
		return $this->day;
	}
	
	/**
	 * @param mixed $day
	 *
	 * @return EmbryoAssessment
	 */
	public function setDay($day)
	{
		$this->day = $day;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getComment()
	{
		return $this->comment;
	}
	
	/**
	 * @param mixed $comment
	 *
	 * @return EmbryoAssessment
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getWitnesses()
	{
		return $this->witnesses;
	}
	
	/**
	 * @param mixed $witnesses
	 *
	 * @return EmbryoAssessment
	 */
	public function setWitnesses($witnesses)
	{
		$this->witnesses = $witnesses;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getData()
	{
		return $this->data;
	}
	
	/**
	 * @param mixed $data
	 *
	 * @return EmbryoAssessment
	 */
	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}
	
	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			
			$canCommit = !$pdo->inTransaction();
			try {
				$pdo->beginTransaction();
			} catch (PDOException $e) {
			}
			
			$instance = $this->getInstance()->getId();
			$createDate = $this->getCreateDate() ? quote_esc_str($this->getCreateDate()) : 'NOW()';
			$createUser = $this->getCreateUser() ? $this->getCreateUser()->getId() : $_SESSION['staffID'];
			$day = $this->getDay();
			$comment = !is_blank($this->getComment()) ? quote_esc_str($this->getComment()) : 'NULL';
			$witnesses = [];
			
			foreach ($this->getWitnesses() as $witness) {
				$witnesses[] = (int)$witness->getId();
			}
			
			$_witnesses = count($witnesses) > 0 ? quote_esc_str(implode(",", $witnesses)) : "NULL";
			
			$sql = "INSERT INTO ivf_embryo_assessment (instance_id, create_date, create_user_id, `day`, `comment`, witness_ids) VALUES ($instance, $createDate, $createUser, $day, $comment, $_witnesses)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				foreach ($this->getData() as $data) {
					$data->setAssessment($this);
					//$data = new EmbryoAssessmentData();
					if ($data->add($pdo) == null) {
						if ($canCommit) {
							$pdo->rollBack();
						}
						return null;
					}
				}
				if ($canCommit) {
					$pdo->commit();
				}
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}