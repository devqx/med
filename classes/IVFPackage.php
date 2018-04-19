<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/15/16
 * Time: 2:39 PM
 */
class IVFPackage implements JsonSerializable
{
	private $id;
	private $name;
	private $amount;
	private $code;

	public function __construct($id=NULL){
		$this->id = $id;
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
	 * @return IVFPackage
	 */
	public function setId($id)
	{
		$this->id = $id;
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
	 * @return IVFPackage
	 */
	public function setName($name)
	{
		$this->name = $name;
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
	 * @return IVFPackage
	 */
	public function setAmount($amount)
	{
		$this->amount = $amount;
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
	 * @return IVFPackage
	 */
	public function setCode($code)
	{
		$this->code = $code;
		return $this;
	}

	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}

	function add($pdo=null){
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$pdo->beginTransaction();
			$code = "IVP" . generateBillableItemCode('ivf_package', $pdo);
			$this->setCode($code);
			$sql = "INSERT INTO ivf_package SET billing_code = '$code', `name`='" . escape($this->getName()) . "', amount='" . $this->getAmount() . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());

				$insureBI = new InsuranceBillableItem();
				$insureBI->setItem($this);
				$insureBI->setItemDescription(escape($this->getName()));
				$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(22, $pdo));
				$insureBI->setClinic(new Clinic(1));

				$insBI = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($insureBI, $pdo);
				if ($insBI == NULL) {
					$pdo->rollBack();
					$stmt = null;
					return NULL;
				}
				$insureIC = new InsuranceItemsCost();
				$insureIC->setItem($this);
				$insureIC->setSellingPrice ($this->getAmount());
				$insureIC->setInsuranceScheme(new InsuranceScheme(1));
				$insureIC->setClinic(new Clinic(1));
				$insIC = (new InsuranceItemsCostDAO())->addInsuranceItemsCost($insureIC, $pdo);
				if ($insIC == NULL) {
					$pdo->rollBack();
					$stmt = null;
					return NULL;
				}
				$pdo->commit();
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function update($pdo=null){
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$pdo->beginTransaction();
			$sql = "UPDATE ivf_package SET `name`='" . escape($this->getName()) . "', amount='" . $this->getAmount() . "' WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());

				$insureBI = (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($this->getCode(), TRUE, $pdo);
				$insureBI->setItem($this);
				$insureBI->setItemDescription(escape($this->getName()));
				$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(22, $pdo));
				$insureBI->setClinic(new Clinic(1));

				$insBI = (new InsuranceBillableItemDAO())->updateBillableItem($insureBI, $pdo);
				if ($insBI == NULL) {
					error_log('is it here?');
					$pdo->rollBack();
					$stmt = null;
					return NULL;
				}
				$insureIC = new InsuranceItemsCost();
				$insureIC->setItem($this);
				$insureIC->setSellingPrice ($this->getAmount());
				$insureIC->setInsuranceScheme(new InsuranceScheme(1));
				$insureIC->setClinic(new Clinic(1));
				$insIC = (new InsuranceItemsCostDAO())->updateInsuranceItemCost($insureIC, $pdo);
				if ($insIC == NULL) {
					$pdo->rollBack();
					$stmt = null;
					return NULL;
				}
				$pdo->commit();
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

}