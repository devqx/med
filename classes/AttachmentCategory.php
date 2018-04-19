<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/21/16
 * Time: 5:26 PM
 */
class AttachmentCategory implements JsonSerializable
{
	private $id;
	private $name;
	private $roles;
	private $rolesFull;
	
	/**
	 * AttachmentCategory constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null)
	{
		$this->id = $id;
	}
	
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
	 * @return AttachmentCategory
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
	 *
	 * @return AttachmentCategory
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getRoles()
	{
		return $this->roles;
	}
	
	/**
	 * @param mixed $roles
	 *
	 * @return AttachmentCategory
	 */
	public function setRoles($roles)
	{
		$this->roles = $roles;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getRolesFull()
	{
		return $this->rolesFull;
	}
	
	/**
	 * @param mixed $rolesFull
	 *
	 * @return AttachmentCategory
	 */
	public function setRolesFull($rolesFull)
	{
		$this->rolesFull = $rolesFull;
		return $this;
	}
	
	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
			$name = quote_esc_str($this->getName());
			$roleIds = count(@array_filter($this->getRoles()))>0 ? quote_esc_str( implode(",", $this->getRoles()) ) : 'null';
			$sql = "INSERT INTO attachment_category SET `name` = $name, role_ids = $roleIds";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if($stmt->rowCount() == 1){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function update($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
			$name = quote_esc_str($this->getName());
			$roleIds = count(@array_filter($this->getRoles()))>0 ? quote_esc_str( implode(",", $this->getRoles()) ) : 'null';
			
			$sql = "UPDATE attachment_category SET `name` = $name, role_ids = $roleIds WHERE id = {$this->getId()}";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if($stmt->rowCount() >= 0){
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
}