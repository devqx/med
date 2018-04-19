<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/13/16
 * Time: 9:23 AM
 */
class PackageSubscription implements JsonSerializable
{
	private $id;
	private $patient;
	private $package;
	private $dateSubscribed;
	private $active;
	private $createUser;
	
	/**
	 * PackageSubscription constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
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
	 *
	 * @return PackageSubscription
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
	 *
	 * @return PackageSubscription
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getPackage()
	{
		return $this->package;
	}
	
	/**
	 * @param mixed $package
	 *
	 * @return PackageSubscription
	 */
	public function setPackage($package)
	{
		$this->package = $package;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getDateSubscribed()
	{
		return $this->dateSubscribed;
	}
	
	/**
	 * @param mixed $dateSubscribed
	 *
	 * @return PackageSubscription
	 */
	public function setDateSubscribed($dateSubscribed)
	{
		$this->dateSubscribed = $dateSubscribed;
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
	 * @return PackageSubscription
	 */
	public function setActive($active)
	{
		$this->active = $active;
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
	 * @return PackageSubscription
	 */
	public function setCreateUser($createUser)
	{
		$this->createUser = $createUser;
		return $this;
	}
	
	function add($pdo=null)
	{
		@session_start();
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PackageToken.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PackageDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$canCommit = !$pdo->inTransaction();
			
			try {$pdo->beginTransaction();}catch (Exception $exception){}
			$patientId = $this->getPatient() ? $this->getPatient()->getId() : 'NULL';
			$packageId = $this->getPackage() ? $this->getPackage()->getId() : 'NULL';
			$dateSubscribed = $this->getDateSubscribed() ? quote_esc_str($this->getDateSubscribed()) : 'NOW()';
			$active = $this->getActive() ? var_export($this->getActive(), true) : 'FALSE';
			$createUser = $this->getCreateUser() ? $this->getCreateUser()->getId() : $_SESSION['staffID'];
			$sql = "INSERT INTO package_subscription SET patient_id=$patientId, package_id=$packageId, date_subscribed=$dateSubscribed, active=$active, create_user=$createUser";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$this->setId($pdo->lastInsertId());
				
				$package = (new PackageDAO())->get($this->getPackage()->getId(), FALSE, $pdo);
				$patient = (new PatientDemographDAO())->getPatient($this->getPatient()->getId(), TRUE, $pdo);
				
				$bil = new Bill();
				$bil->setPatient($this->getPatient());
				$bil->setDescription("Package subscription charges: " . $package->getName());
				
				$bil->setItem($package);
				$bil->setSource((new BillSourceDAO())->findSourceById(24, $pdo));
				$bil->setTransactionType("credit");
				$bil->setAmount($package->getPrice());
				$bil->setInPatient(NULL);
				$bil->setDiscounted(null);
				$bil->setDiscountedBy(null);
				$bil->setClinic(new Clinic(1));
				$bil->setBilledTo($patient->getScheme());
				//
				$bil->setTransactionDate($this->getDateSubscribed() ? date(MainConfig::$mysqlDateTimeFormat, strtotime($this->getDateSubscribed())) : date(MainConfig::$mysqlDateTimeFormat));
				
				$bil->setReferral(null);
				$bil->setCostCentre(null);
				$bil->add(1, NULL, $pdo);
				//get the items in the package and add the tokens to the patient
				$items = (new PackageDAO())->get($packageId, TRUE, $pdo)->getItems();
				foreach ($items as $item){
					//$item = new PackageItem();
					(new PackageToken())->setPatient($this->getPatient())->setItemCode($item->getItemCode())->setOriginalQuantity($item->getQuantity())->setRemainingQuantity($item->getQuantity())->add($pdo);
				}
				if($canCommit){$pdo->commit();}
				return $this;
			}
			return null;
		}catch (PDOException $exception){
			errorLog($exception);
			return null;
		}
	}
	function update($pdo=null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$patientId = $this->getPatient() ? $this->getPatient()->getId() : 'NULL';
			$packageId = $this->getPackage() ? $this->getPackage()->getId() : 'NULL';
			$dateSubscribed = $this->getDateSubscribed() ? quote_esc_str($this->getDateSubscribed()) : 'NOW()';
			$active = $this->getActive() ? var_export($this->getActive(), true) : 'FALSE';
			$createUser = $this->getCreateUser() ? $this->getCreateUser()->getId() : $_SESSION['staffID'];
			$sql = "UPDATE package_subscription SET patient_id=$patientId, package_id=$packageId, date_subscribed=$dateSubscribed, active=$active, create_user=$createUser WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return $this;
			}
			return null;
		}catch (PDOException $exception){
			errorLog($exception);
			return null;
		}
	}
}