<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/23/16
 * Time: 12:58 PM
 */
class MiscellaneousItemDAO
{
    private $conn = null;

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/MiscellaneousItem.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }
}