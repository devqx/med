<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/9/17
 * Time: 4:52 PM
 */

class TransferData implements JsonSerializable
{
	private $id;
	private $transfer;
	private $cell;
	private $cellsTransferred;
	private $type;
	private $embrayoStage;
	private $quality;
	
	/**
	 * TransferData constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
	public function jsonSerialize()
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
	 * @return TransferData
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getTransfer()
	{
		return $this->transfer;
	}
	
	/**
	 * @param mixed $transfer
	 *
	 * @return TransferData
	 */
	public function setTransfer($transfer)
	{
		$this->transfer = $transfer;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCell()
	{
		return $this->cell;
	}
	
	/**
	 * @param mixed $cell
	 *
	 * @return TransferData
	 */
	public function setCell($cell)
	{
		$this->cell = $cell;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCellsTransferred()
	{
		return $this->cellsTransferred;
	}
	
	/**
	 * @param mixed $cellsTransferred
	 *
	 * @return TransferData
	 */
	public function setCellsTransferred($cellsTransferred)
	{
		$this->cellsTransferred = $cellsTransferred;
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
	 * @return TransferData
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getEmbrayoStage()
	{
		return $this->embrayoStage;
	}
	
	/**
	 * @param mixed $embrayoStage
	 *
	 * @return TransferData
	 */
	public function setEmbrayoStage($embrayoStage)
	{
		$this->embrayoStage = $embrayoStage;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getQuality()
	{
		return $this->quality;
	}
	
	/**
	 * @param mixed $qaulity
	 *
	 * @return TransferData
	 */
	public function setQuality($quality)
	{
		$this->quality = $quality;
		return $this;
	}
	
	
	
	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			//$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$transfer = $this->getTransfer()->getId();
			$num_transferred = $this->getCellsTransferred();
			$cell = $this->getCell() ? $this->getCell() : 'NULL';
			$type = $this->getType()?$this->getType()->getId() : "NULL";
			$em_stage = $this->getEmbrayoStage() ? $this->getEmbrayoStage() : 'NULL';
			$em_quality = $this->getQaulity() ? $this->getQaulity() : 'NULL';
			$sql = "INSERT INTO ivf_transfer_data (transfer_id, cell, num_transferred, transfer_type_id, embrayo_stage, quality) VALUES ($transfer, $cell, $num_transferred, $type, '". $em_stage ."', '". $em_quality ."')";
			error_log($sql);
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