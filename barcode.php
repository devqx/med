<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/16/16
 * Time: 3:37 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Barcode.php';
(new Barcode())->create($_GET['text'],'50', 'horizontal', 'code39');
