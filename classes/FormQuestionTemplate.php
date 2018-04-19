<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/16/15
 * Time: 10:25 AM
 */
class FormQuestionTemplate implements JsonSerializable
{
    private $id;
    private $label;
    private $question;

//    static $obstetrics_template_id = 4;
//    static $gynaecological_template_id = 3;

    /**
     * HistoryTemplate constructor.
     * @param $id
     */
    public function __construct($id=NULL)
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
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param mixed $question
     */
    public function setQuestion($question)
    {
        $this->question = $question;
    }


    function jsonSerialize()
    {
        return (object)get_object_vars($this);
    }


}