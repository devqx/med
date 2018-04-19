<?php

/**
 * Created by PhpStorm.
 * User: nnamdi
 * Date: 1/5/17
 * Time: 4:16 PM
 */
class AuditLog implements JsonSerializable
{
    private $id;
    private $user;
    private $object;
    private $objectId;
    private $field;
    private $newValue;
    private $oldValue;
    private $date;

    /**
     * AuditLog constructor.
     * @param $id
     */
    public function __construct($id=null)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return AuditLog
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return AuditLog
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param mixed $object
     * @return AuditLog
     */
    public function setObject($object)
    {
        $this->object = $object;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param mixed $objectId
     * @return AuditLog
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $field
     * @return AuditLog
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNewValue()
    {
        return $this->newValue;
    }

    /**
     * @param mixed $newValue
     * @return AuditLog
     */
    public function setNewValue($newValue)
    {
        $this->newValue = $newValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }

    /**
     * @param mixed $oldValue
     * @return AuditLog
     */
    public function setOldValue($oldValue)
    {
        $this->oldValue = $oldValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     * @return AuditLog
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function add($pdo = null)
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
        try {
            $userId = $this->getUser() ? $this->getUser()->getId() : $_SESSION['staffID'];
            $object = quote_esc_str( $this->getObject());
            $objectId = quote_esc_str( $this->getObjectId());
            $field = quote_esc_str($this->getField()) ;
            $oldValue = quote_esc_str($this->getOldValue());
            $newValue = quote_esc_str($this->getNewValue());
            $pdo = $pdo === null ? (new MyDBConnector())->getPDO() : $pdo;
            $sql = "INSERT INTO audit_log(user_id, object, object_id,`field`, old_value, new_value) VALUES ($userId, $object, $objectId, $field, $oldValue, $newValue)";
            $stmt = $pdo->prepare($sql);
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

    function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
        return (object)get_object_vars($this);
    }


}
