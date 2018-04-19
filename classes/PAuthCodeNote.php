<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/21/16
 * Time: 12:41 PM
 */
class PAuthCodeNote implements JsonSerializable
{
	private $id;
	private $pauthCode;
	private $note;
	private $user;
	private $time;
	
	/**
	 * PAuthCodeNote constructor.
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
	 * @return PAuthCodeNote
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getPauthCode()
	{
		return $this->pauthCode;
	}
	
	/**
	 * @param mixed $pauthCode
	 *
	 * @return PAuthCodeNote
	 */
	public function setPauthCode($pauthCode)
	{
		$this->pauthCode = $pauthCode;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getNote()
	{
		return $this->note;
	}
	
	/**
	 * @param mixed $note
	 *
	 * @return PAuthCodeNote
	 */
	public function setNote($note)
	{
		$this->note = $note;
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
	 * @return PAuthCodeNote
	 */
	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getTime()
	{
		return $this->time;
	}
	
	/**
	 * @param mixed $time
	 *
	 * @return PAuthCodeNote
	 */
	public function setTime($time)
	{
		$this->time = $time;
		return $this;
	}
	
	function add($pdo=null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO():$pdo;
			
			$authorizationCode = $this->getPauthCode() ? $this->getPauthCode()->getId() : 'null';
			$note = !is_blank($this->getNote()) ? quote_esc_str($this->getNote()) : 'null';
			$createTime = !is_blank($this->getTime()) ? quote_esc_str($this->getTime()) : 'NOW()';
			$createUser = $this->getUser() ? $this->getUser()->getId() : $_SESSION['staffID'];
			
			$sql = "INSERT INTO authorization_code_note (authorization_code_id, note, create_time, create_user) VALUES ($authorizationCode, $note, $createTime, $createUser)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() >= 0){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		}catch (PDOException $e)
		{
			errorLog($e);
			return null;
		}
	}
	
}