<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/6/18
 * Time: 5:03 PM
 */

class HxTemplate implements JsonSerializable
{
	
	private $id;
	private $category;
	private $name;
	private $content;
	
	/**
	 * HxTemplate constructor.
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
	 * @return HxTemplate
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCategory()
	{
		return $this->category;
	}
	
	/**
	 * @param mixed $category
	 *
	 * @return HxTemplate
	 */
	public function setCategory($category)
	{
		$this->category = $category;
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
	 * @return HxTemplate
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getContent()
	{
		return $this->content;
	}
	
	/**
	 * @param mixed $content
	 *
	 * @return HxTemplate
	 */
	public function setContent($content)
	{
		$this->content = $content;
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
		$name = escape($this->getName());
		$category_id = $this->getCategory()->getId();
		$note = escape($this->getContent());
		try {
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO(): $pdo;
			$sql = "INSERT INTO hx_template (`name`, category_id, note) VALUES ('$name', $category_id, '$note')";
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
		$category_id = $this->getCategory()->getId();
		$note = quote_esc_str($this->getContent());
		
		try {
			$pdo = $pdo==NULL?(new MyDBConnector())->getPDO():$pdo;
			$sql = "UPDATE hx_template SET `name`='". $name . "',  category_id='". $category_id ."', note='" . $note ."' WHERE id=".$this->getId();
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