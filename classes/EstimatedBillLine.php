<?php
/**
 * Created by PhpStorm.
 * User: nnamdi
 * Date: 4/16/17
 * Time: 4:05 PM
 */

class EstimatedBillLine implements JsonSerializable{

    private $id;
    private $estimated_bill_id;
    private $service_id;
    private $unit_price;
    private $item_description;
    private $item_cost_id;
    private $service_description;
    private $item_code;
    private $quantity;
    private $item_insurance_id;
    private $estimated_bill;


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
    public function getEstimatedBillId()
    {
        return $this->estimated_bill_id;
    }

    /**
     * @param mixed $estimated_bill_id
     */
    public function setEstimatedBillId($estimated_bill_id)
    {
        $this->estimated_bill_id = $estimated_bill_id;
    }

    /**
     * @return mixed
     */
    public function getServiceId()
    {
        return $this->service_id;
    }

    /**
     * @param mixed $service_id
     */
    public function setServiceId($service_id)
    {
        $this->service_id = $service_id;
    }

    /**
     * @return mixed
     */
    public function getUnitPrice()
    {
        return $this->unit_price;
    }

    /**
     * @param mixed $unit_price
     */
    public function setUnitPrice($unit_price)
    {
        $this->unit_price = $unit_price;
    }

    /**
     * @return mixed
     */
    public function getItemDescription()
    {
        return $this->item_description;
    }

    /**
     * @param mixed $item_description
     */
    public function setItemDescription($item_description)
    {
        $this->item_description = $item_description;
    }

    /**
     * @return mixed
     */
    public function getItemCostId()
    {
        return $this->item_cost_id;
    }

    /**
     * @param mixed $item_cost_id
     */
    public function setItemCostId($item_cost_id)
    {
        $this->item_cost_id = $item_cost_id;
    }

    /**
     * @return mixed
     */
    public function getServiceDescription()
    {
        return $this->service_description;
    }

    /**
     * @param mixed $service_description
     */
    public function setServiceDescription($service_description)
    {
        $this->service_description = $service_description;
    }

    /**
     * @return mixed
     */
    public function getItemCode()
    {
        return $this->item_code;
    }

    /**
     * @param mixed $item_code
     */
    public function setItemCode($item_code)
    {
        $this->item_code = $item_code;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return mixed
     */
    public function getItemInsuranceId()
    {
        return $this->item_insurance_id;
    }

    /**
     * @param mixed $item_insurance_id
     */
    public function setItemInsuranceId($item_insurance_id)
    {
        $this->item_insurance_id = $item_insurance_id;
    }

    /**
     * @return mixed
     */
    public function getEstimatedBill()
    {
        return $this->estimated_bill;
    }

    /**
     * @param mixed $estimated_bill
     */
    public function setEstimatedBill($estimated_bill)
    {
        $this->estimated_bill = $estimated_bill;
    }



    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }
}