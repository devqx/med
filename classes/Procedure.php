<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/31/14
 * Time: 3:49 PM
 */
class Procedure implements JsonSerializable
{
	private $id;
	private $name;
	private $code;
	private $icd_code;
	private $description;
	private $basePrice;
	private $priceTheatre;
	private $priceSurgeon;
	private $priceAnaesthesia;
	private $category;

	public static $desc;

	function __construct($id = null)
	{
		$this->id = $id;
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		if (Clinic::$editStyleByAdd) {
			$this::$desc = ["Surgical Fee: ", "Anaesthetist Fee: ", "Anaesthesia: ", "Theatre Cost: "];
		} else {
			$this::$desc = ["Base Price: ", "Surgeon Price: ", "Anaesthesia: ", "Theatre Cost: "];
		}
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
	 * @return Procedure
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
	 * @return Procedure
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
	 * @return Procedure
	 */
	public function setCode($code)
	{
		$this->code = $code;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getIcdCode()
	{
		return $this->icd_code;
	}

	/**
	 * @param mixed $icd_code
	 * @return Procedure
	 */
	public function setIcdCode($icd_code)
	{
		$this->icd_code = $icd_code;
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
	 * @return Procedure
	 */
	public function setDescription($description)
	{
		$this->description = $description;
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
	 * @return Procedure
	 */
	public function setBasePrice($basePrice)
	{
		$this->basePrice = $basePrice;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPriceTheatre()
	{
		return $this->priceTheatre;
	}

	/**
	 * @param mixed $priceTheatre
	 * @return Procedure
	 */
	public function setPriceTheatre($priceTheatre)
	{
		$this->priceTheatre = $priceTheatre;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPriceSurgeon()
	{
		return $this->priceSurgeon;
	}

	/**
	 * @param mixed $priceSurgeon
	 * @return Procedure
	 */
	public function setPriceSurgeon($priceSurgeon)
	{
		$this->priceSurgeon = $priceSurgeon;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPriceAnaesthesia()
	{
		return $this->priceAnaesthesia;
	}

	/**
	 * @param mixed $priceAnaesthesia
	 * @return Procedure
	 */
	public function setPriceAnaesthesia($priceAnaesthesia)
	{
		$this->priceAnaesthesia = $priceAnaesthesia;
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
	 * @return Procedure
	 */
	public function setCategory($category)
	{
		$this->category = $category;
		return $this;
	}


	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}


} 