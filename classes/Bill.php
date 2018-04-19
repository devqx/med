<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Bill
 *
 * @author pauldic
 */
class Bill implements JsonSerializable
{
	private $id;
	private $patient;
	private $transactionDate;
	private $dueDate;
	private $description;
	private $source;
	private $sub_source;
	private $item;
	private $inPatient;
	private $transactionType = "credit";
	private $amount;
	private $copay = 0;
	private $priceType;
	private $discounted;
	private $discountedBy;
	private $invoiced;
	private $receiver;
	private $hospid;
	private $billedTo;
	private $paymentMethod;
	private $paymentReference;
	private $authCode;
	private $reviewed = TRUE;
	private $referral;

	private $voucher;

	private $costCentre;
	private $revenueAccount;

	private $itemCode;
	private $transferred;
	private $claimed;
	private $validated;
	private $quantity;
	private $unit_price;
	private $parent;
	private $cancelledOn;
	private $cancelledBy;
	private $miscellaneous;
	private $activeBill;

	function __construct($id = null)
	{
		$this->id = $id;
	}

	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}

	public function __toString()
	{
		return $this->amount . " for " . $this->description;
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
	 * @return Bill
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPatient()
	{
		return $this->patient;
	}

	/**
	 * @param mixed $patient
	 * @return Bill
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTransactionDate()
	{
		return $this->transactionDate;
	}

	/**
	 * @param mixed $transactionDate
	 * @return Bill
	 */
	public function setTransactionDate($transactionDate)
	{
		$this->transactionDate = $transactionDate;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDueDate()
	{
		return $this->dueDate;
	}
	
	/**
	 * @param mixed $dueDate
	 *
	 * @return Bill
	 */
	public function setDueDate($dueDate)
	{
		$this->dueDate = $dueDate;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param mixed $description
	 * @return Bill
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * @param mixed $source
	 * @return Bill
	 */
	public function setSource($source)
	{
		$this->source = $source;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSubSource()
	{
		return $this->sub_source;
	}

	/**
	 * @param mixed $sub_source
	 * @return Bill
	 */
	public function setSubSource($sub_source)
	{
		$this->sub_source = $sub_source;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getItem()
	{
		return $this->item;
	}

	/**
	 * @param mixed $item
	 * @return Bill
	 */
	public function setItem($item)
	{
		$this->item = $item;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getInPatient()
	{
		return $this->inPatient;
	}

	/**
	 * @param mixed $inPatient
	 * @return Bill
	 */
	public function setInPatient($inPatient)
	{
		$this->inPatient = $inPatient;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTransactionType()
	{
		return $this->transactionType;
	}

	/**
	 * @param string $transactionType
	 * @return Bill
	 */
	public function setTransactionType($transactionType)
	{
		$this->transactionType = $transactionType;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * @param mixed $amount
	 * @return Bill
	 */
	public function setAmount($amount)
	{
		$this->amount = $amount;
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getCopay()
	{
		return $this->copay;
	}
	
	/**
	 * @param int $copay
	 *
	 * @return Bill
	 */
	public function setCopay($copay)
	{
		$this->copay = $copay;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPriceType()
	{
		return $this->priceType;
	}

	/**
	 * @param mixed $priceType
	 * @return Bill
	 */
	public function setPriceType($priceType)
	{
		$this->priceType = $priceType;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDiscounted()
	{
		return $this->discounted;
	}

	/**
	 * @param mixed $discounted
	 * @return Bill
	 */
	public function setDiscounted($discounted)
	{
		$this->discounted = $discounted;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDiscountedBy()
	{
		return $this->discountedBy;
	}

	/**
	 * @param mixed $discountedBy
	 * @return Bill
	 */
	public function setDiscountedBy($discountedBy)
	{
		$this->discountedBy = $discountedBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getInvoiced()
	{
		return $this->invoiced;
	}

	/**
	 * @param mixed $invoiced
	 * @return Bill
	 */
	public function setInvoiced($invoiced)
	{
		$this->invoiced = $invoiced;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getReceiver()
	{
		return $this->receiver;
	}

	/**
	 * @param mixed $receiver
	 * @return Bill
	 */
	public function setReceiver($receiver)
	{
		$this->receiver = $receiver;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getClinic()
	{
		return $this->hospid;
	}

	/**
	 * @param mixed $hospid
	 * @return Bill
	 */
	public function setClinic($hospid)
	{
		$this->hospid = $hospid;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBilledTo()
	{
		return $this->billedTo;
	}

	/**
	 * @param mixed $billedTo
	 * @return Bill
	 */
	public function setBilledTo($billedTo)
	{
		$this->billedTo = $billedTo;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPaymentMethod()
	{
		return $this->paymentMethod;
	}

	/**
	 * @param mixed $paymentMethod
	 * @return Bill
	 */
	public function setPaymentMethod($paymentMethod)
	{
		$this->paymentMethod = $paymentMethod;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPaymentReference()
	{
		return $this->paymentReference;
	}

	/**
	 * @param mixed $paymentReference
	 * @return Bill
	 */
	public function setPaymentReference($paymentReference)
	{
		$this->paymentReference = $paymentReference;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAuthCode()
	{
		return $this->authCode;
	}

	/**
	 * @param mixed $authCode
	 * @return Bill
	 */
	public function setAuthCode($authCode)
	{
		$this->authCode = $authCode;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isReviewed()
	{
		return $this->reviewed;
	}

	/**
	 * @return boolean
	 */
	public function getReviewed()
	{
		return $this->reviewed;
	}

	/**
	 * @param boolean $reviewed
	 * @return Bill
	 */
	public function setReviewed($reviewed)
	{
		$this->reviewed = $reviewed;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getReferral()
	{
		return $this->referral;
	}

	/**
	 * @param mixed $referral
	 * @return Bill
	 */
	public function setReferral($referral)
	{
		$this->referral = $referral;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getVoucher()
	{
		return $this->voucher;
	}

	/**
	 * @param mixed $voucher
	 * @return Bill
	 */
	public function setVoucher($voucher)
	{
		$this->voucher = $voucher;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCostCentre()
	{
		return $this->costCentre;
	}

	/**
	 * @param mixed $costCentre
	 * @return Bill
	 */
	public function setCostCentre($costCentre)
	{
		$this->costCentre = $costCentre;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRevenueAccount()
	{
		return $this->revenueAccount;
	}

	/**
	 * @param mixed $revenueAccount
	 * @return Bill
	 */
	public function setRevenueAccount($revenueAccount)
	{
		$this->revenueAccount = $revenueAccount;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getItemCode()
	{
		return $this->itemCode;
	}

	/**
	 * @param mixed $itemCode
	 * @return Bill
	 */
	public function setItemCode($itemCode)
	{
		$this->itemCode = $itemCode;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTransferred()
	{
		return $this->transferred;
	}

	/**
	 * @param mixed $transferred
	 * @return Bill
	 */
	public function setTransferred($transferred)
	{
		$this->transferred = $transferred;
		return $this;
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
	 * @return Bill
	 */
	public function setUnitPrice($unit_price)
	{
		$this->unit_price = $unit_price;
		return $this;
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
	 * @return Bill
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getClaimed()
	{
		return $this->claimed;
	}

	/**
	 * @param mixed $claimed
	 * @return Bill
	 */
	public function setClaimed($claimed)
	{
		$this->claimed = $claimed;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getValidated()
	{
		return $this->validated;
	}

	/**
	 * @param mixed $validated
	 * @return Bill
	 */
	public function setValidated($validated)
	{
		$this->validated = $validated;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * @param mixed $parent
	 * @return Bill
	 */
	public function setParent($parent)
	{
		$this->parent = $parent;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCancelledOn()
	{
		return $this->cancelledOn;
	}

	/**
	 * @param mixed $cancelledOn
	 * @return Bill
	 */
	public function setCancelledOn($cancelledOn)
	{
		$this->cancelledOn = $cancelledOn;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCancelledBy()
	{
		return $this->cancelledBy;
	}

	/**
	 * @param mixed $cancelledBy
	 * @return Bill
	 */
	public function setCancelledBy($cancelledBy)
	{
		$this->cancelledBy = $cancelledBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMiscellaneous()
	{
		return $this->miscellaneous;
	}

	/**
	 * @param mixed $miscellaneous
	 * @return Bill
	 */
	public function setMiscellaneous($miscellaneous=false)
	{
		$this->miscellaneous = $miscellaneous;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getActiveBill()
	{
		return $this->activeBill;
	}
	
	/**
	 * @param mixed $activeBill
	 *
	 * @return Bill
	 */
	public function setActiveBill($activeBill)
	{
		$this->activeBill = $activeBill;
		return $this;
	}
	
	
	
	
	
	
	public function add($qty, $ipId, $pdo=null){
		return (new BillDAO())->addBill($this, $qty, $pdo, $ipId);
	}
	

	
	
	
	public function update($pdo = null)
	{
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$parent = $this->getParent() ? $this->getParent()->getId() : "NULL";
			$cancelled_on = $this->getCancelledOn() ? quote_esc_str($this->getCancelledOn()) : "NULL" ;
			$cancelled_by = $this->getCancelledBy() ? $this->getCancelledBy()->getId() : "NULL";
			$priceType = $this->getPriceType() ? quote_esc_str($this->getPriceType()) : quote_esc_str('selling_price');
			$miscellaneous = $this->getMiscellaneous() ? var_export($this->getMiscellaneous(), true) : 'FALSE';
			$active_ = $this->getActiveBill() ?   $this->getActiveBill() : 'bill_active';
			//'selling_price','followUpPrice','theatrePrice','anaesthesiaPrice','surgeonPrice'
			$sql = "UPDATE `bills` SET `patient_id`={$this->getPatient()->getId()},`transaction_date`='{$this->getTransactionDate()}',`description`='" . escape($this->getDescription()) . "',`bill_source_id`={$this->getSource()->getId()},`bill_sub_source_id`=" . ($this->getSubSource() ? $this->getSubSource()->getId() : "NULL") . ",`in_patient_id`=" . ($this->getInPatient() ? $this->getInPatient()->getId() : "NULL") . ",`transaction_type`='{$this->getTransactionType()}',`amount`={$this->getAmount()}, price_type=$priceType, `discounted`='" . ($this->getDiscounted() && $this->getDiscounted() != '' ? $this->getDiscounted() : 'no') . "',`discounted_by`=" . ($this->getDiscountedBy() ? $this->getDiscountedBy()->getId() : "NULL") . ",`invoiced`='" . ($this->getInvoiced() && $this->getInvoiced() != '' ? $this->getInvoiced() : 'no') . "',`receiver`=" . ($this->getReceiver() ? $this->getReceiver()->getId() : "NULL") . ",`auth_code`=" . ($this->getAuthCode() ? "'" . $this->getAuthCode() . "'" : "NULL") . ",`reviewed`=" . var_export((bool)$this->getReviewed(), true) . ",`transferred`=" . var_export((bool)$this->getTransferred(), true) . ",`claimed`=" . var_export((bool)$this->getClaimed(), true) . ",`validated`=" . var_export((bool)$this->getValidated(), true) . ",`voucher_id`=" . ($this->getVoucher() ? $this->getVoucher()->getId() : "NULL") . ", `hospid`=1,`billed_to`=" . ($this->getBilledTo() ? $this->getBilledTo()->getId() : "NULL") . ",`payment_method_id`=" . ($this->getPaymentMethod() ? $this->getPaymentMethod()->getId() : "NULL") . ",`payment_reference`=" . ($this->getPaymentReference() ? "'" . $this->getPaymentReference() . "'" : "NULL") . ",`referral_id`=" . ($this->getReferral() ? $this->getReferral()->getId() : "NULL") . ",`cost_centre_id`=" . ($this->getCostCentre() ? $this->getCostCentre()->getId() : "NULL") . ",`revenue_account_id`=" . ($this->getRevenueAccount() ? $this->getRevenueAccount()->getId() : "NULL") . ",`item_code`='{$this->getItemCode()}',`quantity`={$this->getQuantity()}, parent_id=$parent, cancelled_on=$cancelled_on, cancelled_by=$cancelled_by, misc=$miscellaneous, bill_active='". $active_ ."' WHERE bill_id={$this->getId()}";
			sleep(0.01);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return $this;
			}

			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}
