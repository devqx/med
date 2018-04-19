<?php
/**
 * Created by JetBrains PhpStorm.
 * User: peter
 * Date: 10/19/13
 * Time: 3:48 PM
 * To change this template use File | Settings | File Templates.
 */require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.bedspaces.php';
$bedObj=new Bedspaces;
echo $bedObj->roughListBedTypes();
exit;