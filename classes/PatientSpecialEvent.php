<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/9/15
 * Time: 11:59 AM
 */
class PatientSpecialEvent implements JsonSerializable
{
    private $id;
    private $patient;
    private $note;
    private $notedBy;
    private $date;
    private $dismissed;
    private $alert_date;

    /**
     * PatientSpecialEvents constructor.
     * @param $id
     */
    public function __construct($id=NULL)
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
	 * @return PatientSpecialEvent
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
	 * @return PatientSpecialEvent
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
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
	 * @return PatientSpecialEvent
	 */
	public function setNote($note)
	{
		$this->note = $note;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getNotedBy()
	{
		return $this->notedBy;
	}

	/**
	 * @param mixed $notedBy
	 * @return PatientSpecialEvent
	 */
	public function setNotedBy($notedBy)
	{
		$this->notedBy = $notedBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @param mixed $date
	 * @return PatientSpecialEvent
	 */
	public function setDate($date)
	{
		$this->date = $date;
		return $this;
	}
	public  function getAlertDate()
    {
        return $this->alert_date;
    }

    public function setAlertDate($alert_date)
    {
        $this->alert_date = $alert_date;
        return $this;
    }

	/**
	 * @return mixed
	 */
	public function getDismissed()
	{
		return $this->dismissed;
	}

	/**
	 * @param mixed $dismissed
	 * @return PatientSpecialEvent
	 */
	public function setDismissed($dismissed)
	{
		$this->dismissed = $dismissed;
		return $this;
	}




    function jsonSerialize()
    {
        return (object)get_object_vars($this);
    }


}