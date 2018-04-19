<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/15/14
 * Time: 11:49 AM
 */
class PatientScanAttachmentDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Scan.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientScanAttachment.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function getAttachment($id, $pdo = null)
	{
		$atch = new PatientScanAttachment();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_scan_attachment WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$atch->setId($row['id']);
				$atch->setPatientScan((new PatientScanDAO())->getScan($row['patient_scan_id'], $pdo));
				$atch->setAttachmentURL($row['attachment_url']);
				$atch->setDateAdded($row['timeAdded']);
			} else {
				$atch = null;
				$stmt = null;
			}

		} catch (PDOException $e) {
			$atch = null;
		}
		return $atch;
	}

	function getAttachments($pdo = null)
	{
		$attchs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_scan_attachment";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$atch = new PatientScanAttachment();
				$atch->setId($row['id']);
				$atch->setPatientScan((new PatientScanDAO())->getScan($row['patient_scan_id'], $pdo));
				$atch->setAttachmentURL($row['attachment_url']);
				$atch->setNote($row['note']);
				$atch->setDateAdded($row['timeAdded']);
				$atch->setCreator((new StaffDirectoryDAO())->getStaff($row['create_uid'], FALSE, $pdo));

				$attchs[] = $atch;
			}
		} catch (PDOException $e) {
			$attchs = array();
		}
		return $attchs;
	}

	function getScanAttachments($scanId, $pdo = null)
	{
		$attchs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_scan_attachment WHERE patient_scan_id = '$scanId'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$atch = new PatientScanAttachment();
				$atch->setId($row['id']);
//                $atch->setPatientScan( (new PatientScanDAO())->getScan($row['patient_scan_id'], $pdo) );
				$atch->setAttachmentURL($row['attachment_url']);
				$atch->setNote($row['note']);
				$atch->setDateAdded($row['timeAdded']);
				$atch->setCreator((new StaffDirectoryDAO())->getStaff($row['create_uid'], FALSE, $pdo));

				$attchs[] = $atch;
			}
		} catch (PDOException $e) {
			$attchs = array();
		}
		return $attchs;
	}

	function addAttachment($atch, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;

			$image_file = $atch->getAttachment();
			$user_dir = $atch->getPatientScan()->getPatient()->getId();

			$img_location = '/imaging/scans/' . $user_dir . '/';
			if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $img_location)) {
				$oldmask = umask(0);
				if (!@mkdir($_SERVER['DOCUMENT_ROOT'] . $img_location, 0777, true)) {
					$error = error_get_last();
//                    die("error:".$error['message']);
					die("error:Permission denied for mkdir action.");
				} else {
					umask($oldmask);
				}
			}
			$timestamp = time();
			$ext = pathinfo($image_file['name'])['extension'];

			if (move_uploaded_file($image_file['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $img_location . $timestamp . '.' . $ext)) {
				$url = $img_location . $timestamp . '.' . $ext;
				$atch->setAttachmentURL($url);
			} else {
				die("error:Failed to save uploaded file.");
			}
			$sql = "INSERT INTO patient_scan_attachment (patient_scan_id, attachment_url, note, create_uid) VALUES (" . $atch->getPatientScan()->getId() . ", '" . $atch->getAttachmentURL() . "', '" . $atch->getNote() . "', " . $atch->getCreator()->getId() . ")";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$atch->setId($pdo->lastInsertId());
				$atch->setDateAdded(date("Y-m-d H:i:s"));
				//$atch->setDateAdded();
			} else {
				$atch = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$atch = null;
		}
		return $atch;
	}
} 