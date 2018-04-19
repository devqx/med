<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/16/16
 * Time: 6:35 PM
 */
class PAuthCode implements JsonSerializable
{
	private $id;
	private $patient;
	private $status;
	private $creator;
	private $createDate;
	private $receiveDate;
	private $channel;
	private $channelAddress;
	private $scheme;
	private $code;
	private $notes;
	
	/**
	 * PAuthCode constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
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
	 * @return PAuthCode
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
	 * @return PAuthCode
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getStatus()
	{
		return $this->status;
	}
	
	/**
	 * @param mixed $status
	 *
	 * @return PAuthCode
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCreator()
	{
		return $this->creator;
	}
	
	/**
	 * @param mixed $creator
	 *
	 * @return PAuthCode
	 */
	public function setCreator($creator)
	{
		$this->creator = $creator;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCreateDate()
	{
		return $this->createDate;
	}
	
	/**
	 * @param mixed $createDate
	 *
	 * @return PAuthCode
	 */
	public function setCreateDate($createDate)
	{
		$this->createDate = $createDate;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getReceiveDate()
	{
		return $this->receiveDate;
	}
	
	/**
	 * @param mixed $receiveDate
	 *
	 * @return PAuthCode
	 */
	public function setReceiveDate($receiveDate)
	{
		$this->receiveDate = $receiveDate;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCode()
	{
		return $this->code;
	}
	
	/**
	 * @param mixed $code
	 *
	 * @return PAuthCode
	 */
	public function setCode($code)
	{
		$this->code = $code;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getScheme()
	{
		return $this->scheme;
	}
	
	/**
	 * @param mixed $scheme
	 *
	 * @return PAuthCode
	 */
	public function setScheme($scheme)
	{
		$this->scheme = $scheme;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getChannel()
	{
		return $this->channel;
	}
	
	/**
	 * @param mixed $channel
	 *
	 * @return PAuthCode
	 */
	public function setChannel($channel)
	{
		$this->channel = $channel;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getChannelAddress()
	{
		return $this->channelAddress;
	}
	
	/**
	 * @param mixed $channelAddress
	 *
	 * @return PAuthCode
	 */
	public function setChannelAddress($channelAddress)
	{
		$this->channelAddress = $channelAddress;
		return $this;
	}
	
	
	/**
	 * @return mixed
	 */
	public function getNotes()
	{
		return $this->notes;
	}
	
	/**
	 * @param mixed $notes
	 *
	 * @return PAuthCode
	 */
	public function setNotes($notes)
	{
		$this->notes = $notes;
		return $this;
	}
	
	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}
	
	function add($pdo=null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'].'/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/InsuranceSchemeDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/MessageDispatchDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/MessageDispatch.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		
		$patient = $this->getPatient() ? $this->getPatient()->getId() : 'null';
		$status = quote_esc_str('pending');
		$creator = $this->getCreator() ? $this->getCreator()->getId() : $_SESSION['staffID'];
		$createDate = $this->getCreateDate() ? quote_esc_str($this->getCreateDate()) : 'NOW()';
		$receiveDate = $this->getReceiveDate() ? quote_esc_str($this->getReceiveDate()) : 'NULL';
		$code = !is_blank($this->getCode()) ? quote_esc_str($this->getCode()) : 'null';
		$channel = $this->getChannel() ? $this->getChannel()->getId() : 'null';
		$channelAddress = !is_blank($this->getChannelAddress()) ? quote_esc_str($this->getChannelAddress()) : 'null';
		$scheme = $this->getScheme() ? $this->getScheme()->getId() : 'NULL';
		
		try {
			$sql = "INSERT INTO authorization_code SET patient_id=$patient, `status`=$status, scheme_id=$scheme, creator_id=$creator, create_date=$createDate, receive_date=$receiveDate, `code`=$code, channel_id=$channel, channel_address=$channelAddress";
			// error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if($stmt->rowCount() > 0){
				$this->setId($pdo->lastInsertId());
				// $scheme_ = (new InsuranceSchemeDAO())->get($scheme, $pdo);
				$type = $this->getChannel()->getId();
				if($type ==1){
					$sms = $this->getChannelAddress();
					$email = [];
					$voice = $this->getChannelAddress();
				}else if($type==2){
					$sms = '';
					$email[] = $this->getChannelAddress();
					$voice = '';
				}else if($type==3){
					$sms = '';
					$email = [];
					$voice = $this->getChannelAddress();
				}
				$message = '';
				
				
				
				//in this mode only one note was taken
				foreach ($this->getNotes() as $note){
					//$note = new PAuthCodeNote();
					$message = $note->getNote();
					$note->setPauthCode($this)->add($pdo);
				}
				
				$d = (new MessageDispatch())->setSubject('PA Code Request')->setMessage($message)->setPatient($this->getPatient())->setEmailChannelAddress($email)->setEmailDeliveryStatus(FALSE)->setSmsChannelAddress($sms)->setSmsDeliveryStatus(FALSE)->setVoiceChannelAddress($voice)->setVoiceDeliveryStatus(FALSE)->add($pdo);
				(new MessageDispatchDAO())->sendItem($d, $type, 'pa-codes', $pdo);
				
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	function update($pdo=null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'].'/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		try {
			$patient = $this->getPatient() ? $this->getPatient()->getId() : 'null';
			$status =  !is_blank($this->getStatus()) ? quote_esc_str($this->getStatus()): quote_esc_str('pending');
			$creator = $this->getCreator() ? $this->getCreator()->getId() : $_SESSION['staffID'];
			$createDate = $this->getCreateDate() ? quote_esc_str($this->getCreateDate()) : 'NOW()';
			$receiveDate = $this->getReceiveDate() ? quote_esc_str($this->getReceiveDate()) : 'NULL';
			$code = !is_blank($this->getCode()) ? quote_esc_str($this->getCode()) : 'null';
			$channel = $this->getChannel() ? $this->getChannel()->getId() : 'null';
			
			$sql = "UPDATE authorization_code SET patient_id=$patient, `status`=$status, creator_id=$creator, create_date=$createDate, receive_date=$receiveDate, `code`=$code, channel_id=$channel WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() >= 0){
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
}