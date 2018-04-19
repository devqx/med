<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExtendedPDO
 *
 * @author pauldic
 */
class ExtendedPDO extends PDO
{
	private $dbName;
	private $stmt;

	function __construct($host, $dbName, $user, $pwd, $properties)
	{
		parent::__construct("mysql:host=" . $host . ";dbname=" . $dbName . ";", $user, $pwd, $properties);
		$this->dbName = $dbName;
		//parent is PDO object
	}

	public function getDBName()
	{
		return $this->dbName;
	}

	/*//new methods
	public function query($query){
		$this->stmt = $this->prepare($query);
	}

	public function bind($param, $value, $type = null){
		if (is_null($type)) {
			switch (true) {
				case is_int($value):
					$type = PDO::PARAM_INT;
					break;
				case is_bool($value):
					$type = PDO::PARAM_BOOL;
					break;
				case is_null($value):
					$type = PDO::PARAM_NULL;
					break;
				default:
					$type = PDO::PARAM_STR;
			}
		}
		$this->stmt->bindValue($param, $value, $type);
	}

	public function execute(){
		return $this->stmt->execute();
	}

	public function resultset(){
		$this->execute();
		return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function single(){
		$this->execute();
		return $this->stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function rowCount(){
		return $this->stmt->rowCount();
	}

	public function lastInsertId($seqName=null){
		return $this->lastInsertId($seqName);
	}

	public function beginTransaction(){
		return $this->beginTransaction();
	}

	public function endTransaction(){
		return $this->commit();
	}

	public function cancelTransaction(){
		return $this->rollBack();
	}*/
}
