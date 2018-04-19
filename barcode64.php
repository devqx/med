<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/28/16
 * Time: 11:42 AM
 */
$url = ($_SERVER['HTTPS'] ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/barcode.php?text=". $_GET['text'];
$imdata = base64_encode(file_get_contents($url));
exit("data:image/png;base64,$imdata");