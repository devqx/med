<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DrugGeneric 
 *
 * @author pauldic
 */
class DrugGeneric implements JsonSerializable{
    private $id;
    private $name;
    private $categories;
    private $body_systems;
    private $weight;
    private $form;
    private $description;
    private $low_stock_level;
    private $active;
    private $service_centre_id;

    function __construct($id=NULL) {
        $this->id = $id;
    }

    /**
     * @param mixed $body_systems
     */
    public function setBodySystems($body_systems)
    {
        $this->body_systems = $body_systems;
    }

    /**
     * @return mixed
     */
    public function getBodySystems()
    {
        return $this->body_systems;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return mixed
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param mixed $form
     */
    public function setForm($form)
    {
        $this->form = $form;
    }

    /**
     * @return mixed
     */
    public function getForm()
    {
        return $this->form;
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
     * @param mixed $low_stock_level
     */
    public function setLowStockLevel($low_stock_level)
    {
        $this->low_stock_level = $low_stock_level;
    }

    /**
     * @return mixed
     */
    public function getLowStockLevel()
    {
        return $this->low_stock_level;
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
     * @param mixed $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return mixed
     */
    public function getServiceCentreId()
    {
        return $this->service_centre_id;
    }

    /**
     * @param mixed $service_centre_id
     * @return DrugGeneric
     */
    public function setServiceCentreId($service_centre_id)
    {
        $this->service_centre_id = $service_centre_id;
        return $this;
    }



    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }


}
