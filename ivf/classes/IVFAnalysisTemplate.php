<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/7/18
 * Time: 11:43 AM
 */

class IVFAnalysisTemplate implements JsonSerializable
{
	
	private $id;
	private $name;
	private $type;
	private $note;
	
	/**
	 * IVFAnalysisTemplate constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
	
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
	 * @return IVFAnalysisTemplate
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
	 * @return IVFAnalysisTemplate
	 */
	public function setName($name)
	{
		$this->name = $name;
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
	 * @return IVFAnalysisTemplate
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getNote()
	{
		return $this->note;
	}
	
	/**
	 * @param mixed $note
	 *
	 * @return IVFAnalysisTemplate
	 */
	public function setNote($note)
	{
		$this->note = $note;
		return $this;
	}
	
	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
	
	function add($pdo = null)
	{
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$name = !is_blank($this->getName()) ? quote_esc_str($this->getName()) : 'NULL';
			$type = !is_blank($this->getType()) ? quote_esc_str($this->getType()) : 'NULL';
			$content = !is_blank($this->getNote()) ? quote_esc_str($this->getNote()) : 'NULL';
			$sql = "INSERT INTO ivf_analysis_templates (name, type, note) VALUES ($name, $type, $content)";
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
			$name = !is_blank($this->getName()) ? quote_esc_str($this->getName()) : 'NULL';
			$type = !is_blank($this->getType()) ? quote_esc_str($this->getType()) : 'NULL';
			$content = !is_blank($this->getNote()) ? quote_esc_str($this->getNote()) : 'NULL';
			$sql = "UPDATE ivf_analysis_templates SET name=$name, type=$type, note=$content WHERE id={$this->getId()}";
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