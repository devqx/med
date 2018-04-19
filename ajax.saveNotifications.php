<?php
    require $_SERVER['DOCUMENT_ROOT']. '/classes/NotificationOptions.php';
 
    if($_POST['pid']){
        $ids=array();
        for($i=0; $i<$_POST['counter']; $i++){
            $ids[]=$_POST['ch_'.$i];
        }
        $not=new NotificationOptions();
        $pid=$_POST['pid'];
        $status=$not->saveChanges($pid, strtolower($_POST['checkEmail'])=="true", $ids);
        
        if($status===TRUE){
            echo "Changes Saved";
        } else {
            if($status===FALSE){
                echo 'Some error occoured';
            }else{
                echo $status;
            }
        }
    }  else {
        echo "Error!!!";
    }
    
exit;