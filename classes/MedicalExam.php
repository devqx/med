<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/3/16
 * Time: 2:06 PM
 */
class MedicalExam implements JsonSerializable
{
	private $id;
	private $code;
	private $name;
	private $labs;
	private $imagings;
	private $procedures;
	private $basePrice;

	/**
	 * MedicalExam constructor.
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }

	/**
	 * @return null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param null $id
	 * @return MedicalExam
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param mixed $code
	 * @return MedicalExam
	 */
	public function setCode($code)
	{
		$this->code = $code;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param mixed $name
	 * @return MedicalExam
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBasePrice()
	{
		return $this->basePrice;
	}

	/**
	 * @param mixed $basePrice
	 * @return MedicalExam
	 */
	public function setBasePrice($basePrice)
	{
		$this->basePrice = $basePrice;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLabs()
	{
		return $this->labs;
	}

	/**
	 * @param mixed $labs
	 * @return MedicalExam
	 */
	public function setLabs($labs)
	{
		$this->labs = $labs;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getImagings()
	{
		return $this->imagings;
	}

	/**
	 * @param mixed $imagings
	 * @return MedicalExam
	 */
	public function setImagings($imagings)
	{
		$this->imagings = $imagings;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getProcedures()
	{
		return $this->procedures;
	}

	/**
	 * @param mixed $procedures
	 * @return MedicalExam
	 */
	public function setProcedures($procedures)
	{
		$this->procedures = $procedures;
		return $this;
	}

	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}

	public function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';

		$name = escape($this->getName());
		try {
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO(): $pdo;
			$pdo->beginTransaction();
			$code = "ME" . generateBillableItemCode('medical_exam', $pdo);
			$this->setCode($code);

			$labs = !is_blank($this->getLabs()) ? quote_esc_str($this->getLabs()) : "NULL";
			$procedures = !is_blank($this->getProcedures()) ? quote_esc_str($this->getProcedures()) : "NULL";
			$imagings = !is_blank($this->getImagings()) ? quote_esc_str($this->getImagings()) : "NULL";

			$sql = "INSERT INTO medical_exam (`name`, billing_code, labs, procedures, imagings) VALUES ('$name', '$code', $labs, $procedures, $imagings)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$this->setId($pdo->lastInsertId());

				$insureBI = new InsuranceBillableItem();
				$insureBI->setItem($this);
				$insureBI->setItemDescription($this->getName());
				$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(12, $pdo));
				$insureBI->setClinic( new Clinic(1));

				$insBI = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($insureBI, $pdo);
				if ($insBI == NULL) {
					$pdo->rollBack();
					return NULL;
				}
				$insureIC = new InsuranceItemsCost();
				$insureIC->setItem($this);
				$insureIC->setSellingPrice ($this->getBasePrice());
				$insureIC->setInsuranceScheme(new InsuranceScheme(1));
				$insureIC->setClinic(new Clinic(1));
				$insIC = (new InsuranceItemsCostDAO())->addInsuranceItemsCost($insureIC, $pdo);
				if ($insIC == NULL) {
					$pdo->rollBack();
					return NULL;
				}
				$pdo->commit();
				return $this;
			}
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}

	function update($pdo=NULL){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/Clinic.php';
		require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/InsuranceBillableItemDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/BillSourceDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/InsuranceItemsCostDAO.php';

		try {
			$pdo = $pdo==NULL? (new MyDBConnector())->getPDO() : $pdo;
			$pdo->beginTransaction();

			$labs = !is_blank($this->getLabs()) ? quote_esc_str($this->getLabs()) : "NULL";
			$procedures = !is_blank($this->getProcedures()) ? quote_esc_str($this->getProcedures()) : "NULL";
			$imagings = !is_blank($this->getImagings()) ? quote_esc_str($this->getImagings()) : "NULL";

			$sql = "UPDATE medical_exam SET `name` = '".escape($this->getName())."', billing_code = '".$this->getCode()."', labs=$labs, procedures=$procedures, imagings=$imagings WHERE id = ".$this->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$insureBI = (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($this->getCode(), TRUE, $pdo);
			$insureBI->setItemDescription($this->getName());
			$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(12, $pdo));
			$insureBI->setClinic(new Clinic(1));
			$insBI = (new InsuranceBillableItemDAO())->updateBillableItem($insureBI, $pdo);

			if ($insBI == NULL) {
				$pdo->rollBack();
				return NULL;
			}
			$insureIC = (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($this->getCode(), 1, FALSE, TRUE, $pdo);
			$insureIC->selling_price = ($this->getBasePrice());
			$insIC = (new InsuranceItemsCostDAO())->updateInsuranceItemCost($insureIC, $pdo);

			if ($insIC == NULL) {
				$pdo->rollBack();
				return NULL;
			}
			$pdo->commit();
			return $this;
		} catch(Exception $e) {
			errorLog($e);
			return NULL;
		}
	}
}