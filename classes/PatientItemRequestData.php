<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/7/17
 * Time: 10:28 AM
 */
class PatientItemRequestData implements JsonSerializable
{
	private $id;
	private $group_code;
	private $requestItem;
	private $generic;
	private $item;
	private $quantity;
	private $batch;
	private $filled_by;
	private $filled_date;
	private $status;
	private $cancelled_on;
	private $cancelled_by;
	private $hosp_id;
	private $cancelled_note;
	private $filledQuantity;
	private $completedBy;
	private $completedOn;
	private $requestedFrom;

	/**
	 * PatientItemRequestData constructor.
	 * @param $id
	 */
	public function __construct($id = null)
	{
		$this->id = $id;
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
	 * @return PatientItemRequestData
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getGroupCode()
	{
		return $this->group_code;
	}

	/**
	 * @param mixed $group_code
	 * @return PatientItemRequestData
	 */
	public function setGroupCode($group_code)
	{
		$this->group_code = $group_code;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getGeneric()
	{
		return $this->generic;
	}

	/**
	 * @param mixed $generic
	 * @return PatientItemRequestData
	 */
	public function setGeneric($generic)
	{
		$this->generic = $generic;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getItem()
	{
		return $this->item;
	}

	/**
	 * @param mixed $item
	 * @return PatientItemRequestData
	 */
	public function setItem($item)
	{
		$this->item = $item;
		return $this;
	}

	/**
	 * @return mixed
	 */


	
	public function getQuantity()
	{
		return $this->quantity;
	}

	/**
	 * @param mixed $quantity
	 * @return PatientItemRequestData
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBatch()
	{
		return $this->batch;
	}

	/**
	 * @param mixed $batch
	 * @return PatientItemRequestData
	 */
	public function setBatch($batch)
	{
		$this->batch = $batch;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRequestItem()
	{
		return $this->requestItem;
	}

	/**
	 * @param mixed $requestItem
	 * @return PatientItemRequestData
	 */
	public function setRequestItem($requestItem)
	{
		$this->requestItem = $requestItem;
		return $this;
	}

	

	/**
	 * @return mixed
	 */
	public function getFilledDate()
	{
		return $this->filled_date;
	}

	/**
	 * @param mixed $filled_date
	 * @return PatientItemRequestData
	 */
	public function setFilledDate($filled_date)
	{
		$this->filled_date = $filled_date;
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
	 * @return PatientItemRequestData
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCancelledOn()
	{
		return $this->cancelled_on;
	}

	/**
	 * @param mixed $cancelled_on
	 * @return PatientItemRequestData
	 */
	public function setCancelledOn($cancelled_on)
	{
		$this->cancelled_on = $cancelled_on;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCancelledBy()
	{
		return $this->cancelled_by;
	}

	/**
	 * @param mixed $cancelled_by
	 * @return PatientItemRequestData
	 */
	public function setCancelledBy($cancelled_by)
	{
		$this->cancelled_by = $cancelled_by;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getFilledBy()
	{
		return $this->filled_by;
	}

	/**
	 * @param mixed $filled_by
	 * @return PatientItemRequestData
	 */
	public function setFilledBy($filled_by)
	{
		$this->filled_by = $filled_by;
		return $this;
	}

	
	/**
	 * @return mixed
	 */
	public function getHospId()
	{
		return $this->hosp_id;
	}

	/**
	 * @param mixed $hosp_id
	 * @return PatientItemRequestData
	 */
	public function setHospId($hosp_id)
	{
		$this->hosp_id = $hosp_id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCancelledNote()
	{
		return $this->cancelled_note;
	}

	/**
	 * @param mixed $cancelled_note
	 * @return PatientItemRequestData
	 */
	public function setCancelledNote($cancelled_note)
	{
		$this->cancelled_note = $cancelled_note;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getFilledQuantity()
	{
		return $this->filledQuantity;
	}

	/**
	 * @param mixed $filledQuantity
	 * @return PatientItemRequestData
	 */
	public function setFilledQuantity($filledQuantity)
	{
		$this->filledQuantity = $filledQuantity;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCompletedBy()
	{
		return $this->completedBy;
	}

	/**
	 * @param mixed $completedBy
	 * @return PatientItemRequestData
	 */
	public function setCompletedBy($completedBy)
	{
		$this->completedBy = $completedBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCompletedOn()
	{
		return $this->completedOn;
	}

	/**
	 * @param mixed $completedOn
	 * @return PatientItemRequestData
	 */
	public function setCompletedOn($completedOn)
	{
		$this->completedOn = $completedOn;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRequestedFrom()
	{
		return $this->requestedFrom;
	}

	/**
	 * @param mixed $requestedFrom
	 * @return PatientItemRequestData
	 */
	public function setRequestedFrom($requestedFrom)
	{
		$this->requestedFrom = $requestedFrom;
		return $this;
	}

	
	


	function jsonSerialize()
	{
		return (object) get_object_vars($this);
	}

    function add($pdo = null)
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
        $pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
        $cancelled_by = $this->getCancelledBy() ? $this->getCancelledBy()->getId() : "NULL";
        $cancelled_note = $this->getCancelledNote() ? escape($this->getCancelledNote()) : "NULL";
        $filled_by = $this->getFilledBy() ? $this->getFilledBy()->getId() : "NULL";
        $batch_id = $this->getBatch() ? $this->getBatch()->getId() : "NULL";
        $filled_qty = $this->getFilledQuantity() ? $this->getFilledQuantity() : "NULL";
        $request_qty = $this->getQuantity() ? $this->getQuantity() : "NULL";
        $generic = $this->getGeneric() ? $this->getGeneric()->getId() : "NULL";
        $item = $this->getItem() ? $this->getItem()->getId() : "NULL";
        $completed_by = $this->getCompletedBy() ? $this->getCompletedBy()->getId() : "NULL";
        $completed_on = $this->getCompletedOn() ? $this->getCompletedOn() : "NULL";
        $filled_on = $this->getFilledDate() ? $this->getFilledDate() : "NULL";
        try {
            $sql = "INSERT INTO patient_item_request_data SET group_code='" . $this->getGroupCode() . "', generic_id=$generic,  item_id=$item, quantity=$request_qty, batch_id=$batch_id, `status`= '" . $this->getStatus() . "', cancelled_by=$cancelled_by,  hosp_id='" . $this->getHospId() . "', cancelled_note=$cancelled_note, filled_qty=$filled_qty, filled_date='". $filled_on ."', completed_on='". $completed_on ."', filled_by=$filled_by, completed_by=$completed_by";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return $this;
            }
            return null;
        } catch (PDOException $e) {
            errorLog($e);
            return null;
        }
    }

}