<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/22/15
 * Time: 11:34 AM
 */
class PatientAttachmentDAO
{
	private $conn = null;
	public $maxDocFileSize = 7120;//in kilobytes
	public $valid_file_types
		= array(//        "image/jpeg", "image/png", "image/gif", "bmp",
		        //        "application/octet-stream",
		        //        "application/vnd.sun.xml.writer",
		        "application/pdf",//        "text/plain"
		);
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientAttachment.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AttachmentCategoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			if (!isset($_SESSION))
				session_start();
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo = null)
	{
		$attach = new PatientAttachment();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_attachment WHERE id=" . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$attach->setId($row['id'])->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo))->setNote($row['note'])->setDateAdded($row['date_added'])->setDateAdded($row['date_added'])->setUrl($row['document_url'])->setCategory((new AttachmentCategoryDAO())->get($row['category_id'], $pdo))->setUser((new StaffDirectoryDAO())->getStaff($row['user_add_id'], false, $pdo));
				
			} else {
				$attach = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$attach = null;
		}
		return $attach;
	}
	
	function countForPatient($pid, $pdo = null)
	{
		$attachments = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT COUNT(*) AS pDocs FROM patient_attachment WHERE patient_id=" . $pid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$attachments = $row['pDocs'];
			}
			$stmt = null;
		} catch (PDOException $e) {
			$attachments = 0;
		}
		return $attachments;
	}
	
	function patient($pid, $categoryId = null, $page = 0, $pageSize = 10, $pdo = null)
	{
		$extra = "";
		if (!is_null($categoryId)) {
			$extra .= " category_id = $categoryId AND ";
		}
		$sql = "SELECT * FROM patient_attachment WHERE {$extra} patient_id=" . $pid." AND is_deleted IS NOT TRUE";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		
		$attachments = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$attachments[] = $this->get($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$attachments = [];
		}
		$results = (object)null;
		$results->data = $attachments;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function encounter($encounterId, $page = 0, $pageSize = 10, $pdo = null)
	{
		$sql = "SELECT * FROM patient_attachment WHERE encounter_id=$encounterId";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		
		$attachments = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$attachments[] = $this->get($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$attachments = [];
		}
		$results = (object)null;
		$results->data = $attachments;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function all($categoryId = null, $page = 0, $pageSize = 10, $pdo = null)
	{
		$extra = "";
		if (!is_null($categoryId)) {
			$extra .= "category_id = $categoryId AND";
		}
		$sql = "SELECT * FROM patient_attachment WHERE {$extra} is_deleted IS NOT TRUE ";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$attachments = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$attachments[] = $this->get($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$attachments = [];
		}
		$results = (object)null;
		$results->data = $attachments;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	function add($attachment, $file, $pdo = null)
	{
		//$attachment = new PatientAttachment();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$categoryName = strtolower((new AttachmentCategoryDAO())->get($attachment->getCategory()->getId(), $pdo)->getName());
			$upload = $this->uploadFile($attachment->getPatient()->getId(), $file, $categoryName);
			$encounter = $attachment->getEncounter() ? $attachment->getEncounter()->getId() : 'NULL';
			$note = !is_blank($attachment->getNote()) ? quote_esc_str($attachment->getNote()) : 'null';
			//$note = quote_esc_str($attachment->getNote());
			
			if ($upload['status'] != "error") {
				$sql = "INSERT INTO patient_attachment (patient_id, note, document_url, date_added, user_add_id, category_id, encounter_id) VALUES ('" . $attachment->getPatient()->getId() . "', $note, '" . str_replace($_SERVER['DOCUMENT_ROOT'], "", $upload['filename']) . "', '" . $attachment->getDateAdded() . "', " . $attachment->getUser()->getId() . ", {$attachment->getCategory()->getId()}, $encounter)";
				$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$stmt->execute();
				
				if ($stmt->rowCount() === 1) {
					if ($upload['status'] !== "error") {
						$attachment->setId($pdo->lastInsertId());
						$pdo->commit();
						return array("status" => "success", "message" => $upload['message']);
						// return $attachment;
					} else {
						$pdo->rollBack();
						return array("status" => "error", "message" => $upload['message']);
					}
				} else {
					$pdo->rollBack();
					return array("status" => "error", "message" => "DB error");
				}
			} else {
				$pdo->rollBack();
				return array("status" => "error", "message" => $upload['message']);
			}
			
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function deleteDoc($doc,$pdo=null){
	    try{
	        $pdo = $pdo == null? $this->conn->getPDO() : $pdo;
	        $sql = "UPDATE patient_attachment SET is_deleted = TRUE, deleted_by = ".$doc->getDeletedBY()." WHERE id=".$doc->getId();
	        $stmt = $pdo->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	        $stmt->execute();
	        if ($stmt->rowCount() == 1){
	            return $doc;
            }
            else{
	            return null;
            }
        }
        catch(PDOException $e){
          return null;
        }
    }
	
	private function uploadFile($pid, $file, $category)
	{
		$path = $_SERVER['DOCUMENT_ROOT'] . "/documents/uploads/";
		$name = $file['name'];
		$size = $file['size'];
		$fileType = preg_replace("/\r|\n/", "", shell_exec("file -b --mime-type -m /usr/share/misc/magic {$file['tmp_name']}"));
		
		if (strlen($name)) {
			//file is given, ok
			if ($size > getMaximumFileUploadSize() * 1024) {
				return array('status' => 'error', 'message' => 'File size exceeded the allowable limit of ' . getMaximumFileUploadSize() . 'KB limit');
			} else if (!in_array($fileType, $this->valid_file_types)) {
				return array('status' => 'error', 'message' => 'Invalid file type [' . $fileType . ']');
			} else if ($size > $this->maxDocFileSize * 1024) {
				return array('status' => 'error', 'message' => 'File size exceeded the ' . $this->maxDocFileSize . 'KB limit');
			} else {
				//filetype is valid and size is within the limit
				$actual_file_name = preg_replace("/[^a-zA-Z0-9-_\.]/", '_', $name);
				//remove spaces from the name and illegal characters
				$tmp = $file['tmp_name'];
				$main_upload_location = $path . $pid . "/" . $category;
				
				if (!is_dir($main_upload_location)) {
					mkdir($main_upload_location, 0777, true);
				}
				if (move_uploaded_file($tmp, $main_upload_location . '/' . $actual_file_name)) {
					//upload was successful
					return array('status' => 'success', 'filename' => $main_upload_location . '/' . $actual_file_name, 'message' => 'uploaded', 'mimetype' => $file['type']);
				} else {
					return array('status' => 'error', 'message' => 'failed to save the file; access error');
				}
			}
		} else {
			return array('status' => 'error', 'message' => 'no file selected');
		}
	}
}