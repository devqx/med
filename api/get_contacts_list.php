<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 2/24/15
 * Time: 5:01 PM
 */

require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DistributionList.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DistributionListDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { //for Ajax
    header('Content-Type: application/json');
    if (isset($_REQUEST['q'])) {
        $id = json_decode($_REQUEST['q']);
        $distListSQL = $contacts = $new_contacts = $new_contacts_ = array();
        for($i=0; $i<count($id); $i++){
            $distListSQL[] = (new DistributionListDAO())->getDistributionList($id[$i])->getSqlQuery();
        }
        if(count($distListSQL)>0){
            for($s=0; $s<count($distListSQL); $s++){
                $contact_ = (new PatientDemographDAO())->querySQL($distListSQL[$s]);
                foreach($contact_ as $k=>$c){
                    $contacts[] = array('patientId'=>$c->getId(),'fname'=>$c->getFname(),'mname'=>$c->getMname(),'lname'=>$c->getLname());
                }
            }
        }
        $new_contacts = array_map("unserialize", array_unique(array_map("serialize", $contacts)));
        foreach($new_contacts as $c_){
            $new_contacts_[] = $c_;
        }
        $data = json_encode($new_contacts_);
        echo $data;
        exit;
    }
}