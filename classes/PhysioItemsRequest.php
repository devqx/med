<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/13/16
 * Time: 3:38 PM
 */
class PhysioItemsRequest implements JsonSerializable
{
	private $id;
	private $patient;
	private $requester;
	private $receiver;
	private $deliverer;
	private $requestTime;
	private $receiveTime;
	private $deliverTime;
	private $amount;
	private $items;
	private $status;
	private $serviceCentre;
	
	/**
	 * PhysioItemsRequests constructor.
	 *
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
	 *
	 * @return PhysioItemsRequest
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getPatient()
	{
		return $this->patient;
	}
	
	/**
	 * @param mixed $patient
	 *
	 * @return PhysioItemsRequest
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getRequester()
	{
		return $this->requester;
	}
	
	/**
	 * @param mixed $requester
	 *
	 * @return PhysioItemsRequest
	 */
	public function setRequester($requester)
	{
		$this->requester = $requester;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getRequestTime()
	{
		return $this->requestTime;
	}
	
	/**
	 * @param mixed $requestTime
	 *
	 * @return PhysioItemsRequest
	 */
	public function setRequestTime($requestTime)
	{
		$this->requestTime = $requestTime;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAmount()
	{
		return $this->amount;
	}
	
	/**
	 * @param mixed $amount
	 *
	 * @return PhysioItemsRequest
	 */
	public function setAmount($amount)
	{
		$this->amount = $amount;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getServiceCentre()
	{
		return $this->serviceCentre;
	}
	
	/**
	 * @param mixed $serviceCentre
	 *
	 * @return PhysioItemsRequest
	 */
	public function setServiceCentre($serviceCentre)
	{
		$this->serviceCentre = $serviceCentre;
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
	 *
	 * @return PhysioItemsRequest
	 */
	public function setItems($items)
	{
		$this->items = $items;
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
	 *
	 * @return PhysioItemsRequest
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getReceiveTime()
	{
		return $this->receiveTime;
	}
	
	/**
	 * @param mixed $receiveTime
	 *
	 * @return PhysioItemsRequest
	 */
	public function setReceiveTime($receiveTime)
	{
		$this->receiveTime = $receiveTime;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDeliverTime()
	{
		return $this->deliverTime;
	}
	
	/**
	 * @param mixed $deliverTime
	 *
	 * @return PhysioItemsRequest
	 */
	public function setDeliverTime($deliverTime)
	{
		$this->deliverTime = $deliverTime;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getReceiver()
	{
		return $this->receiver;
	}
	
	/**
	 * @param mixed $receiver
	 *
	 * @return PhysioItemsRequest
	 */
	public function setReceiver($receiver)
	{
		$this->receiver = $receiver;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDeliverer()
	{
		return $this->deliverer;
	}
	
	/**
	 * @param mixed $deliverer
	 *
	 * @return PhysioItemsRequest
	 */
	public function setDeliverer($deliverer)
	{
		$this->deliverer = $deliverer;
		return $this;
	}
	
	
	function cancel($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
		if ($this->getStatus() == 'Received') {
			//return the inventory to -1
			exit('error:Item has been received, cancellation is not possible at this time');
			//todo: how do you know which batches, the items were received into,
			//todo: because that's where we need to update the quantity
		}
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$pdo->beginTransaction();
			
			
			$sql = "UPDATE physiotherapy_items_request SET `status`='Cancelled' WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount()) {
				$bil = new Bill();
				$bil->setPatient($this->getPatient());
				$itemDescription = [];
				foreach ($this->getItems() as $item) {
					//$item = new PhysioItemsRequestData();
					$itemDescription[] = $item->getItem()->getName();
				}
				$bil->setDescription("Items Request Cancellation: " . implode(", ", $itemDescription));
				$bil->setItem(null);
				$bil->setSource((new BillSourceDAO())->findSourceById(20, $pdo));
				$bil->setTransactionType("reversal");
				$bil->setAmount(0 - $this->getAmount());
				$bil->setDiscounted(null);
				$bil->setDiscountedBy(null);
				$bil->setClinic(new Clinic(1));
				$bil->setDueDate($this->getRequestTime());
				$bil->setBilledTo((new PatientDemographDAO())->getPatient($this->getPatient()->getId(), false, $pdo, null)->getScheme());
				$costCentre = (is_null($this->getServiceCentre())) ? null : (new ServiceCenterDAO())->get($this->getServiceCentre()->getId(), $pdo)->getCostCentre();
				$bil->setCostCentre($costCentre);
				if ((new BillDAO())->addBill($bil, 1, $pdo)) {
					$pdo->commit();
					return $this;
				}
			}
			$pdo->rollBack();
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}