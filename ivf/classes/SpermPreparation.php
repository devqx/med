<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/6/16
 * Time: 11:21 AM
 */
class SpermPreparation implements JsonSerializable
{
	private $id;
	private $instance;
	private $timeEntered;
	private $user;
	private $source;
	private $state;
	private $donorCode;
	private $procedure;
	private $abstinenceDays;
	private $collectionDate;
	private $preAnalysisReport;
	private $postAnalysisReport;
	private $witnesses;
	private $productionTime;
	private $analysisTime;
	private $preparationMethod;

	/**
	 * SpermCollection constructor.
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
	 * @return SpermPreparation
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getInstance()
	{
		return $this->instance;
	}

	/**
	 * @param mixed $instance
	 * @return SpermPreparation
	 */
	public function setInstance($instance)
	{
		$this->instance = $instance;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTimeEntered()
	{
		return $this->timeEntered;
	}

	/**
	 * @param mixed $timeEntered
	 * @return SpermPreparation
	 */
	public function setTimeEntered($timeEntered)
	{
		$this->timeEntered = $timeEntered;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param mixed $user
	 * @return SpermPreparation
	 */
	public function setUser($user)
	{
		$this->user = $user;
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
	 * @return SpermPreparation
	 */
	public function setSource($source)
	{
		$this->source = $source;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @param mixed $state
	 * @return SpermPreparation
	 */
	public function setState($state)
	{
		$this->state = $state;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDonorCode()
	{
		return $this->donorCode;
	}

	/**
	 * @param mixed $donorCode
	 * @return SpermPreparation
	 */
	public function setDonorCode($donorCode)
	{
		$this->donorCode = $donorCode;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getProcedure()
	{
		return $this->procedure;
	}

	/**
	 * @param mixed $procedure
	 * @return SpermPreparation
	 */
	public function setProcedure($procedure)
	{
		$this->procedure = $procedure;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAbstinenceDays()
	{
		return $this->abstinenceDays;
	}

	/**
	 * @param mixed $abstinenceDays
	 * @return SpermPreparation
	 */
	public function setAbstinenceDays($abstinenceDays)
	{
		$this->abstinenceDays = $abstinenceDays;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCollectionDate()
	{
		return $this->collectionDate;
	}

	/**
	 * @param mixed $collectionDate
	 * @return SpermPreparation
	 */
	public function setCollectionDate($collectionDate)
	{
		$this->collectionDate = $collectionDate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPreAnalysisReport()
	{
		return $this->preAnalysisReport;
	}

	/**
	 * @param mixed $preAnalysisReport
	 * @return SpermPreparation
	 */
	public function setPreAnalysisReport($preAnalysisReport)
	{
		$this->preAnalysisReport = $preAnalysisReport;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getWitnesses()
	{
		return $this->witnesses;
	}

	/**
	 * @param mixed $witnesses
	 * @return SpermPreparation
	 */
	public function setWitnesses($witnesses)
	{
		$this->witnesses = $witnesses;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPostAnalysisReport()
	{
		return $this->postAnalysisReport;
	}

	/**
	 * @param mixed $postAnalysisReport
	 * @return SpermPreparation
	 */
	public function setPostAnalysisReport($postAnalysisReport)
	{
		$this->postAnalysisReport = $postAnalysisReport;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getProductionTime()
	{
		return $this->productionTime;
	}

	/**
	 * @param mixed $productionTime
	 * @return SpermPreparation
	 */
	public function setProductionTime($productionTime)
	{
		$this->productionTime = $productionTime;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAnalysisTime()
	{
		return $this->analysisTime;
	}

	/**
	 * @param mixed $analysisTime
	 * @return SpermPreparation
	 */
	public function setAnalysisTime($analysisTime)
	{
		$this->analysisTime = $analysisTime;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPreparationMethod()
	{
		return $this->preparationMethod;
	}

	/**
	 * @param mixed $preparationMethod
	 * @return SpermPreparation
	 */
	public function setPreparationMethod($preparationMethod)
	{
		$this->preparationMethod = $preparationMethod;
		return $this;
	}


	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$instance = $this->getInstance() ? $this->getInstance()->getId() : 'NULL';
			$timeEntered = $this->getTimeEntered() ? quote_esc_str($this->getTimeEntered()) : 'null';
			$userId = $this->getUser() ? $this->getUser()->getId() : 'null';
			$collectionDate = $this->getCollectionDate() ? quote_esc_str($this->getCollectionDate()) : 'null';

			$source = $this->getSource() ? $this->getSource()->getId() : 'null';
			$state = $this->getState() ? $this->getState()->getId() : 'null';
			$donorCode = !is_blank($this->getDonorCode()) ? quote_esc_str($this->getDonorCode()) : 'null';
			$procedure = $this->getProcedure() ? $this->getProcedure()->getId() : 'null';
			$abstinence = !is_blank($this->getAbstinenceDays()) ? $this->getAbstinenceDays() : 0;

			$witnesses = count($this->getWitnesses())>0 ? implode(',',$this->getWitnesses()) : 'NULL';
			$preAnalysis = !is_blank($this->getPreAnalysisReport()) ? quote_esc_str($this->getPreAnalysisReport()) : 'null';
			$postAnalysis = !is_blank($this->getPostAnalysisReport()) ? quote_esc_str($this->getPostAnalysisReport()) : 'null';
			$productionTime = $this->getProductionTime() ? quote_esc_str($this->getProductionTime()) : 'null';
			$analysisTime = $this->getAnalysisTime() ? quote_esc_str($this->getAnalysisTime()) : 'null';
			$preparationMethod = !is_blank($this->getPreparationMethod()) ? quote_esc_str($this->getPreparationMethod()) : 'null';
			$sql = "INSERT INTO ivf_sperm_collection (instance_id, time_entered, user_id, source_id, state_id, donor_code, procedure_id, abstinence_days, collection_date, witness_ids, analysis_post_report, analysis_pre_report, production_time, analysis_time, preparation_method) VALUES ($instance, $timeEntered, $userId, $source, $state, $donorCode, $procedure, $abstinence, $collectionDate, $witnesses, $postAnalysis, $preAnalysis, $productionTime, $analysisTime, $preparationMethod)";

			//error_log($sql  );
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
}