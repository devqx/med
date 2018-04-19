<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/10/15
 * Time: 10:43 AM
 */

class Invoice implements JsonSerializable {
    private $id;
    private $lines;

    private $scheme;
    private $patient;

    private $cashier;
    private $time;

    function __construct($id=NULL)
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
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * @param mixed $lines
     */
    public function setLines($lines)
    {
        $this->lines = $lines;
    }

    /**
     * @return mixed
     */
    public function getCashier()
    {
        return $this->cashier;
    }

    /**
     * @param mixed $cashier
     */
    public function setCashier($cashier)
    {
        $this->cashier = $cashier;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param mixed $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * @return mixed
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @param mixed $scheme
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
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




    function jsonSerialize()
    {
        return (object)get_object_vars($this);
    }


}