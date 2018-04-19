<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/7/15
 * Time: 11:33 AM
 */
class LabComboData implements JsonSerializable
{
    private $id;
    private $labCombo;
    private $lab;

    /**
     * LabComboData constructor.
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
     * @return LabComboData
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabCombo()
    {
        return $this->labCombo;
    }

    /**
     * @param mixed $labCombo
     * @return LabComboData
     */
    public function setLabCombo($labCombo)
    {
        $this->labCombo = $labCombo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLab()
    {
        return $this->lab;
    }

    /**
     * @param mixed $lab
     * @return LabComboData
     */
    public function setLab($lab)
    {
        $this->lab = $lab;
        return $this;
    }
}