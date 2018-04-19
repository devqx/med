<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/PatientQueue.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php');
$pat = new PatientDemograph();
$pat->setId($_REQUEST['pid']);
$pq = new PatientQueue();
$pq->setType($_REQUEST['type']);
$pq->setPatient($pat);
(new PatientQueueDAO())->addPatientQueue($pq);
echo json_encode("Patient added to queue");