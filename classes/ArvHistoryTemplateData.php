<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/16/15
 * Time: 10:54 AM
 */
class ArvHistoryTemplateData implements JsonSerializable
{
    private $id;
    private $historyTemplate;
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
    public function getHistoryTemplate()
    {
        return $this->historyTemplate;
    }

    /**
     * @param mixed $historyTemplate
     */
    public function setHistoryTemplate($historyTemplate)
    {
        $this->historyTemplate = $historyTemplate;
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
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param mixed $dataType
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
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
     * @return ArvHistoryTemplateData
     */
    public function setRelation($relation)
    {
        $this->relation = $relation;
        return $this;
    }



    function renderType($name){
        switch($this->dataType){
            case 'text':
                return '<input type="text" name="'.$name.'">';
            case 'radio':
                return '<input type="radio" name="'.$name.'">';
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
                return '<select name="'.$name.'" data-placeholder=" select an option "><option></option>'.$str.'</select>';
        }
        return '<input type="text" name="'.$name.'">';
    }
}