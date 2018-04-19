<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';

$bill = (new BillDAO())->getBill($_GET['id'], true);
$clinic = (new ClinicDAO())->getClinic(1, true);

require_once($_SERVER['DOCUMENT_ROOT'] . "/libs/escpos-php/escpos.php");



//$file = "img/logo/logo.png";
//$image = ImageCreateFromPng($file);
//list($w, $h) = GetImageSize($file);
//
//$pixels = array();
//for ($x=0; $x<$w; $x++){
//    for ($y=0; $y<$h; $y++){
//        $rgb = ImageColorAt($image, $x, $y);
//        $r = ($rgb >> 16) & 0xFF;
//        $g = ($rgb >> 8) & 0xFF;
//        $b = $rgb & 0xFF;
//        $pixels = '0x'.sprintf('%02x', ($r+$g+$b)/3); # store the average of r/g/b
//    }
//}


$printer="/dev/usb/lp0";
$fp = fopen($printer, "w");
$printer = new escpos($fp);

/* Initialize */
$printer -> initialize();
$printer->select_print_mode($printer::MODE_EMPHASIZED);
$printer->set_justification($printer::JUSTIFY_CENTER);

$printer -> text( $clinic->getName()."\n");
$printer->set_underline(2);
$printer -> text( $clinic->getAddress()."\n");
$printer->set_underline(0);
$printer->feed(1);

$printer->text("Payment Receipt: ".$bill->getId()."\n");
$printer->feed(1);

$printer->select_print_mode();
$printer->set_justification();

$printer->text("For ".($bill->getPatient() !== NULL ? $bill->getPatient()->getFullname() ."(".$bill->getPatient()->getId().")" : $bill->getBilledTo()->getName()) ."\n");
$printer->feed(1);

$printer->text("In the amount of: ");
$printer->text(ucwords(convert_number_to_words($bill->getAmount() * -1)). "(N". number_format($bill->getAmount() * -1, 2, '.',',') . ")"."\n");
$printer->feed(1);

$printer->text("Payment Method: ");
$printer->text( strtoupper($bill->getPaymentMethod()->getName()) ."\n");
$printer->feed(1);

$printer->text("Reference: ");
$printer->text( strtoupper($bill->getPaymentReference()) ."\n");
$printer->feed(1);

$printer->set_justification($printer::JUSTIFY_CENTER);
$printer->set_font($printer::FONT_B);
$printer->text("Received by: ". $bill->getReceiver()->getFullname());
$printer->feed(1);

$printer->cut();

fclose($fp);
