<?php
if( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) ){
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.queue.php';
    $queue = new Queue();
    echo $queue->getQueuedPatients();
    exit;
}


