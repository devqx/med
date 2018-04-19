<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date:
 * Time: 10:43 AM
 */
class ArvHistory implements JsonSerializable
{
    private $id;
    private $template;

    /**
     * History constructor.
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
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null $id
     * @return ArvHistory
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param mixed $template
     * @return ArvHistory
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    

}