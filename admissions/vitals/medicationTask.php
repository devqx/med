<?php 
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php';
    
    $details=(new PrescriptionDataDAO())->getFullfilledPrescriptionData($_GET['ipid'], $_GET['aid']);
?>

<h4><?= count($details) ?></h4>