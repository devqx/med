<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/6/17
 * Time: 11:21 AM
 */

class EmbryoAssessmentData implements JsonSerializable
{
	private $id;
	private $assessment;
	private $embryoNumber;
	private $cellNumber;
	private $quality;
	private $morula;
	private $blastocyst;
	private $state;
	private $stage;
	
	/**
	 * EmbryoAssessmentData constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
	public function jsonSerialize()
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
	 * @return EmbryoAssessmentData
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAssessment()
	{
		return $this->assessment;
	}
	
	/**
	 * @param mixed $assessment
	 *
	 * @return EmbryoAssessmentData
	 */
	public function setAssessment($assessment)
	{
		$this->assessment = $assessment;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getEmbryoNumber()
	{
		return $this->embryoNumber;
	}
	
	/**
	 * @param mixed $embryoNumber
	 *
	 * @return EmbryoAssessmentData
	 */
	public function setEmbryoNumber($embryoNumber)
	{
		$this->embryoNumber = $embryoNumber;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCellNumber()
	{
		return $this->cellNumber;
	}
	
	/**
	 * @param mixed $cellNumber
	 *
	 * @return EmbryoAssessmentData
	 */
	public function setCellNumber($cellNumber)
	{
		$this->cellNumber = $cellNumber;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getQuality()
	{
		return $this->quality;
	}
	
	/**
	 * @param mixed $quality
	 *
	 * @return EmbryoAssessmentData
	 */
	public function setQuality($quality)
	{
		$this->quality = $quality;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getMorula()
	{
		return $this->morula;
	}
	
	/**
	 * @param mixed $morula
	 *
	 * @return EmbryoAssessmentData
	 */
	public function setMorula($morula)
	{
		$this->morula = $morula;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getBlastocyst()
	{
		return $this->blastocyst;
	}
	
	/**
	 * @param mixed $blastocyst
	 *
	 * @return EmbryoAssessmentData
	 */
	public function setBlastocyst($blastocyst)
	{
		$this->blastocyst = $blastocyst;
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
	 *
	 * @return EmbryoAssessmentData
	 */
	public function setState($state)
	{
		$this->state = $state;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getStage()
	{
		return $this->stage;
	}
	
	/**
	 * @param mixed $stage
	 *
	 * @return EmbryoAssessmentData
	 */
	public function setStage($stage)
	{
		$this->stage = $stage;
		return $this;
	}
	
	
	function add($pdo)
	{
		//require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			//$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$assessment = $this->getAssessment()->getId();
			$embryoNum =  $this->getEmbryoNumber() ? parseNumber($this->getEmbryoNumber()) : 'NULL';
			$cellNum = $this->getCellNumber() ? parseNumber($this->getCellNumber()) : 'NULL';
			$quality =  $this->getQuality() ? quote_esc_str($this->getQuality()) : 'NULL';
			$morula = $this->getMorula() ? quote_esc_str($this->getMorula()) : 'NULL';
			$blasto =  $this->getBlastocyst()? quote_esc_str($this->getBlastocyst()) : 'NULL';
			$em_stage = $this->getStage() ? $this->getStage() : 'NULL';
			$state =  $this->getState() ?  $this->getState() : 'NULL';
			$sql = "INSERT INTO ivf_embryo_assessment_data (ivf_embryo_assessment_id, embryo_no, cell_no, quality, morula, blastocyst, state, stage) VALUES ($assessment, $embryoNum, ". $cellNum . ", ". $quality . ", $morula, $blasto, $state, '". $em_stage ."')";
			
			error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}