<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/13/16
 * Time: 10:20 AM
 */
class QualityControl implements JsonSerializable
{
    private $id;
    private $request;
    private $type;
    private $user;
    private $actionDate;

    /**
     * QualityControl constructor.
     * @param $id
     */
    public function __construct($id = NULL)
    {
        $this->id = $id;
    }

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
     * @return QualityControl
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     * @return QualityControl
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return QualityControl
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * @return QualityControl
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getActionDate()
    {
        return $this->actionDate;
    }

    /**
     * @param mixed $actionDate
     * @return QualityControl
     */
    public function setActionDate($actionDate)
    {
        $this->actionDate = $actionDate;
        return $this;
    }


    function add($pdo=null){
	    if(!isset($_SESSION)){@session_start();}
	    require_once $_SERVER['DOCUMENT_ROOT']. '/Connections/MyDBConnector.php';
	    $request_id = $this->getRequest() ? $this->getRequest()->getId() : "NULL";
	    $quality_control_type_id = $this->getType() ? $this->getType()->getId() : "NULL";
	    $user_id = $this->getUser() ? $this->getUser()->getId() : "NULL";
	    $date = $this->getActionDate() ? quote_esc_str($this->getActionDate()) : "NULL";
	    try {
		    $pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		    $sql = "INSERT INTO genetic_quality_control (request_id, quality_control_type_id, user_id, `date`) VALUES ($request_id, $quality_control_type_id, $user_id, $date)";
		    $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		    $stmt->execute();
		    if ($stmt->rowCount() == 1) {
			    $this->setId($pdo->lastInsertId());
			    return $this;
		    }
		    return null;
	    }catch (PDOException $e){
		    errorLog($e);
		    return null;
	    }
    }

	function update($pdo=null){
	    if(!isset($_SESSION)){@session_start();}
	    require_once $_SERVER['DOCUMENT_ROOT']. '/Connections/MyDBConnector.php';
	    $request_id = $this->getRequest() ? $this->getRequest()->getId() : "NULL";
	    $quality_control_type_id = $this->getType() ? $this->getType()->getId() : "NULL";
	    $user_id = $this->getUser() ? $this->getUser()->getId() : $_SESSION['staffID'];
	    $date = $this->getActionDate() ? quote_esc_str($this->getActionDate()) : "NOW()";
	    try {
		    $pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		    $sql = "UPDATE genetic_quality_control SET request_id=$request_id, quality_control_type_id=$quality_control_type_id, user_id=$user_id, `date`=$date WHERE id={$this->getId()}";
		    //error_log($sql);
		    $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		    $stmt->execute();
		    if ($stmt->rowCount() == 1) {
			    $this->setId($pdo->lastInsertId());
			    return $this;
		    }
		    return null;
	    }catch (PDOException $e){
		    errorLog($e);
		    return null;
	    }
    }
}