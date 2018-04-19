<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/3/15
 * Time: 11:07 AM
 */
class PatientProcedureMedicalReport implements JsonSerializable
{
    private $id;
    private $patient_procedure;
    private $request_time;
    private $create_user;
    private $content;

    /**
     * PatientProcedureMedicalReport constructor.
     * @param $id
     */
    public function __construct($id=NULL)
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
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getPatientProcedure()
    {
        return $this->patient_procedure;
    }

    /**
     * @param mixed $patient_procedure
     */
    public function setPatientProcedure($patient_procedure)
    {
        $this->patient_procedure = $patient_procedure;
    }

    /**
     * @return mixed
     */
    public function getRequestTime()
    {
        return $this->request_time;
    }

    /**
     * @param mixed $request_time
     */
    public function setRequestTime($request_time)
    {
        $this->request_time = $request_time;
    }

    /**
     * @return mixed
     */
    public function getCreateUser()
    {
        return $this->create_user;
    }

    /**
     * @param mixed $create_user
     */
    public function setCreateUser($create_user)
    {
        $this->create_user = $create_user;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }



    function jsonSerialize()
    {
        return (object)get_object_vars($this);
    }


}