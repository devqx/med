<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/27/17
 * Time: 10:52 AM
 */
class SFormQuestion implements JsonSerializable
{
	private $id;
	private $form;
	private $text;
	private $type;
	private $options;
	private $page;
	
	private $answer;
	
	/**
	 * SFormQuestion constructor.
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
	 * @return SFormQuestion
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getForm()
	{
		return $this->form;
	}
	
	/**
	 * @param mixed $form
	 *
	 * @return SFormQuestion
	 */
	public function setForm($form)
	{
		$this->form = $form;
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
	 * @return SFormQuestion
	 */
	public function setText($text)
	{
		$this->text = $text;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * @param mixed $type
	 *
	 * @return SFormQuestion
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
	/**
	 * @return mixed
	 */
	public function getOptions()
	{
		return $this->options;
	}
	
	/**
	 * @param mixed $options
	 *
	 * @return SFormQuestion
	 */
	public function setOptions($options)
	{
		$this->options = $options;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getPage()
	{
		return $this->page;
	}
	
	/**
	 * @param mixed $page
	 *
	 * @return SFormQuestion
	 */
	public function setPage($page)
	{
		$this->page = $page;
		return $this;
	}
	
	/**
	 * @param null $encounterId
	 * @param null $pdo
	 *
	 * @return mixed
	 */
	public function getAnswer($encounterId=null, $pdo=null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SFormAnswerOptionDAO.php';
		
		$sql = "SELECT sao.* FROM sform_answer_option sao LEFT JOIN sform_answer sa ON sa.id=sao.sform_answer_id WHERE sa.encounter_id=$encounterId AND sa.question_id={$this->getId()}";
		
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$data = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = (new SFormAnswerOptionDAO())->get($row['id'], $pdo);
			}
			return $data;
			
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
		//return $this->answer;
	}
	
	/**
	 * @param mixed $answer
	 *
	 * @return SFormQuestion
	 */
	public function setAnswer($answer)
	{
		$this->answer = $answer;
		return $this;
	}
	
	function add($pdo=null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$formId = $this->getForm() ? $this->getForm()->getId() : 'null';
		$text = !is_blank($this->getText()) ? quote_esc_str($this->getText()) : 'null';
		$type = !is_blank($this->getType()) ? quote_esc_str($this->getType()) : 'null';
		$page = $this->getPage() ? (int)$this->getPage() : 1;
		
		$sql = "INSERT INTO sform_question SET `sform_id`=$formId, `text`=$text, type=$type, `page`=$page";
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				$this->setId($pdo->lastInsertId());
				foreach ($this->getOptions() as $option){
					//$option=new SFormOption();
					$option->setQuestion($this)->add($pdo);
				}
				unset($option);
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
		$formId = $this->getForm() ? $this->getForm()->getId() : 'null';
		$text = !is_blank($this->getText()) ? quote_esc_str($this->getText()) : 'null';
		$type = !is_blank($this->getType()) ? quote_esc_str($this->getType()) : 'null';
		
		$sql = "UPDATE sform_question SET `sform_id`=$formId, `text`=$text, type,$type WHERE id={$this->getId()}";
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