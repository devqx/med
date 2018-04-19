<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 7/24/15
 * Time: 5:28 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ApprovedQueueDAO.php';

$id = $_POST['q'];
$result = (new ApprovedQueueDAO())->setRead($id);
return $result;