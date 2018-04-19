<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/13/16
 * Time: 10:25 AM
 */
class GeneticTemplate implements JsonSerializable
{
	private $id;
	private $name;
	private $content;

	/**
	 * GeneticTemplate constructor.
	 * @param $id
	 */
	public function __construct($id = null)
	{
		$this->id = $id;
	}

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
	 * @return GeneticTemplate
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
	 * @return GeneticTemplate
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
	 * @return GeneticTemplate
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	function update($pdo=null){
		try {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$content = escape($this->getContent());
			$sql = "UPDATE genetic_template SET `name`='{$this->getName()}', content='$content' WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}