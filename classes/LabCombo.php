<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/7/15
 * Time: 11:23 AM
 */
class LabCombo implements JsonSerializable
{
	private $id;
	private $name;
	private $combos;
	
	/**
	 * LabCombo constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null)
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
	 *
	 * @return LabCombo
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
	 * @return LabCombo
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCombos()
	{
		return $this->combos;
	}
	
	/**
	 * @param mixed $combos
	 *
	 * @return LabCombo
	 */
	public function setCombos($combos)
	{
		$this->combos = $combos;
		return $this;
	}
	
	function update($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$pdo->beginTransaction();
			$name = quote_esc_str($this->getName());
			$sql = "UPDATE lab_combo SET `name`=$name WHERE id=" . $this->getId();// VALUES ('" . escape($combo->getName()) . "')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$sql1 = "DELETE FROM lab_combo_data WHERE lab_combo_id=" . $this->getId();// VALUES ('" . escape($combo->getName()) . "')";
			$stmt1 = $pdo->prepare($sql1, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt1->execute();
			
			$data = [];
			foreach ($this->getCombos() as $comboData) {
				$comboData->setLabCombo($this);
				$data[] = (new LabComboDataDAO())->add($comboData, $pdo);
			}
			
			if (in_array(null, $data)) {
				$pdo->rollBack();
				return null;
			}
			$pdo->commit();
			return $this;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
	
}