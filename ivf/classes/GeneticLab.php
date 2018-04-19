<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/13/16
 * Time: 10:17 AM
 */
class GeneticLab implements JsonSerializable
{
	private $id;
	private $name;
	private $code;
	private $basePrice;
	private $template;
	private $printLayout;
	private $qualityControls;

	/**
	 * GeneticLab constructor.
	 * @param $id
	 */
	public function __construct($id = null)
	{
		$this->id = $id;
	}

	function jsonSerialize()
	{
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
	 * @return GeneticLab
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
	 * @return GeneticLab
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
	 * @return GeneticLab
	 */
	public function setCode($code)
	{
		$this->code = $code;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTemplate()
	{
		return $this->template;
	}

	/**
	 * @param mixed $template
	 * @return GeneticLab
	 */
	public function setTemplate($template)
	{
		$this->template = $template;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPrintLayout()
	{
		return $this->printLayout;
	}

	/**
	 * @param mixed $printLayout
	 * @return GeneticLab
	 */
	public function setPrintLayout($printLayout)
	{
		$this->printLayout = $printLayout;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getQualityControls()
	{
		return $this->qualityControls;
	}

	/**
	 * @param mixed $qualityControls
	 * @return GeneticLab
	 */
	public function setQualityControls($qualityControls)
	{
		$this->qualityControls = $qualityControls;
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
	 * @return GeneticLab
	 */
	public function setBasePrice($basePrice)
	{
		$this->basePrice = $basePrice;
		return $this;
	}

	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$name = escape($this->getName());
		$template = $this->getTemplate()->getId();
		$printLayout = $this->getPrintLayout();
		$controlIds = implode(",", $this->getQualityControls());
		//the comma separated values of ids. no need to convert to object here
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$pdo->beginTransaction();
			$billingCode = 'PGD' . generateBillableItemCode('genetic_lab', $pdo);
			$this->setCode($billingCode);
			$sql = "INSERT INTO genetic_lab (`name`, billing_code, genetic_template_id, print_layout, quality_control_ids) VALUES ('$name', '$billingCode', $template, '$printLayout', '$controlIds')";

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				$insureBI = new InsuranceBillableItem();
				$insureBI->setItem($this);
				$insureBI->setItemDescription($this->getName());
				$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(21, $pdo));
				$insureBI->setClinic(new Clinic(1));
				$insBI = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($insureBI, $pdo);
				if ($insBI == null) {
					$pdo->rollBack();
					$stmt = null;
					return null;
				}

				$insureIC = new InsuranceItemsCost();
				$insureIC->setItem($this);
				$insureIC->setSellingPrice($this->getBasePrice());
				$insureIC->setInsuranceScheme(new InsuranceScheme(1));
				$insureIC->setClinic(new Clinic(1));
				$insIC = (new InsuranceItemsCostDAO())->addInsuranceItemsCost($insureIC, $pdo);
				if ($insIC == null) {
					$pdo->rollBack();
					$stmt = null;
					return null;
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

	function update($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$name = quote_esc_str($this->getName());
		$template = $this->getTemplate()->getId();
		$printLayout = quote_esc_str($this->getPrintLayout());
		$controlIds = quote_esc_str(implode(",", $this->getQualityControls()));
		//the comma separated values of ids. no need to convert to object here
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$pdo->beginTransaction();
			$sql = "UPDATE genetic_lab SET `name`=$name, genetic_template_id=$template, print_layout=$printLayout, quality_control_ids=$controlIds WHERE id={$this->getId()}";
//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				$this->setId($pdo->lastInsertId());
				$insureBI = (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($this->getCode(), FALSE, $pdo);
				$insureBI->setItem($this);
				$insureBI->setItemDescription($this->getName());
				$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(21, $pdo));
				$insureBI->setClinic(new Clinic(1));
				$insBI = (new InsuranceBillableItemDAO())->updateBillableItem($insureBI, $pdo);
				if ($insBI == null) {
					$pdo->rollBack();
					return null;
				}

				$insureIC = new InsuranceItemsCost();
				$insureIC->setItem($this);
				$insureIC->setSellingPrice($this->getBasePrice());
				$insureIC->setInsuranceScheme(new InsuranceScheme(1));
				$insureIC->setClinic(new Clinic(1));
				$insIC = (new InsuranceItemsCostDAO())->updateInsuranceItemCost($insureIC, $pdo);
				if ($insIC == null) {
					$pdo->rollBack();
					$stmt = null;
					return null;
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