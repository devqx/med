<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/2/16
 * Time: 9:01 AM
 */
class NursingTemplate implements JsonSerializable
{
	private $id;
	private $title;
	private $content;

	/**
	 * NursingTemplate constructor.
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }

	/**
	 * @return null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param null $id
	 * @return NursingTemplate
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param mixed $title
	 * @return NursingTemplate
	 */
	public function setTitle($title)
	{
		$this->title = $title;
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
	 * @return NursingTemplate
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}

	public function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$title = escape($this->getTitle());
		$content = escape($this->getContent());
		try {
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO(): $pdo;
			$sql = "INSERT INTO nursing_template (title, content) VALUES ('$title', '$content')";
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
	public function update($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$title = escape($this->getTitle());
		$content = escape($this->getContent());
		try {
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO(): $pdo;
			$sql = "UPDATE nursing_template SET title = '$title', content='$content' WHERE id=".$this->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() >= 0){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
}