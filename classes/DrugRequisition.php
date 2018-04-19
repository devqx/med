<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/26/16
 * Time: 4:36 PM
 */
class DrugRequisition implements JsonSerializable
{
	private $id;
	private $createDate;
	private $createUser;
	private $status;
	private $lastActionUser;
	private $lastAction;
	private $items;
	private $itemsCount;

	/**
	 * DrugRequistion constructor.
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
	 * @return DrugRequisition
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLastAction()
	{
		return $this->lastAction;
	}

	/**
	 * @param mixed $lastAction
	 * @return DrugRequisition
	 */
	public function setLastAction($lastAction)
	{
		$this->lastAction = $lastAction;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCreateDate()
	{
		return $this->createDate;
	}

	/**
	 * @param mixed $createDate
	 * @return DrugRequisition
	 */
	public function setCreateDate($createDate)
	{
		$this->createDate = $createDate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCreateUser()
	{
		return $this->createUser;
	}

	/**
	 * @param mixed $createUser
	 * @return DrugRequisition
	 */
	public function setCreateUser($createUser)
	{
		$this->createUser = $createUser;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param mixed $status
	 * @return DrugRequisition
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLastActionUser()
	{
		return $this->lastActionUser;
	}

	/**
	 * @param mixed $lastActionUser
	 * @return DrugRequisition
	 */
	public function setLastActionUser($lastActionUser)
	{
		$this->lastActionUser = $lastActionUser;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @param mixed $items
	 * @return DrugRequisition
	 */
	public function setItems($items)
	{
		$this->items = $items;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getItemsCount()
	{
		return $this->itemsCount;
	}

	/**
	 * @param mixed $itemsCount
	 * @return DrugRequisition
	 */
	public function setItemsCount($itemsCount)
	{
		$this->itemsCount = $itemsCount;
		return $this;
	}


	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}

	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . "/Connections/MyDBConnector.php";
		require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/utils.php";

		$createDate = $this->getCreateDate() ? quote_esc_str($this->getCreateDate()) : quote_esc_str(date('Y-m-d H:i:s')) ;
		$createUser = $this->getCreateUser() ? $this->getCreateUser()->getId() : $_SESSION['staffID'];
		$status = $this->getStatus() ? quote_esc_str($this->getStatus()) : quote_esc_str("draft");
		$lastEditUser = $this->getLastActionUser() ? $this->getLastActionUser()->getId() : $_SESSION['staffID'];
		$lastAction = quote_esc_str('Create');

		try {
			$sql = "INSERT INTO drug_requisition SET create_date=$createDate, create_user_id=$createUser, `status`=$status, last_action_user=$lastEditUser, last_action=$lastAction, last_action_time=NOW()";
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$pdo->beginTransaction();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$items = [];
			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				foreach ($this->getItems() as $item) {
					$item->setRequisition($this);
					$_ = $item->add($pdo);
					if($_ != null){$items[] = $_;}
				}

				if(count($items) != sizeof($this->getItems())){
					$pdo->rollBack();
					return null;
				}
				error_log(count($items).",".sizeof($this->getItems()));
				$pdo->commit();
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function update($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . "/Connections/MyDBConnector.php";
		require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/utils.php";
		require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/ServiceCenter.php";
		require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/DrugBatchDAO.php";
		require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DrugBatch.php";

		$createDate = $this->getCreateDate() ? quote_esc_str($this->getCreateDate()) : "NULL";
		$createUser = $this->getCreateUser() ? $this->getCreateUser()->getId() : "NULL";
		$status = $this->getStatus() ? quote_esc_str($this->getStatus()) : quote_esc_str("draft");
		$lastEditUser = $this->getLastActionUser() ? $this->getLastActionUser()->getId() : $_SESSION['staffID'];
		$lastAction = $status;

		try {
			$sql = "UPDATE drug_requisition SET create_date=$createDate, create_user_id=$createUser, `status`=$status, last_action_user=$lastEditUser, last_action_time=NOW(), last_action=$lastAction WHERE id={$this->getId()}";
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$pdo->beginTransaction();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				if($this->getStatus() == "Received"){
					//to add the batch
					foreach ($this->getItems() as $item) {
						//$item = new DrugRequisitionLine();
						$batch = (new DrugBatch())->setDrug( $item->getDrug() )->setExpirationDate($item->getExpiration())->setName($item->getBatchName())->setQuantity($item->getQuantity())->setServiceCentre(new ServiceCenter($_SESSION['service_centre_id']));
						if( (new DrugBatchDAO())->add($batch, $pdo) === null){
							$pdo->rollBack();
							return null;
						}
					}
					unset($_SESSION['service_centre_id']);
				}
				$pdo->commit();
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
}