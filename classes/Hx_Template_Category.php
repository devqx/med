<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/6/18
 * Time: 5:40 PM
 */

class Hx_Template_Category implements JsonSerializable
{
	
	private $id;
	private $name;
	
	/**
	 * Hx_Template_Category constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) {
		$this->id = $id;
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
	 * @return Hx_Template_Category
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
	 * @return Hx_Template_Category
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	public function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}
	
	public function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$name = quote_esc_str($this->getName());
		try {
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO(): $pdo;
			$sql = "INSERT INTO hx_template_category (`name`) VALUES ($name)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	
	function update($pdo=NULL){
		require_once $_SERVER['DOCUMENT_ROOT'].'/Connections/MyDBConnector.php';
		$name = quote_esc_str($this->getName());
		try {
			$pdo = $pdo==NULL?(new MyDBConnector())->getPDO():$pdo;
			$sql = "UPDATE hx_template_category SET `name`='". $name . "' WHERE id=".$this->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() >= 0){
				return $this;
			}
			return NULL;
		} catch (PDOException $e){
			errorLog($e);
			return NULL;
		}
	}
	
	
}