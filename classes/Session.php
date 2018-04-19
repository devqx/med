<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/24/16
 * Time: 4:05 PM
 */
class Session_
{
	private $db;

	public function __construct(){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$this->db = (new MyDBConnector())->getPDO();
		
		session_set_save_handler(
			array(&$this, "_open"),
			array(&$this, "_close"),
			array(&$this, "_read"),
			array(&$this, "_write"),
			array(&$this, "_destroy"),
			array(&$this, "_gc")
		);
		register_shutdown_function('session_write_close');
		// Start the session
		if(!isset($_SESSION))
		{
			session_start();
		}
		error_log("session has started...");
	}

	public function _open(){
		error_log("session has opened...");
		if($this->db){
			return true;
		}
		return false;
	}

	public function _close(){
		error_log("session has closed...");
		// Close the database connection
		// If successful
		$this->db = null;
		
		if(!$this->db){
			// Return True
			return true;
		}
		// Return False
		return false;
	}

	public function _read($id){
		error_log("session data has been read...");
		// Set query
		$this->db->exec('SELECT `data` FROM sessions WHERE id = :id');

		// Bind the Id
		$this->db-> bind(':id', $id);

		// Attempt execution
		// If successful
		if($this->db->execute()){
			// Save returned row
			$row = $this->db->single();
			// Return the data
			return $row['data'];
		}else{
			// Return an empty string
			return '';
		}
	}

	public function _write($id, $data){
		error_log("session has been written...");
		// Create time stamp
		$access = time();

		// Set query
		$this->db->query('REPLACE INTO sessions VALUES (:id, :access, :data)');

		// Bind data
		$this->db->bind(':id', $id);
		$this->db->bind(':access', $access);
		$this->db->bind(':data', $data);

		// Attempt Execution
		// If successful
		if($this->db->execute()){
			// Return True
			return true;
		}

		// Return False
		return false;
	}

	public function _destroy($id){
		error_log("session has been destroyed...");
		// Set query
		$this->db->query('DELETE FROM sessions WHERE id = :id');

		// Bind data
		$this->db->bind(':id', $id);

		// Attempt execution
		// If successful
		if($this->db->execute()){
			// Return True
			return true;
		}

		// Return False
		return false;
	}

	public function _gc($max){
		error_log("session has been cleaned...");
		// Calculate what is to be deemed old
		$old = time() - $max;

		// Set query
		$this->db->query('DELETE * FROM sessions WHERE access < :old');

		// Bind data
		$this->db->bind(':old', $old);

		// Attempt execution
		if($this->db->execute()){
			// Return True
			return true;
		}

		// Return False
		return false;
	}
}