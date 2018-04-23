<?php
/**
 * Created by PhpStorm.
 * User: oluwaseunpaul
 * Date: 4/22/18
 * Time: 8:02 PM
 */

class HealthStates implements JsonSerializable
{


    private $state;
    private $id;

    /**
     * HealthStates constructor.
     */
    public function __construct()
    {


    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     * @return HealthStates
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
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
     * @return HealthStates
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }



    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
        return (object)get_object_vars($this);
    }


}