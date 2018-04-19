<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/15/16
 * Time: 4:12 PM
 */
class PhysioBooking implements JsonSerializable
{
    private $id;
    private $active;
    private $requestCode;
    private $patient;
    private $bookingDate;
    private $specialization;
    private $count;
    private $bookedBy;
    private $available;
    private $sessions;

    /**
     * PhysioBooking constructor.
     * @param $id
     */
    public function __construct($id=NULL)
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
     * @return PhysioBooking
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
     * @return PhysioBooking
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBookingDate()
    {
        return $this->bookingDate;
    }

    /**
     * @param mixed $bookingDate
     * @return PhysioBooking
     */
    public function setBookingDate($bookingDate)
    {
        $this->bookingDate = $bookingDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSpecialization()
    {
        return $this->specialization;
    }

    /**
     * @param mixed $specialization
     * @return PhysioBooking
     */
    public function setSpecialization($specialization)
    {
        $this->specialization = $specialization;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param mixed $count
     * @return PhysioBooking
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBookedBy()
    {
        return $this->bookedBy;
    }

    /**
     * @param mixed $bookedBy
     * @return PhysioBooking
     */
    public function setBookedBy($bookedBy)
    {
        $this->bookedBy = $bookedBy;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAvailable()
    {
        return $this->available;
    }

    /**
     * @param mixed $available
     * @return PhysioBooking
     */
    public function setAvailable($available)
    {
        $this->available = $available;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSessions()
    {
        return $this->sessions;
    }

    /**
     * @param mixed $sessions
     * @return PhysioBooking
     */
    public function setSessions($sessions)
    {
        $this->sessions = $sessions;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequestCode()
    {
        return $this->requestCode;
    }

    /**
     * @param mixed $requestCode
     * @return PhysioBooking
     */
    public function setRequestCode($requestCode)
    {
        $this->requestCode = $requestCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return boolval($this->active);
    }

    /**
     * @param mixed $active
     * @return PhysioBooking
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }



}