<?php
/**
 * Created by PhpStorm.
 * User: oluwaseunpaul
 * Date: 4/6/18
 * Time: 3:39 PM
 */

class InPatientHealthState implements JsonSerializable
{

    private $health_status;
    private $patient_id;
    private $risk_to_fall;
    private $id;


    /**
     * InPatientHealth constructor.
     */
    public function __construct()
    {

    }


    public function getHealthStatusId()
    {
        return $this->health_status;
    }

    /**
     * @param mixed $health_status
     */
    public function setHealthStatusId($health_status)
    {
        $this->health_status = $health_status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPatientId()
    {
        return $this->patient_id;
    }

    /**
     * @param mixed $patient_id
     * @return InPatientHealth
     */
    public function setPatientId($patient_id)
    {
        $this->patient_id = $patient_id;
        return $this;
    }



    /**
     * @return mixed
     */
    public function getRiskToFall()
    {
        return $this->risk_to_fall;
    }

    /**
     * @param mixed $risk_to_fall
     * @return InPatientHealthState
     */
    public function setRiskToFall($risk_to_fall)
    {
        $this->risk_to_fall = $risk_to_fall;
        return $this;
    }


    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
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
     * @return InPatientHealthState
     */

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }





}