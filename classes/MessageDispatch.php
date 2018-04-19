<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/5/14
 * Time: 2:44 PM
 */
class MessageDispatch implements JsonSerializable
{
	
	private $id;
	private $patient;
	private $subject;
	private $message;
	private $smsChannelAddress;
	private $emailChannelAddress;
	private $voiceChannelAddress;
	private $smsDeliveryStatus;
	private $emailDeliveryStatus;
	private $voiceDeliveryStatus;
	private $exportStatus;
	private $user;
	
	function __construct($id = null)
	{
		$this->id = $id;
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
	 * @return MessageDispatch
	 */
	public function setId($id)
	{
		$this->id = $id;
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
	 * @return MessageDispatch
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getSubject()
	{
		return $this->subject;
	}
	
	/**
	 * @param mixed $subject
	 *
	 * @return MessageDispatch
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getMessage()
	{
		return $this->message;
	}
	
	/**
	 * @param mixed $message
	 *
	 * @return MessageDispatch
	 */
	public function setMessage($message)
	{
		$this->message = $message;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getSmsChannelAddress()
	{
		return $this->smsChannelAddress;
	}
	
	/**
	 * @param mixed $smsChannelAddress
	 *
	 * @return MessageDispatch
	 */
	public function setSmsChannelAddress($smsChannelAddress)
	{
		$this->smsChannelAddress = $smsChannelAddress;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getEmailChannelAddress()
	{
		return $this->emailChannelAddress;
	}
	
	/**
	 * @param mixed $emailChannelAddress
	 *
	 * @return MessageDispatch
	 */
	public function setEmailChannelAddress($emailChannelAddress)
	{
		$this->emailChannelAddress = $emailChannelAddress;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getVoiceChannelAddress()
	{
		return $this->voiceChannelAddress;
	}
	
	/**
	 * @param mixed $voiceChannelAddress
	 *
	 * @return MessageDispatch
	 */
	public function setVoiceChannelAddress($voiceChannelAddress)
	{
		$this->voiceChannelAddress = $voiceChannelAddress;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getSmsDeliveryStatus()
	{
		return $this->smsDeliveryStatus;
	}
	
	/**
	 * @param mixed $smsDeliveryStatus
	 *
	 * @return MessageDispatch
	 */
	public function setSmsDeliveryStatus($smsDeliveryStatus)
	{
		$this->smsDeliveryStatus = $smsDeliveryStatus;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getEmailDeliveryStatus()
	{
		return $this->emailDeliveryStatus;
	}
	
	/**
	 * @param mixed $emailDeliveryStatus
	 *
	 * @return MessageDispatch
	 */
	public function setEmailDeliveryStatus($emailDeliveryStatus)
	{
		$this->emailDeliveryStatus = $emailDeliveryStatus;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getVoiceDeliveryStatus()
	{
		return $this->voiceDeliveryStatus;
	}
	
	/**
	 * @param mixed $voiceDeliveryStatus
	 *
	 * @return MessageDispatch
	 */
	public function setVoiceDeliveryStatus($voiceDeliveryStatus)
	{
		$this->voiceDeliveryStatus = $voiceDeliveryStatus;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getExportStatus()
	{
		return $this->exportStatus;
	}
	
	/**
	 * @param mixed $exportStatus
	 *
	 * @return MessageDispatch
	 */
	public function setExportStatus($exportStatus)
	{
		$this->exportStatus = $exportStatus;
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
	 *
	 * @return MessageDispatch
	 */
	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}
	
	
	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
	
	function add($pdo=null){
		try {
			@session_start();
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			if (gettype($this->getEmailChannelAddress()) == 'array'){
				$email = implode(",", $this->getEmailChannelAddress());
			}else{
				$email = $this->getEmailChannelAddress();
			}
			
			$sql = "INSERT INTO message_dispatch SET pid ={$this->getPatient()->getId()}, message = '" . escape($this->getMessage()) . "', sms_channel_address = '" . in8nPhone($this->getSmsChannelAddress()) . "', email_channel_address = '" . $email . "', sms_delivery_status =FALSE, email_delivery_status = FALSE, voice_delivery_status = FALSE, user_id={$_SESSION['staffID']}";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() > 0) {
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