<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 10/17/17
 * Time: 3:14 PM
 */
class SpokenLanguage implements JsonSerializable
{
    private $id;
    private $name;

    /**
     * SpokenLanguage constructor.
     * @param $id
     */
    public function __construct($id)
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
     * @return SpokenLanguage
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return SpokenLanguage
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }


    function jsonSerialize()
    {
        return (object)get_object_vars($this);
    }

    public function add($pdo = null){
        try {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
            $pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
            $name = $this->getName();
            $sql = "INSERT INTO spoken_language (`name`) VALUES ($name)";
            //error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($stmt->rowCount() == 1) {
                $this->setId($pdo->lastInsertId());
                return $this;
            }
            return null;
        } catch (PDOException $e) {
            errorLog($e);
            return null;
        }
    }

    function update($pdo = null)
    {
        try {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
            $pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
            $name = $this->getName();
            $sql = "UPDATE spoken_language SET `name`=$name WHERE id={$this->getId()}";
            //error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($stmt->rowCount() >= 0) {
                return $this;
            }
            return null;
        } catch (PDOException $e) {
            errorLog($e);
            return null;
        }
    }
}