<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/6/17
 * Time: 12:17 PM
 */
class DRT implements JsonSerializable
{
	private $id;
	private $code;
	private $name;
	private $basePrice;
	private $description;
	private $createDate;
	private $createUser;
	
	/**
	 * DRT constructor.
	 *
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
	 *
	 * @return DRT
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
	 *
	 * @return DRT
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
	 *
	 * @return DRT
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
	 *
	 * @return DRT
	 */
	public function setBasePrice($basePrice)
	{
		$this->basePrice = $basePrice;
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
	 *
	 * @return DRT
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCreateDate()
	{
		return $this->createDate;
	}
	
	/**
	 * @param mixed $createDate
	 *
	 * @return DRT
	 */
	public function setCreateDate($createDate)
	{
		$this->createDate = $createDate;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCreateUser()
	{
		return $this->createUser;
	}
	
	/**
	 * @param mixed $createUser
	 *
	 * @return DRT
	 */
	public function setCreateUser($createUser)
	{
		$this->createUser = $createUser;
		return $this;
	}
	
	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}
	
	function add($pdo=null){
		@session_start();
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
		
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$pdo->beginTransaction();
			$code = ("DRT" . generateBillableItemCode('drt', $pdo));
			$this->setCode($code);
			
			$name = !is_blank($this->getName()) ? quote_esc_str($this->getName()) : 'NULL';
			$createDate = !is_blank($this->getCreateDate()) ? quote_esc_str($this->getCreateDate()) : 'NOW()';
			$createUser = $this->getCreateUser() ? $this->getCreateUser()->getId() : $_SESSION['staffID'];
			$price = $this->getBasePrice() ? $this->getBasePrice() : 0;
			$description = !is_blank($this->getDescription()) ? quote_esc_str($this->getDescription()) : 'NULL';
			$code = quote_esc_str($code);
			$sql = "INSERT INTO drt SET `billing_code`=$code, `name`=$name, create_date=$createDate, create_user_id=$createUser, description=$description";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$this->setId($pdo->lastInsertId());
				$insureBI = new InsuranceBillableItem();
				$insureBI->setItem($this);
				$insureBI->setItemDescription(escape($this->getName()));
				$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(25, $pdo));
				$insureBI->setClinic(new Clinic(1));
				
				$insBI = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($insureBI, $pdo);
				if ($insBI == NULL) {
					error_log('failed to add a billable item');
					$pdo->rollBack();
					return NULL;
				}
				$insureIC = new InsuranceItemsCost();
				$insureIC->setItem($this);
				$insureIC->setSellingPrice ($price);
				$insureIC->setInsuranceScheme(new InsuranceScheme(1));
				$insureIC->setClinic(new Clinic(1));
				$insIC = (new InsuranceItemsCostDAO())->addInsuranceItemsCost($insureIC, $pdo);
				if ($insIC == NULL) {
					error_log('failed to add an item cost row');
					$pdo->rollBack();
					$stmt = null;
					return NULL;
				}
				$pdo->commit();
				return $this;
			}
			return null;
		} catch (PDOException $exception) {
			errorLog($exception);
			return null;
		}
	}
	
	function update($pdo=null){
		@session_start();
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$pdo->beginTransaction();
			$name = !is_blank($this->getName()) ? quote_esc_str($this->getName()) : 'NULL';
			$createDate = !is_blank($this->getCreateDate()) ? quote_esc_str($this->getCreateDate()) : 'NOW()';
			$createUser = $this->getCreateUser() ? $this->getCreateUser()->getId() : $_SESSION['staffID'];
			$description = !is_blank($this->getDescription()) ? quote_esc_str($this->getDescription()) : 'NULL';
			$sql = "UPDATE drt SET `name`=$name, create_date=$createDate, create_user_id=$createUser, description=$description WHERE id = " . $this->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$clinic = new Clinic();
			$clinic->setId(1);
			$insureBI = (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($this->getCode(), TRUE, $pdo);
			$insureBI->setItemDescription($this->getName());
			$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(25, $pdo));
			$insureBI->setClinic($clinic);
			$insBI = (new InsuranceBillableItemDAO())->updateBillableItem($insureBI, $pdo);
			if ($insBI == null) {
				$pdo->rollBack();
				$stmt = null;
				return null;
			}
			
			$insureIC = new InsuranceItemsCost();
			$insureIC->setItem($this);
			$insureIC->setSellingPrice($this->getBasePrice());
			$insureSch = new InsuranceScheme();
			$insureSch->setId(1);
			$insureIC->setInsuranceScheme($insureSch);
			$insureIC->setClinic($clinic);
			$insIC = (new InsuranceItemsCostDAO())->updateInsuranceItemCost($insureIC, $pdo);
			if ($insIC == null) {
				$pdo->rollBack();
				$stmt = null;
				return null;
			}
			$pdo->commit();
			return $this;
		} catch (Exception $e) {
			errorLog($e);
			return null;
		}
		
	}
}