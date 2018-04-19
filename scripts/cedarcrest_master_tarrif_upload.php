<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 10/26/17
 * Time: 9:20 PM
 */
header('Content-Type: text/plain');

function output($message){
    echo '--------------------------------'. PHP_EOL;
    echo $message.PHP_EOL;
    echo '-------------------------------'. PHP_EOL;
}
require_once $_SERVER['DOCUMENT_ROOT'] .'/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/spreadsheet-reader-master/php-excel-reader/excel_reader2.php';
require_once $_SERVER['DOCUMENT_ROOT']  . '/libs/spreadsheet-reader-master/SpreadsheetReader.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';