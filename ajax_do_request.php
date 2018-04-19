<?php
////error_reporting(0);
//$patientID = base64_decode($_POST['pid']);
////update regimen statuses,
////items are separated by |
//$items=explode("|",$_POST['items']);
//$action=strtolower($_POST['action']);
//
//if(!isset($_SESSION)){@session_start();}
//if($action=="fill"){
//	$done = "filled";
//}else if($action=="complete"){
//	$done = "completed";
//}else {
//	$done ="saved";//used as a fallback
//}
//
//$amount=0;
//$desc='';
//require_once 'class.drugs.php';
//$drugs=new Drugs();
//$count = count($items);
//$error ="";
//for($t=0;$t<$count;$t++){
//	$item = explode("-",$items[$t]);
//	$drugQuantity = $drugs->getDrugQuantityFromRegimenGroupID($item[1], $item[0]);
//	$amount = $amount  + Drugs::getDrugPrice($item[1], $patientID) * $drugQuantity;
//
//	//price per unit * the number of units
//	$desc.=$drugQuantity .' '.$drugs->getDrugType($item[1]) .($drugQuantity>1?'s':'').' of '.$drugs->getDrugName($item[1]).' at '.number_format((float)$drugs->getDrugPrice($item[1], $patientID),2);
//	if(!$drugQuantity <= 0 && is_numeric($drugQuantity)){
//		if($t != ($count -1)){$desc.=' and ';}
//	}
//	else{
//		return 'The quantity and description of the prescription is required';exit;
//	}
//	//then reduce the quantity of the drug from the inventory only when filling
//	if($action=="fill"){
//		$error=$drugs->dispenseDrug($item[1],$drugQuantity);
//	}
//
//	if($error=="ok" && $action == "fill"){
//		require_once 'class.RegimenFull.php';
//		$reg=new RegimenFull();
//		$reg->updateRegimen($items[$t],$action,$_SESSION['staffID']);
//
//	}
//	if($error=="" && $action == "complete"){
//		require_once 'class.RegimenFull.php';
//		$reg=new RegimenFull();
//		$reg->updateRegimen($items[$t],$action,$_SESSION['staffID']);
//	}
//}


//if($error == "" || $error == "ok") {
//	if ($action == "fill") {
//        require_once 'class.bills.php';
//        $bill=new Bills();
//
//		Bills::addBillToQueue ($patientID, "Regimen charges on " . $desc, $amount, "drugs","credit" );
//	}
//	echo "Regimen " . $done . " successfully";
//} else {
//	echo "Regimen update failed!<br>The quantity of drugs in the store is less than the quantity you want to fill<br><small>(for one or more of the drugs)</small>";
//}
?>
