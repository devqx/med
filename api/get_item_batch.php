<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 4/6/17
 * Time: 1:17 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/ItemBatchDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/ItemDAO.php';
@ob_end_clean();
$batches = null;
if (isset($_REQUEST['id'])) {
    file_put_contents('/tmp/Ite.txt', json_encode($_REQUEST['id']));
    $batches = (new ItemBatchDAO())->getItemBatches($_REQUEST['id']);
    exit(json_encode($batches));
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
    @ob_end_clean();
    if (isset($_REQUEST['id'])) {
        $data = json_encode($batches);
    }
    if (!isset($_GET['suppress'])) {
        @ob_end_clean();
        echo $data;
    }
}
