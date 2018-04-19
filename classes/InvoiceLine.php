<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/3/15
 * Time: 4:43 PM
 */

class InvoiceLine implements JsonSerializable{

    private $id;
    private $invoice;
    private $bill;

    function __construct($id=NULL)
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
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param mixed $invoice
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return mixed
     */
    public function getBill()
    {
        return $this->bill;
    }

    /**
     * @param mixed $bill
     */
    public function setBill($bill)
    {
        $this->bill = $bill;
    }



    function jsonSerialize()
    {
        return (object)get_object_vars($this);
    }


}