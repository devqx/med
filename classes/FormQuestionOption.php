<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/16/15
 * Time: 10:54 AM
 */
class FormQuestionOption implements JsonSerializable
{
    private $id;
    private $questionTemplate;
    private $label;
    private $dataType;
    private $relation;

    /**
     * HistoryTemplateData constructor.
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
     * @return FormQuestionOption
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuestionTemplate()
    {
        return $this->questionTemplate;
    }

    /**
     * @param mixed $questionTemplate
     * @return FormQuestionOption
     */
    public function setQuestionTemplate($questionTemplate)
    {
        $this->questionTemplate = $questionTemplate;
        return $this;
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
     * @return FormQuestionOption
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param mixed $dataType
     * @return FormQuestionOption
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * @param mixed $relation
     * @return FormQuestionOption
     */
    public function setRelation($relation)
    {
        $this->relation = $relation;
        return $this;
    }

    function renderType($name, $defaultValue=NULL){
        $val = (!is_null($defaultValue))? ' value="'.$defaultValue.'"': '';

        switch($this->dataType){
            case 'text':
                return '<input type="text" name="'.$name.'">';
            case 'radio':
                return '<input type="radio" name="'.$name.'"'.$val.'>';
            case 'boolean':
                return '<input type="checkbox" name="'.$name.'">';
            case 'float':
                return '<input type="number" step="any" name="'.$name.'">';
            case 'integer':
                return '<input type="number" name="'.$name.'">';
            case 'date':
                return '<input type="date" name="'.$name.'">';
            case 'selection':
                $options = explode("|", $this->relation);
                $str = '';
                foreach($options as $option){
                    $str.= '<option value="'.$option.'">'.$option.'</option>';
                }
                return '<select name="'.$name.'" data-placeholder=" select an option "><option>----</option>'.$str.'</select>';
        }
        return '<input type="text" name="'.$name.'">';
    }
}