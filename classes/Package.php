<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/8/16
 * Time: 12:41 PM
 */
class Package implements JsonSerializable
{
	private $id;
	private $name;
	private $code;
	private $price;
	private $active;
	private $expiration;
	private $category;
	private $createDate;
	private $createUser;
	private $items;
	
	/**
	 * Package constructor.
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
	 * @return Package
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
	 *
	 * @return Package
	 */
	public function setName($name)
	{
		$this->name = $name;
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
	 * @return Package
	 */
	public function setCode($code)
	{
		$this->code = $code;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getPrice()
	{
		return $this->price;
	}
	
	/**
	 * @param mixed $price
	 *
	 * @return Package
	 */
	public function setPrice($price)
	{
		$this->price = $price;
		return $this;
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
	 *
	 * @return Package
	 */
	public function setActive($active)
	{
		$this->active = $active;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getExpiration()
	{
		return $this->expiration;
	}
	
	/**
	 * @param mixed $expiration
	 *
	 * @return Package
	 */
	public function setExpiration($expiration)
	{
		$this->expiration = $expiration;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCategory()
	{
		return $this->category;
	}
	
	/**
	 * @param mixed $category
	 *
	 * @return Package
	 */
	public function setCategory($category)
	{
		$this->category = $category;
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
	 * @return Package
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
	 * @return Package
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
	
	/**
	 * @return mixed
	 */
	public function getItems()
	{
		return $this->items;
	}
	
	/**
	 * @param mixed $items
	 *
	 * @return Package
	 */
	public function setItems($items)
	{
		$this->items = $items;
		return $this;
	}
	
	function add($pdo = null)
	{
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
			$code = ("PKG" . generateBillableItemCode('package', $pdo));
			$this->setCode($code);
			
			$active = var_export($this->getActive(), true);
			$expiration = $this->getExpiration() ? quote_esc_str($this->getExpiration()) : 'NOW()';
			$categoryId = $this->getCategory() ? $this->getCategory()->getId() : 'NULL';
			$name = !is_blank($this->getName()) ? quote_esc_str($this->getName()) : 'NULL';
			$createDate = !is_blank($this->getCreateDate()) ? quote_esc_str($this->getCreateDate()) : 'NOW()';
			$createUser = $this->getCreateUser() ? $this->getCreateUser()->getId() : $_SESSION['staffID'];
			$price = $this->getPrice() ? $this->getPrice() : 0;
			$code = quote_esc_str($code);
			$sql = "INSERT INTO package SET `billing_code`=$code, active=$active, expiration=$expiration, category_id=$categoryId, `name`=$name, create_date=$createDate, create_user_id=$createUser, price=$price";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$this->setId($pdo->lastInsertId());
				$insureBI = new InsuranceBillableItem();
				$insureBI->setItem($this);
				$insureBI->setItemDescription(escape($this->getName()));
				$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(24, $pdo));
				$insureBI->setClinic(new Clinic(1));
				
				$insBI = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($insureBI, $pdo);
				if ($insBI == NULL) {
					error_log('failed to add a billable item');
					$pdo->rollBack();
					return NULL;
				}
				$insureIC = new InsuranceItemsCost();
				$insureIC->setItem($this);
				$insureIC->setSellingPrice ($this->getPrice());
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
	
	
	function update($pdo = null)
	{
		@session_start();
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
		
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$pdo->beginTransaction();
			$active = var_export($this->getActive(), true);
			$expiration = $this->getExpiration() ? quote_esc_str($this->getExpiration()) : 'NOW()';
			$categoryId = $this->getCategory() ? $this->getCategory()->getId() : 'NULL';
			$name = !is_blank($this->getName()) ? quote_esc_str($this->getName()) : 'NULL';
			$createDate = !is_blank($this->getCreateDate()) ? quote_esc_str($this->getCreateDate()) : 'NOW()';
			$createUser = $this->getCreateUser() ? $this->getCreateUser()->getId() : $_SESSION['staffID'];
			$price = $this->getPrice() ? $this->getPrice() : 0;
			$sql = "UPDATE package SET active=$active, expiration=$expiration, category_id=$categoryId, `name`=$name, create_date=$createDate, create_user_id=$createUser, price=$price WHERE id={$this->getId()}";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				
				$clinic = new Clinic(1);
				$insureBI = (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($this->getCode(), TRUE, $pdo);
				$insureBI->setItemDescription($this->getName());
				$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(24, $pdo));
				$insureBI->setClinic($clinic);
				$insBI = (new InsuranceBillableItemDAO())->updateBillableItem($insureBI, $pdo);
				if ($insBI == null) {
					$pdo->rollBack();
					$stmt = null;
					return null;
				}
				
				
				$insureIC = new InsuranceItemsCost();
				$insureIC->setItem($this);
				$insureIC->setSellingPrice($this->getPrice());
				$insureSch = new InsuranceScheme(1);
				$insureIC->setInsuranceScheme($insureSch);
				$insureIC->setClinic($clinic);
				$insIC = (new InsuranceItemsCostDAO())->updateInsuranceItemCost($insureIC, $pdo);
				if ($insIC == null) {
					$pdo->rollBack();
					$stmt = null;
					return null;
				}
				$pdo->commit();
				$stmt = null;
				return $this;
			}
			return null;
		} catch (PDOException $exception) {
			errorLog($exception);
			return null;
		}
	}
	
}