<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/27/17
 * Time: 12:49 PM
 */
class SFormOption implements JsonSerializable
{
	private $id;
	private $question;
	private $text;
	
	/**
	 * SFormOption constructor.
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
	 * @return SFormOption
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getQuestion()
	{
		return $this->question;
	}
	
	/**
	 * @param mixed $question
	 *
	 * @return SFormOption
	 */
	public function setQuestion($question)
	{
		$this->question = $question;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getText()
	{
		return $this->text;
	}
	
	/**
	 * @param mixed $text
	 *
	 * @return SFormOption
	 */
	public function setText($text)
	{
		$this->text = $text;
		return $this;
	}
	
	
	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$formQuestion = $this->getQuestion() ? $this->getQuestion()->getId() : 'null';
		$text = !is_blank($this->getText()) ? quote_esc_str($this->getText()) : 'null';
		$sql = "INSERT INTO sform_option SET sform_question_id=$formQuestion, `text`=$text";
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		}catch (PDOException $exception){
			errorLog($exception);
			return null;
		}
		
	}
	
	function update($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$formQuestion = $this->getQuestion() ? $this->getQuestion()->getId() : 'null';
		$text = !is_blank($this->getText()) ? quote_esc_str($this->getText()) : 'null';
		$sql = "UPDATE sform_option SET sform_question_id=$formQuestion, `text`=$text WHERE id={$this->getId()}";
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