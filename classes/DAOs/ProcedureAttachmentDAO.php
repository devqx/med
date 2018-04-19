<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/26/16
 * Time: 11:11 AM
 */
class ProcedureAttachmentDAO
{
	private $conn = null;
	public $maxDocFileSize = 7120;//in kilobytes
	public $valid_file_types = array("image/jpeg", "image/png", "image/gif", "bmp", "application/octet-stream", "application/vnd.sun.xml.writer", "application/pdf", "text/plain","text/csv");
	
	function __construct() {
		if(!isset($_SESSION)){@session_start();}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/ProcedureAttachment.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PatientProcedure.php';
			$this->conn=new MyDBConnector();
		}catch(PDOException $e) {
			exit( 'ERROR: ' . $e->getMessage() );
		}
	}
	
	function get($id, $pdo=NULL){
		try {
			$pdo = $pdo == NULL?$this->conn->getPDO():$pdo;
			$sql = "SELECT * FROM procedure_attachment WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$enteredBy = (new StaffDirectoryDAO())->getStaff($row['entered_by']. FALSE, $pdo);
				return (new ProcedureAttachment($row['id']))->setPatientProcedure( new PatientProcedure($row['patient_procedure_id']) )->setDescription($row['description'])->setUploadDate($row['time_entered'])->setUploadBy( $enteredBy )->setUrl($row['url'])->setMimeType($row['mimetype']);
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function forPatProcedure($id, $pdo=NULL){
		$procedures = [];
		try {
			$pdo = $pdo == NULL?$this->conn->getPDO():$pdo;
			$sql = "SELECT * FROM procedure_attachment WHERE patient_procedure_id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$procedures[] = $this->get($row['id'], $pdo);
			}
		}catch (PDOException $e){
			errorLog($e);
			$procedures = [];
		}
		return $procedures;
	}
	
	function uploadFile($pid, $file)
	{
		$path = $_SERVER['DOCUMENT_ROOT'] . "/procedures/uploads/";
		$name = $file['name'];
		$size = $file['size'];
		
		if (strlen($name)) {
			//file is given, ok
			if(!in_array($file['type'], $this->valid_file_types)){
				return array('status' => 'error', 'message' => 'Invalid file type ['.$file['type'].']');
			} else if($size > getMaximumFileUploadSize() * 1024){
				return array('status' => 'error', 'message' => 'File size exceeded the allowable limit of ' . getMaximumFileUploadSize() . 'KB limit');
			} else if($size > $this->maxDocFileSize * 1024){
				return array('status' => 'error', 'message' => 'File size exceeded the ' . $this->maxDocFileSize . 'KB limit');
			} else {
				//filetype is valid and size is within the limit
				$actual_file_name = preg_replace("/[^a-zA-Z0-9-_\.]/", '_', $name);
				//remove spaces from the name and illegal characters
				$tmp = $file['tmp_name'];
				$main_upload_location = $path . $pid;
				
				if (!is_dir($main_upload_location)) {
					mkdir($main_upload_location, 0777, true);
				}
				if (move_uploaded_file($tmp, $main_upload_location . '/' . $actual_file_name)) {
					//upload was successful
					$url = str_replace($_SERVER['DOCUMENT_ROOT'], '', $main_upload_location . '/' . $actual_file_name);
					return array('status' => 'success', 'filename' => $url, 'message' => 'uploaded', 'mimetype' => $file['type']);
				} else {
					return array('status' => 'error', 'message' => 'failed to save the file; access error');
				}
			}
		} else {
			return array('status' => 'error', 'message' => 'no file selected');
		}
	}
}