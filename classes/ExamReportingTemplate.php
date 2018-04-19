<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/5/16
 * Time: 5:59 PM
 */
class ExamReportingTemplate implements JsonSerializable
{
	private $id;
	private $title;
	private $bodyPart;

	/**
	 * ExamReportingTemplate constructor.
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
	 * @return ExamReportingTemplate
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
	 * @return ExamReportingTemplate
	 */
	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBodyPart()
	{
		return $this->bodyPart;
	}

	/**
	 * @param mixed $bodyPart
	 * @return ExamReportingTemplate
	 */
	public function setBodyPart($bodyPart)
	{
		$this->bodyPart = $bodyPart;
		return $this;
	}

	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}

	public function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$title = quote_esc_str($this->getTitle());
		$body_part = quote_esc_str($this->getBodyPart());
		try {
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO(): $pdo;
			$sql = "INSERT INTO exam_report_template (title, body_part) VALUES ($title, $body_part)";
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
		try {
			$pdo = $pdo==NULL?(new MyDBConnector())->getPDO():$pdo;
			$sql = "UPDATE exam_report_template SET title='".escape($this->getTitle())."', body_part='".escape($this->getBodyPart())."' WHERE id=".$this->getId();
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