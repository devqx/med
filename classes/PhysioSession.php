<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/15/16
 * Time: 4:19 PM
 */
class PhysioSession implements JsonSerializable
{
private $id;
    private $booking;
    private $date;
    private $note;
    private $notedBy;

    /**
     * PhysioSession constructor.
     * @param $id
     */
    public function __construct($id = NULL)
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
     * @return PhysioSession
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBooking()
    {
        return $this->booking;
    }

    /**
     * @param mixed $booking
     * @return PhysioSession
     */
    public function setBooking($booking)
    {
        $this->booking = $booking;
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
     * @return PhysioSession
     */
    public function setDate($date)
    {
        $this->date = $date;
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
     * @return PhysioSession
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
     * @return PhysioSession
     */
    public function setNotedBy($notedBy)
    {
        $this->notedBy = $notedBy;
        return $this;
    }



}