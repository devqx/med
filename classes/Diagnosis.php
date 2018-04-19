<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/3/14
 * Time: 11:31 AM
 */

class Diagnosis implements JsonSerializable {
    private $id;
    private $name;
    private $code;
    private $type;
    private $parent;
    private $oi;

    function __construct($id=NULL) {
        $this->id = $id;
    }

    /**
     * @param mixed $case
     */
    public function setName($case)
    {
        $this->name = $case;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
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
     * @param mixed $oi
     */
    public function setOi($oi)
    {
        $this->oi = $oi;
    }

    /**
     * @return mixed
     */
    public function getOi()
    {
        return $this->oi;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
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
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    function __toString()
    {
        $str = $this->getName();
        if ($this->getType()=='icd10'){
            $str .= "(ICD10: ". $this->getCode(). ")";
        } else {
            $str .= "(ICPC-2: ". $this->getCode(). ")";
        }
        return $str;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
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
        return (object)get_object_vars($this);
    }
}