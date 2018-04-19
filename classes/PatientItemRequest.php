<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/7/17
 * Time: 10:22 AM
 */
class PatientItemRequest implements JsonSerializable
{
	private $id;
	private $patient;
	private $code;
	private $request_date;
	private $requested_by;
	private $service_center;
	private $inpatient;
	private $request_note;
	private $data;
	private $encounter;
	private $procedure;

	/**
	 * PatientItemRequest constructor.
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
	 * @return PatientItemRequest
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
	 * @return PatientItemRequest
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getServiceCenter()
	{
		return $this->service_center;
	}

	/**
	 * @param mixed $service_center
	 * @return PatientItemRequest
	 */
	public function setServiceCenter($service_center)
	{
		$this->service_center = $service_center;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getInpatient()
	{
		return $this->inpatient;
	}

	/**
	 * @param mixed $inpatient
	 * @return PatientItemRequest
	 */
	public function setInpatient($inpatient)
	{
		$this->inpatient = $inpatient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param mixed $code
	 * @return PatientItemRequest
	 */
	public function setCode($code)
	{
		$this->code = $code;
		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getRequestDate()
	{
		return $this->request_date;
	}

	/**
	 * @param mixed $request_date
	 * @return PatientItemRequest
	 */
	public function setRequestDate($request_date)
	{
		$this->request_date = $request_date;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRequestedBy()
	{
		return $this->requested_by;
	}

	/**
	 * @param mixed $requested_by
	 * @return PatientItemRequest
	 */
	public function setRequestedBy($requested_by)
	{
		$this->requested_by = $requested_by;
		return $this;
	}


	public function getRequestNote()
	{
		return $this->request_note;
	}

	/**
	 * @param mixed $request_note
	 * @return PatientItemRequest
	 */
	public function setRequestNote($request_note)
	{
		$this->request_note = $request_note;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param mixed $data
	 * @return PatientItemRequest
	 */
	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEncounter()
	{
		return $this->encounter;
	}

	/**
	 * @param mixed $encounter
	 * @return PatientItemRequest
	 */
	public function setEncounter($encounter)
	{
		$this->encounter = $encounter;
		return $this;
	}

    /**
     * @return mixed
     */
    public function getProcedure()
    {
        return $this->procedure;
    }

    /**
     * @param mixed $procedure
     * @return PatientItemRequest
     */
    public function setProcedure($procedure)
    {
        $this->procedure = $procedure;
        return $this;
    }




	function jsonSerialize()
	{
		return (object)get_object_vars($this);
		// TODO: Implement jsonSerialize() method.
	}


	function generateItemCode($pdo)
	{
		try {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;

			$sql = "SELECT LPAD( COUNT(*)+1 , 7, 0) AS val FROM `patient_item_request` WHERE MONTH(`requested_date`) = MONTH(NOW())";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return 'CCI' . date("/y/m/") . $row['val'];
			}
			return 'CCI' . date("/Y/m/") . strtoupper(substr(str_shuffle(md5(microtime())), 0, 4));
		} catch (PDOException $e) {
			errorLog($e);
			return 'CCI' . date("/y/m/") . strtoupper(substr(str_shuffle(md5(microtime())), 0, 4));

		}
	}


	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$in_patient = $this->getInpatient() ? $this->getInpatient()->getId() : 'NULL';
		$encounter = $this->getEncounter() ? $this->getEncounter()->getId() : "NULL";
		$procedure = $this->getProcedure() ? $this->getProcedure()->getId() : 'NULL';
		$requested_by = $this->getRequestedBy() ? $this->getRequestedBy()->getId() : $_SESSION['staffID'];

		try {
			$sql = "INSERT INTO patient_item_request SET patient_id='" . $this->getPatient()->getId() . "', group_code='" . $this->getCode() . "', requested_by=$requested_by, service_center_id='" . $this->getServiceCenter() . "', inpatient_id=$in_patient, note='" . $this->getRequestNote() . "', encounter_id=$encounter, procedure_id=$procedure";
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$canCommit = !$pdo->inTransaction();
			try {
				$pdo->beginTransaction();
			} catch (PDOException $e){
			}
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$data = [];
			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				foreach ($this->getData() as $datum) {
					$datum->setRequestItem($this);
					$it = $datum->add($pdo);
					if ($it != null) {
						$data[] = $it;
					}
				}
				if (count($data) != sizeof($this->getData())) {
					if($canCommit){
						$pdo->rollBack();
					}
					return null;
				}
				if($canCommit){
					$pdo->commit();
				}
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			$pdo->rollBack();
			$stmt = null;
			return null;
		}

	}
}