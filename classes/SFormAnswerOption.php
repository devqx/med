<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/7/17
 * Time: 5:28 PM
 */
class SFormAnswerOption implements JsonSerializable
{
	private $id;
	private $answer;
	private $option;
	private $text;
	
	/**
	 * SFormAnswerOption constructor.
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
	 * @return SFormAnswerOption
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAnswer()
	{
		return $this->answer;
	}
	
	/**
	 * @param mixed $answer
	 *
	 * @return SFormAnswerOption
	 */
	public function setAnswer($answer)
	{
		$this->answer = $answer;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getOption()
	{
		return $this->option;
	}
	
	/**
	 * @param mixed $option
	 *
	 * @return SFormAnswerOption
	 */
	public function setOption($option)
	{
		$this->option = $option;
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
	 * @return SFormAnswerOption
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
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		$answerText = !is_blank($this->getText()) ? quote_esc_str($this->getText()) : 'null';
		$optionId = $this->getOption() ? $this->getOption()->getId() : 'null';
		$sFormAnswer = $this->getAnswer()->getId();
		$sql = "INSERT INTO sform_answer_option SET answer_text=$answerText, option_id=$optionId, sform_answer_id=$sFormAnswer";
		//error_log($sql);
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $ex) {
			errorLog($ex);
			return null;
		}
	}
	
	
}