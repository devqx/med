<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/19/15
 * Time: 12:12 PM
 */
class FormPatientQuestionAnswer implements JsonSerializable
{
    private $id;
    private $patientQuestion;
    private $formQuestionOption;
    private $value;

    /**
     * PatientHistoryData constructor.
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
     * @return ArvPatientHistoryData
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPatientQuestion()
    {
        return $this->patientQuestion;
    }

    /**
     * @param mixed $patientQuestion
     * @return ArvPatientHistoryData
     */
    public function setPatientQuestion($patientQuestion)
    {
        $this->patientQuestion = $patientQuestion;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormQuestionOption()
    {
        return $this->formQuestionOption;
    }

    /**
     * @param mixed $formQuestionOption
     * @return ArvPatientHistoryData
     */
    public function setFormQuestionOption($formQuestionOption)
    {
        $this->formQuestionOption = $formQuestionOption;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return ArvPatientHistoryData
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    
}