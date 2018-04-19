<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/21/16
 * Time: 10:25 AM
 */
class ArvConsultingData implements JsonSerializable
{
    private $id;
    private $arvConsulting;
    private $type;
    private $typeData;

    /**
     * ArvConsultingData constructor.
     * @param $id
     */
    public function __construct($id = NULL)
    {
        $this->id = $id;
    }

    function jsonSerialize()
    {
        // Implement jsonSerialize() method.
        return (object)get_object_vars($this);
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
     * @return ArvConsultingData
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getArvConsulting()
    {
        return $this->arvConsulting;
    }

    /**
     * @param mixed $arvConsulting
     * @return ArvConsultingData
     */
    public function setArvConsulting($arvConsulting)
    {
        $this->arvConsulting = $arvConsulting;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return ArvConsultingData
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTypeData()
    {
        return $this->typeData;
    }

    /**
     * @param mixed $typeData
     * @return ArvConsultingData
     */
    public function setTypeData($typeData)
    {
        $this->typeData = $typeData;
        return $this;
    }
    
}