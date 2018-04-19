<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of InsuranceScheme
 *
 * @author pauldic
 */
class Insurer implements JsonSerializable{
    private $id;
    private $name;
    private $address;
    private $phone;
    private $email;
    private $hospital;
    private $schemes;
    private $ErpProduct;

    function __construct($id=NULL) {
        $this->id = $id;
    }


    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getAddress() {
        return $this->address;
    }

    public function setAddress($address) {
        $this->address = $address;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function setPhone($phone) {
        $this->phone = $phone;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getHospital() {
        return $this->hospital;
    }

    public function setHospital($hospital) {
        $this->hospital = $hospital;
    }

    public function getSchemes() {
        return $this->schemes;
    }

    public function setSchemes($schemes) {
        $this->schemes = $schemes;
    }
	
	/**
	 * @return mixed
	 */
	public function getErpProduct()
	{
		return $this->ErpProduct;
	}
	
	/**
	 * @param mixed $ErpProduct
	 *
	 * @return Insurer
	 */
	public function setErpProduct($ErpProduct)
	{
		$this->ErpProduct = $ErpProduct;
		return $this;
	}
    

    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }


}
