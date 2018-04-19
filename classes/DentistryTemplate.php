<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/29/15
 * Time: 12:56 PM
 */

class DentistryTemplate implements JsonSerializable {
    private $id;
    private $category;
    private $title;
    private $bodyPart;

    function __construct($id=NULL){
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
     * @return DentistryTemplate
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     * @return DentistryTemplate
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return DentistryTemplate
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBodyPart()
    {
        return $this->bodyPart;
    }

    /**
     * @param mixed $bodyPart
     * @return DentistryTemplate
     */
    public function setBodyPart($bodyPart)
    {
        $this->bodyPart = $bodyPart;
        return $this;
    }
    

    function jsonSerialize(){
        return (object)get_object_vars($this);
    }
}