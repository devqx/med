<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/5/14
 * Time: 6:04 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/MessageDispatchDAO.php';
$return = array();
$DAO = new MessageDispatchDAO();
foreach ($_POST['messages'] as $m) {
	$mq = $DAO->getItem($m);
	$return[] = $DAO->sendItem($mq, 1, null);
}

exit(json_encode(($return)));
//$messages = ->getItem()