<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/16/14
 * Time: 8:32 AM
 */

class Department implements JsonSerializable {
    private $id;
    private $name;
    private $costCentre;

    function __construct($id=NULL) {
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
     * @return Department
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Department
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCostCentre()
    {
        return $this->costCentre;
    }

    /**
     * @param mixed $costCentre
     * @return Department
     */
    public function setCostCentre($costCentre)
    {
        $this->costCentre = $costCentre;
        return $this;
    }



    public function jsonSerialize()
    {
        return (object)get_object_vars($this);
    }


} 