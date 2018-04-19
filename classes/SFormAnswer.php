<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/7/17
 * Time: 12:26 PM
 */
class SFormAnswer implements JsonSerializable
{
	private $id;
	private $encounter;
	private $patient;
	private $question;
	private $answers;
	private $answerTime;
	private $createUser;
	
	/**
	 * SFormAnswer constructor.
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
	 * @return SFormAnswer
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getEncounter()
	{
		return $this->encounter;
	}
	
	/**
	 * @param mixed $encounter
	 *
	 * @return SFormAnswer
	 */
	public function setEncounter($encounter)
	{
		$this->encounter = $encounter;
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
	 * @return SFormAnswer
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getQuestion()
	{
		return $this->question;
	}
	
	/**
	 * @param mixed $question
	 *
	 * @return SFormAnswer
	 */
	public function setQuestion($question)
	{
		$this->question = $question;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAnswers()
	{
		return $this->answers;
	}
	
	/**
	 * @param mixed $answers
	 *
	 * @return SFormAnswer
	 */
	public function setAnswers($answers)
	{
		$this->answers = $answers;
		return $this;
	}
	
	
	/**
	 * @return mixed
	 */
	public function getAnswerTime()
	{
		return $this->answerTime;
	}
	
	/**
	 * @param mixed $answerTime
	 *
	 * @return SFormAnswer
	 */
	public function setAnswerTime($answerTime)
	{
		$this->answerTime = $answerTime;
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
	 * @return SFormAnswer
	 */
	public function setCreateUser($createUser)
	{
		$this->createUser = $createUser;
		return $this;
	}
	
	function add($pdo = null)
	{
		@session_start();
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		
		$patient = $this->getPatient()->getId();
		$encounter = $this->getEncounter()->getId();
		$question = $this->getQuestion()->getId();
		$timeEntered = $this->getAnswerTime() ? quote_esc_str($this->getAnswerTime()) : 'NOW()';
		$user = $this->getCreateUser() ? $this->getCreateUser()->getId() : $_SESSION['staffID'];
		
		$sql = "INSERT IGNORE INTO sform_answer SET patient_id=$patient, encounter_id=$encounter, question_id=$question, time_entered=$timeEntered, create_user_id=$user";
		//error_log($sql);
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				$this->setId($pdo->lastInsertId());
				
				foreach ($this->getAnswers() as $answer){
					//$answer = new SFormAnswerOption();
					$answer->setAnswer($this)->add($pdo);
				}
				
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}