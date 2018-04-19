<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/27/17
 * Time: 10:38 AM
 */
class SForm implements JsonSerializable
{
	private $id;
	private $name;
	private $category;
	private $questions;
	
	static $options = ['text', 'radio', 'checkbox', 'longtext', 'number', 'date'];
	/**
	 * SForm constructor.
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
	 * @return SForm
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
	 * @return SForm
	 */
	public function setName($name)
	{
		$this->name = $name;
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
	 * @return SForm
	 */
	public function setCategory($category)
	{
		$this->category = $category;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getQuestions()
	{
		return $this->questions;
	}
	
	/**
	 * @param mixed $questions
	 *
	 * @return SForm
	 */
	public function setQuestions($questions)
	{
		$this->questions = $questions;
		return $this;
	}
	
	function add($pdo=null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$category = $this->getCategory() ? $this->getCategory()->getId() : 'null';
		$name = !is_blank($this->getName()) ? quote_esc_str($this->getName()) : 'null';
		
		$sql = "INSERT INTO sform SET `name`=$name, category_id=$category";
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				$this->setId($pdo->lastInsertId());
				foreach ($this->getQuestions() as $question){
					//$question= new SFormQuestion();
					$question->setForm($this)->add($pdo);
				}
				unset($question);
				return $this;
			}
			return null;
		}catch (PDOException $exception){
			errorLog($exception);
			return null;
		}
	}
	function update($pdo=null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$category = $this->getCategory() ? $this->getCategory()->getId() : 'null';
		$name = !is_blank($this->getName()) ? quote_esc_str($this->getName()) : 'null';
		
		$sql = "UPDATE sform SET `name`=$name, category_id=$category WHERE id={$this->getId()}";
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return $this;
			}
			return null;
		}catch (PDOException $exception){
			errorLog($exception);
			return null;
		}
	}
}