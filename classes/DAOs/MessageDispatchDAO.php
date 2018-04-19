<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/5/14
 * Time: 2:51 PM
 */
class MessageDispatchDAO
{
	
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/MessageDispatch.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.config.sms.php';
			
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function addItem($md, $pdo = null)
	{
		return $md->add($pdo);
	}
	
	function getItem($id, $pdo = null)
	{
		$ls = new MessageDispatch();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM message_dispatch m LEFT JOIN patient_demograph d ON d.patient_ID=m.pid WHERE d.active IS TRUE AND id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ls->setId($row['id']);
				$ls->setPatient((new PatientDemographDAO())->getPatient($row['pid'], false, $pdo, null));
				$ls->setMessage($row['message']);
				$ls->setSmsChannelAddress($row['sms_channel_address']);
				$ls->setEmailChannelAddress($row['email_channel_address']);
				$ls->setVoiceChannelAddress(null);
				
				$ls->setSmsDeliveryStatus($row['sms_delivery_status']);
				$ls->setEmailDeliveryStatus($row['email_delivery_status']);
				$ls->setVoiceDeliveryStatus(false);
				$ls->setUser((new StaffDirectoryDAO())->getStaff($row['user_id'], false, $pdo));
			} else {
				$ls = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$ls = null;
		}
		return $ls;
	}
	
	function getItems($pdo = null)
	{
		$lss = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM message_dispatch m LEFT JOIN patient_demograph d ON m.pid=d.patient_ID WHERE d.active IS TRUE";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$lss[] = $this->getItem($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$lss = null;
		}
		return $lss;
	}
	
	function sendItem($mq, $type, $configSection = null, $pdo = null)
	{
		$emailConfig = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/classes/email.config.php', true);
		$response = new stdClass();
		if ($type == 1) {
			$phonenumber = $mq->getSmsChannelAddress();
			$message = $mq->getMessage();
			
			if (!function_exists('curl_init')) {
				$response->response = 404;
				$response->object = $mq;
				$response->status = "error|Server cannot create an external request. However, the message was saved";
				return (($response));
			}
			$ch = curl_init();
			$msg = urlencode($message);
			$CFG = new MainConfig();
			$request = $CFG::$smsGatewayUrl . "&sender=" . $CFG::$smsSenderName . "&message=" . $msg . "&mobile=" . in8nPhone($phonenumber);
			//error_log($request);
			curl_setopt($ch, CURLOPT_URL, $request);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);//connection timeout in seconds
			curl_setopt($ch, CURLOPT_TIMEOUT, 20); //timeout in seconds
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
			curl_setopt($ch, CURLOPT_HEADER, 0);
			
			// grab URL and pass it to the browser
			$output = curl_exec($ch);
			
			// close cURL resource, and free up system resources
			curl_close($ch);
			// sleep(2);
			$response->response = $output;
			// was used in emulating responses from sms gateway server
			$response->object = $mq;
			
			if ($response->response == '1801') {
				try {
					$this->markItemSent($mq, $pdo);
				} catch (Exception $e) {
					errorLog($e);
				}
			}
		} else if ($type == 2) {
			require $_SERVER['DOCUMENT_ROOT'] . '/libs/PHPMailer/PHPMailerAutoload.php';
			$mail = new PHPMailer;
			$mail->SMTPDebug = 0;                               // Enable verbose debug output
			
			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = $emailConfig[$configSection]['host'];               // Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = $emailConfig[$configSection]['username'];           // SMTP username
			$mail->Password = $emailConfig[$configSection]['secret'];             // SMTP password
			$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = $emailConfig[$configSection]['port'];                   // TCP port to connect to
			
			$mail->setFrom($emailConfig[$configSection]['username'], 'Medicplus X-Mailer');
			//$mail->addCC('frontdesk@limihospital.org');
			if (gettype($mq->getEmailChannelAddress()) == 'array') {
				foreach ($mq->getEmailChannelAddress() as $email) {
					$mail->addBCC($email);
				}
			} else {
				$mail->addAddress($mq->getEmailChannelAddress());
			}
			
			//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
			//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
			//$mail->isHTML(true);                                  // Set email format to HTML
			
			$mail->Subject = $mq->getSubject();
			$mail->Body = nl2br($mq->getMessage());
			$mail->AltBody = strip_tags($mq->getMessage());
			
			
			if (!$mail->send()) {
				try {
					$this->markItemSent($mq, $pdo);
				} catch (Exception $e) {
					errorLog($e);
				}
				$mq->add();
				
				error_log('Message could not be sent.');
				error_log('Mailer Error: ' . $mail->ErrorInfo);
			} else {
				//error_log('Message has been sent');
				try {
					// $this->addItem($mq, $pdo);
				} catch (Exception $e) {
					//error_log('Message could not be sent.');
					//error_log('Mailer Error: ' . $mail->ErrorInfo);
				}
			}
		}
		ob_end_clean();
		return $response;
		
	}
	
	function markItemSent($item, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE message_dispatch SET sms_delivery_status = TRUE WHERE id = " . $item->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$item->setSmsDeliveryStatus(true);
			} else {
				$item = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$item = null;
		}
		return $item;
	}
	
	function sendSMS($d, $pdo = null)
	{
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		if (SmsConfig::$sendSMS == 1) {
			$send = $this->sendItem($d, 1, null, $pdo);
		}
	}
	
	function sendEmail($d, $pdo = null)
	{
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		$send = $this->sendItem($d, 2, 'others', $pdo);
	}
}