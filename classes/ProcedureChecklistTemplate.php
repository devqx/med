<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 7/5/16
 * Time: 12:06 PM
 */
class ProcedureChecklistTemplate implements JsonSerializable
{
	private $id;
	private $title;
	private $content;

	/**
	 * ProcedureChecklistTemplate constructor.
	 * @param $id
	 */
	public function __construct($id=null)
	{
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
	 * @return ProcedureChecklistTemplate
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
	 * @return ProcedureChecklistTemplate
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
	 * @return ProcedureChecklistTemplate
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		$title = $this->getTitle() ? quote_esc_str($this->getTitle()) : "NULL";
		$content = $this->getContent() ? quote_esc_str($this->getContent()) : "NULL";
		$sql = "INSERT INTO procedure_checklist_template (title, content) VALUES ($title, $content)";
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}

	function update($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		$title = $this->getTitle() ? quote_esc_str($this->getTitle()) : "NULL";
		$content = $this->getContent() ? quote_esc_str($this->getContent()) : "NULL";
		$sql = "UPDATE procedure_checklist_template SET title=$title, content=$content WHERE id={$this->getId()}";
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}