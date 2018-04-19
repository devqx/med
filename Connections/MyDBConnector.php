<?php
/**
 *
 * @author internet experts
 */
class MyDBConnector
{
	
	public function __construct()
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/ExtendedPDO.php';
	}

	public function getPDO()
	{
		try {
			$pdo = new ExtendedPDO(MainConfig::$dbHost, MainConfig::$dbName, MainConfig::$dbUser, MainConfig::$dbPass, array(PDO::ATTR_PERSISTENT => true));
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$pdo->exec("SET CHARACTER SET utf8");
			$pdo->exec("SET NAMES utf8");
			$pdo->exec("SET SESSION sql_mode=''");
			return $pdo;
		} catch (PDOException $e) {
			errorLog($e);
		}
		return null;
	}
}
