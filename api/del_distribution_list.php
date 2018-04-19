<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 2/25/15
 * Time: 2:02 PM
 */


include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DistributionListDAO.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DistributionListContact.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DistributionList.php';

function __format($output){
    $ret = array(
        'status'=>$output
    );
    return json_encode($ret);
}

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_REQUEST['q'])) {
        $id = json_decode($_REQUEST['q']);
        $distlist = new DistributionList($id);
        $delete_list = (new DistributionListDAO())->delete($distlist);
        $del = ($delete_list) ? "ok|Deleted" : "error|Delete failed";
        echo __format($del);
        exit;
    }
}