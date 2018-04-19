<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/20/16
 * Time: 9:36 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysioBookingDAO.php';
$DAO = new PhysioBookingDAO();
$booking = $DAO->get($_GET['booking_id']);
exit(json_encode($DAO->cancel($booking)));

