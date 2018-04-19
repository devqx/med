<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/9/17
 * Time: 9:24 AM
 */
class SFormAnswerDAO
{
	private $conn = null;
	
	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/SFormAnswer.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo = null)
	{
		if ($id === null || is_blank($id)) {
			return null;
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM sform_answer WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$encounter = !is_blank($row['encounter_id']) ? new Encounter($row['encounter_id']) : null;
				$patient = !is_blank($row['patient_id']) ? new PatientDemograph($row['patient_id']) : null;
				$question = (new SFormQuestionDAO())->get($row['question_id'], $pdo);
				$timeEntered = $row['time_entered'];
				$user = new StaffDirectory($row['create_user_id']);
				$answers = [];
				return (new SFormAnswer($row['id']))->setPatient($patient)->setEncounter($encounter)->setQuestion($question)->setAnswerTime($timeEntered)->setCreateUser($user)->setAnswers($answers);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function forEncounter($encounterId, $pdo = null)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM sform_answer sa LEFT JOIN sform_question sq ON sq.id=sa.question_id LEFT JOIN sform sf ON sf.id=sq.sform_id WHERE encounter_id=$encounterId ORDER BY sf.id, sq.id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
}