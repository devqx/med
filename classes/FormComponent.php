<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/3/16
 * Time: 10:12 AM
 */
class FormComponent implements JsonSerializable
{
    private $id;
    private $form;
    private $formQuestion;

    /**
     * FormComponent constructor.
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
     * @return FormComponent
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param mixed $form
     * @return FormComponent
     */
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormQuestion()
    {
        return $this->formQuestion;
    }

    /**
     * @param mixed $formQuestion
     * @return FormComponent
     */
    public function setFormQuestion($formQuestion)
    {
        $this->formQuestion = $formQuestion;
        return $this;
    }


}