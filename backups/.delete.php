<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/19/15
 * Time: 4:21 PM
 */

$file = (isset($_GET['file']) && $_GET['file']!='')? $_GET['file'] :'';
$redirectUrl = (strstr($_SERVER['HTTP_REFERER'], '?') !== false)? strstr($_SERVER['HTTP_REFERER'], "?", true):$_SERVER['HTTP_REFERER'];
if($file==''){
    header("Location: ".$redirectUrl."?error=could not delete file or file not found");
    exit();
}
else {
    if (file_exists($file)) {
        if(unlink($file)){
            header("Location: " . $redirectUrl . "?success=file deleted");
            exit();
        }
        else {
            $error = error_get_last();
            $error_ = strstr($error['message'], ":");
            header("Location: ".$redirectUrl."?error=".substr($error_,2));
            exit();
        }
    } else {
        header("Location: ".$redirectUrl."?error=could not delete file");
        exit();
    }
}