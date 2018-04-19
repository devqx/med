<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/17/14
 * Time: 12:56 PM
 */

class PatientOphthalmology implements JsonSerializable {
    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    private $id;
    private $patient;
    private $ophthalmology;
    private $ophthalmology_group;
    private $resultApproved;
    private $approveDate;
    private $approver;
    private $performed_by;
    private $notes;

    private $ophthalmologyResult;
    private $test_date;

    private $status;
    private $serviceCentre;
    private $bill;

    function __construct($id=NULL) {
        $this->id = $id;
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
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
    public function getOphthalmologyGroup()
    {
        return $this->ophthalmology_group;
    }

    /**
     * @param mixed $ophthalmology_group
     */
    public function setOphthalmologyGroup($ophthalmology_group)
    {
        $this->ophthalmology_group = $ophthalmology_group;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param mixed $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
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
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
    }

    /**
     * @return mixed
     */
    public function getPerformedBy()
    {
        return $this->performed_by;
    }

    /**
     * @param mixed $performed_by
     */
    public function setPerformedBy($performed_by)
    {
        $this->performed_by = $performed_by;
    }
    /**
     * @return mixed
     */
    public function getOphthalmology()
    {
        return $this->ophthalmology;
    }

    /**
     * @param mixed $ophthalmology
     */
    public function setOphthalmology($ophthalmology)
    {
        $this->ophthalmology = $ophthalmology;
    }

    /**
     * @return mixed
     */
    public function getTestDate()
    {
        return $this->test_date;
    }

    /**
     * @param mixed $test_date
     */
    public function setTestDate($test_date)
    {
        $this->test_date = $test_date;
    }

    /**
     * @param mixed $resultApproved
     */
    public function setResultApproved($resultApproved)
    {
        $this->resultApproved = $resultApproved;
    }

    /**
     * @return mixed
     */
    public function isResultApproved()
    {
        return $this->resultApproved;
    }

    /**
     * @return mixed
     */
    public function getApproveDate()
    {
        return $this->approveDate;
    }

    /**
     * @param mixed $approveDate
     */
    public function setApproveDate($approveDate)
    {
        $this->approveDate = $approveDate;
    }

    /**
     * @return mixed
     */
    public function getApprover()
    {
        return $this->approver;
    }

    /**
     * @param mixed $approver
     */
    public function setApprover($approver)
    {
        $this->approver = $approver;
    }

    function getOphthalmologyResult() {
        return $this->ophthalmologyResult;
    }

    function setOphthalmologyResult($ophthalmologyResult) {
        $this->ophthalmologyResult = $ophthalmologyResult;
    }

    public function getServiceCentre(){
        return $this->serviceCentre;
    }

    public function setServiceCentre($serviceCentre){
        $this->serviceCentre = $serviceCentre;
    }
	
	/**
	 * @return mixed
	 */
	public function getBill()
	{
		return $this->bill;
	}
	
	/**
	 * @param mixed $bill
	 *
	 * @return PatientOphthalmology
	 */
	public function setBill($bill)
	{
		$this->bill = $bill;
		return $this;
	}
    
    

    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }

} 