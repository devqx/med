<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/11/15
 * Time: 4:27 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/DrugBatchDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/DrugDAO.php';
$drug = (new DrugDAO())->getDrug($_REQUEST['did'], FALSE);
$batches = (new DrugBatchDAO())->getDrugBatches($drug);
exit(json_encode( $batches ));