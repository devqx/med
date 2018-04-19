<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 12/11/15
 * Time: 10:13 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PackageItem.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackageItemsDAO.php';

$status = (new AntenatalPackageItemsDAO())->get($_POST['id'])->delete();
if ($status === true) {
	exit("success:Item removed");
} else if ($status === false) {
	exit("error:Failed to remove item");
} else {
	exit("error:System error");
}