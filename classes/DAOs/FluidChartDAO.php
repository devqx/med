<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/19/16
 * Time: 4:43 PM
 */
class FluidChartDAO
{
	private $conn = null;

	function __construct() {
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/ExamRoom.php';
			require_once $_SERVER ['DOCUMENT_ROOT']. '/classes/FluidRoute.php';
			require_once $_SERVER ['DOCUMENT_ROOT']. '/classes/DAOs/FluidRouteDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT']. '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT']. '/classes/FluidChart.php';
			$this->conn=new MyDBConnector();
			@session_start();
		}catch(PDOException $e) {
			exit( 'ERROR: ' . $e->getMessage() );
		}
	}

	function get($id, $pdo=NULL){
		try {
			$pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM fluid_chart WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new FluidChart($row['id']))
					->setPatient( (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo))
					//->setInPatient()
					->setRoute((new FluidRouteDAO())->get($row['route_id'], $pdo))
					->setVolume($row['vol'])
					->setType($row['type'])
					->setUser( (new StaffDirectoryDAO())->getStaff($row['user_id'], FALSE, $pdo) )
					->setTimeEntered($row['time_entered']);
			}
			return null;
		}catch(PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function forInstance($id, $start=null, $stop=null, $pdo=NULL){
		if($start == NULL){
			$dateStart = '1970-01-01 00:00';
		} else {
			$dateStart = date("Y-m-d H:i", strtotime($start));
		}
		if($stop == NULL){
			$dateStop = date("Y-m-d 23:59");
		}else {
			$dateStop = date("Y-m-d H:i", strtotime($stop));
		}

		if(isset($start, $stop)){
			//swap the dates, since mysql does not really obey negative date between`s
			//and assign in a single line. double line assignment fails
			//because by the time the later comparison is called,
			//they would be equal and things are not consistent anymore
			list($dateStart, $dateStop) = [min($dateStart, $dateStop),max($dateStart, $dateStop)];
		}
		try {
			$data = [];
			$pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM fluid_chart WHERE in_patient_id=$id AND (time_entered) BETWEEN ('$dateStart') AND ('$dateStop')";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = (new FluidChart($row['id']))
					->setPatient( (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo))
					//->setInPatient()
					->setRoute((new FluidRouteDAO())->get($row['route_id'], $pdo))
					->setVolume((float)$row['vol'])
					->setType($row['type'])
					->setUser( (new StaffDirectoryDAO())->getStaff($row['user_id'], FALSE, $pdo) )
					->setTimeEntered($row['time_entered']);
			}
			return $data;
		}catch(PDOException $e) {
			errorLog($e);
			return [];
		}
	}
}