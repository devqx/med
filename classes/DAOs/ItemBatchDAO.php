<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 1/9/17
 * Time: 1:57 PM
 */
class ItemBatchDAO
{

	private $conn = null;

	function __construct()
	{
		if(!isset($_SESSION)){
			@session_start();
		}
		try{
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ItemBatch.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ItemDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function add($batch, $pdo=null){
		try{
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO item_batch (`name`, item_id, quantity, expiration_date, service_centre_id) VALUES ('". $batch->getName() ."', '". $batch->getItem()->getId() ."', '". $batch->getQuantity() ."', '". $batch->getExpirationDate() ."', '". $batch->getServiceCenter()->getId() ."')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$batch->setId($pdo->lastInsertId());
				return $batch;
			}else{
				return null;
			}
		}catch (PDOException $e){
			error_log($e);
			return null;
		}
	}

    function updateBatch($batch, $pdo = null)
    {
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $sql = "UPDATE item_batch SET service_centre_id='". $batch->getServiceCenter()->getId() ."',  expiration_date='". $batch->getExpirationDate() ."', name='". $batch->getName() ."', quantity='".$batch->getQuantity() ."' WHERE id =". $batch->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($stmt->rowCount() >= 1) {
                return $batch;
            } else {
                return null;
            }
        } catch (PDOException $e) {
            errorLog($e);
            return null;
        }
    }


	function getItemBatches($item, $pdo = null)
	{
		$item_ = is_string($item) ? $item : $item->getId();
		$batches = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_batch WHERE item_id = " . $item_;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$batch = $this->getBatch($row['id']);
				$batches[] = $batch;
			}

			return $batches;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}

	function getItemBatchByServiceCenter($service, $pdo = null)
	{
		$batches = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_batch WHERE service_centre_id = " . $service;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$batch = new ItemBatch($row['id']);
				$batch->setName($row['name']);
				$batch->setQuantity($row['quantity']);
				$batch->setExpirationDate($row['expiration_date']);
				$batch->setItem((new ItemDAO())->getItem($row['item_id'], $pdo));

				$batches[] = $batch;
			}

			return $batches;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}


	
	function stockUp($batch, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE item_batch SET quantity = (quantity + " . $batch->getQuantity() . ") WHERE id = " . $batch->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				return $batch;
			} else {
				return null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function update($batch, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE item_batch SET quantity='".$batch->getQuantity() ."' WHERE id =". $batch->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 1) {
				return $batch;
			} else {
				return null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function stockAdjust($batch, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE item_batch SET quantity = " . $batch->getQuantity() . ", service_centre_id={$batch->getServiceCenter()->getId()} WHERE id = " . $batch->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				return $batch;
			} else {
				return null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function getBatch($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			if (is_blank($id) || $id === null) return null;
			$sql = "SELECT * FROM item_batch WHERE id = " . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$batch = new ItemBatch($row['id']);
				$batch->setName($row['name']);
				$batch->setItem((new ItemDAO())->getItem($row['item_id'], $pdo));
				$batch->setQuantity($row['quantity']);
				$batch->setExpirationDate($row['expiration_date']);
				$batch->setServiceCenter((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				return $batch;
			}

			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

function getBatches($pdo = null)
	{
		$batches = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_batch";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$batch = new ItemBatch($row['id']);
				$batch->setName($row['name']);
				$batch->setItem((new ItemDAO())->getItem($row['item_id'], FALSE, $pdo));
				$batch->setQuantity($row['quantity']);
				$batch->setExpirationDate($row['expiration_date']);
				$batch->setServiceCenter((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				$batches[] = $batch;

				return $batches;
			}

			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}

	}


	function getBatchByItem($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			if (is_blank($id) || $id === null) return null;
			$sql = "SELECT * FROM item_batch WHERE item_id = " . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$batch = new ItemBatch($row['id']);
				$batch->setName($row['name']);
				$batch->setItem((new ItemDAO())->getItem($row['item_id'], $pdo));
				$batch->setQuantity($row['quantity']);
				$batch->setExpirationDate($row['expiration_date']);
				$batch->setServiceCenter((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				return $batch;
			}

			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

    function getBatchesByItem($id, $pdo = null)
    {
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            if (is_blank($id) || $id === null) return null;
            $sql = "SELECT * FROM item_batch WHERE item_id = " . $id;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $batchs = [];
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $batch = new ItemBatch($row['id']);
                $batch->setName($row['name']);
                $batch->setItem((new ItemDAO())->getItem($row['item_id'], $pdo));
                $batch->setQuantity($row['quantity']);
                $batch->setExpirationDate($row['expiration_date']);
                $batch->setServiceCenter((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
                $batchs[] = $batch;
            }

        } catch (PDOException $e) {
            errorLog($e);
            return [];
        }
        return $batchs;
    }
}