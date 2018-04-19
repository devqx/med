<?php

    require $_SERVER['DOCUMENT_ROOT']. '/classes/Admission.php';
    require $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/AdmissionDAO.php';
    require $_SERVER['DOCUMENT_ROOT']. '/api/get_staff.php';


    $round_enabled =(bool) isset($_POST['rounding'])?1:0;
    if(isset($_POST['freq_val']) && isset($_POST['freq_interval'])){
        $interval=((int)$_POST['freq_val']) * ((int)$_POST['freq_interval'] );
    }  else {
        $interval=NULL;
    }

    $adm = new Admission();
        $pat=new PatientDemograph();
            $pat->setId($_POST['pid']);
    $adm->setPatient($pat);
    $adm->setAdmittedBy($staff);
    $adm->setReason($_POST['reason']);
        $round=NULL;

        if($round_enabled){
            require $_SERVER['DOCUMENT_ROOT']. '/classes/WardRound.php';
            $round=new WardRound();
                $round->setPatient($pat);
                $round->setInPatient($adm);
                $round->setFrequency($interval);
        }
    $adm->setClinicalTask($round);
    $adm->setClinic($staff->getClinic());

    $adm=(new AdmissionDAO())->addAdmission($adm);
    
    
    
?>