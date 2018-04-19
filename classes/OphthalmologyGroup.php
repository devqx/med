<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/17/14
 * Time: 2:13 PM
 */

//defined in lab_requests table
class OphthalmologyGroup implements JsonSerializable {
    private $id;
    private $group_name;
    private $patient;
    private $requested_by;
    private $request_time;
    private $clinic;
    private $referral;
    private $serviceCentre;
//    private $labs;
    //[in]correctly used in creating a lab request
    private $request_data;

    function __construct($id=NULL) {
        $this->id = $id;
    }
    
    /**
     * @return mixed
     */
    public function getGroupName()
    {
        return $this->group_name;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * @return mixed
     */
    public function getRequestTime()
    {
        return $this->request_time;
    }

    /**
     * @return mixed
     */
    public function getRequestedBy()
    {
        return $this->requested_by;
    }

    /**
     * @param mixed $group_name
     */
    public function setGroupName($group_name)
    {
        $this->group_name = $group_name;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $patient
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
    }

    /**
     * @param mixed $request_time
     */
    public function setRequestTime($request_time)
    {
        $this->request_time = $request_time;
    }

    /**
     * @param mixed $requested_by
     */
    public function setRequestedBy($requested_by)
    {
        $this->requested_by = $requested_by;
    }

    /**
     * @param mixed $request_data
     */
    public function setRequestData($request_data)
    {
        $this->request_data = $request_data;
    }

    /**
     * @return mixed
     */
    public function getRequestData()
    {
        return $this->request_data;
    }
    
    function getGroup_name() {
        return $this->group_name;
    }

    function getRequested_by() {
        return $this->requested_by;
    }

    function getRequest_time() {
        return $this->request_time;
    }

    function getClinic() {
        return $this->clinic;
    }

    function getRequest_data() {
        return $this->request_data;
    }

    function setGroup_name($group_name) {
        $this->group_name = $group_name;
    }

    function setRequested_by($requested_by) {
        $this->requested_by = $requested_by;
    }

    function setRequest_time($request_time) {
        $this->request_time = $request_time;
    }

    function setClinic($clinic) {
        $this->clinic = $clinic;
    }

    function setRequest_data($request_data) {
        $this->request_data = $request_data;
    }

    /**
     * @return mixed
     */
    public function getReferral()
    {
        return $this->referral;
    }

    /**
     * @param mixed $referral
     */
    public function setReferral($referral)
    {
        $this->referral = $referral;
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
     */
    public function setServiceCentre($serviceCentre)
    {
        $this->serviceCentre = $serviceCentre;
    }


    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function __toString() {
        return $this->group_name;
    }

    public function jsonSerialize()
    {
        return (object) get_object_vars($this);
    }


} 