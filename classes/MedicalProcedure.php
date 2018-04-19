<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/15/14
 * Time: 11:05 AM
 */

class MedicalProcedure implements JsonSerializable {
    private $id;
    private $billing_code;
    private $name;

    function __construct($id=NULL) {
        $this->id = $id;
    }

    /**
     * @param mixed $billing_code
     */
    public function setBillingCode($billing_code)
    {
        $this->billing_code = $billing_code;
    }

    /**
     * @return mixed
     */
    public function getBillingCode()
    {
        return $this->billing_code;
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return (object) get_object_vars($this);
    }


}