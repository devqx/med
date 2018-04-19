<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/22/16
 * Time: 4:42 PM
 */
class IVFNoteTemplate implements JsonSerializable
{
	private $id;
	private $title;
	private $content;

	/**
	 * IVFNoteTemplate constructor.
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
	 * @return IVFNoteTemplate
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
	 * @return IVFNoteTemplate
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
	 * @return IVFNoteTemplate
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	function add($pdo = null)
	{
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$title = !is_blank($this->getTitle()) ? quote_esc_str($this->getTitle()) : 'NULL';
			$content = !is_blank($this->getContent()) ? quote_esc_str($this->getContent()) : 'NULL';
			$sql = "INSERT INTO ivf_note_template (title, content) VALUES ($title, $content)";
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

	function update($pdo = null)
	{
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$title = !is_blank($this->getTitle()) ? quote_esc_str($this->getTitle()) : 'NULL';
			$content = !is_blank($this->getContent()) ? quote_esc_str($this->getContent()) : 'NULL';
			$sql = "UPDATE ivf_note_template SET title=$title, content=$content WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}
