<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/16/15
 * Time: 10:54 AM
 */
class HistoryTemplateData implements JsonSerializable
{
	private $id;
	private $historyTemplate;
	private $label;
	private $dataType;

	/**
	 * HistoryTemplateData constructor.
	 * @param $id
	 */
	public function __construct($id = null)
	{
		$this->id = $id;
	}

	function jsonSerialize()
	{
		return (object)get_object_vars($this);
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
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getHistoryTemplate()
	{
		return $this->historyTemplate;
	}

	/**
	 * @param mixed $historyTemplate
	 */
	public function setHistoryTemplate($historyTemplate)
	{
		$this->historyTemplate = $historyTemplate;
	}

	/**
	 * @return mixed
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * @param mixed $label
	 */
	public function setLabel($label)
	{
		$this->label = $label;
	}

	/**
	 * @return mixed
	 */
	public function getDataType()
	{
		return $this->dataType;
	}

	/**
	 * @param mixed $dataType
	 */
	public function setDataType($dataType)
	{
		$this->dataType = $dataType;
	}

	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "INSERT INTO history_template_data (history_template_id, label, datatype) VALUES (" . $this->getHistoryTemplate()->getId() . ", '" . $this->getLabel() . "', '" . $this->getDataType() . "')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$this->setId( $pdo->lastInsertId() );
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function renderType()
	{
		switch ($this->dataType) {
			case 'text':
				return 'type="text"';
			case 'float':
				return 'type="number" step="any"';
			case 'integer':
				return 'type="number"';
			case 'date':
				return 'type="date"';
		}
		return 'type="text"';
	}
}