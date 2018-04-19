<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/1/14
 * Time: 7:14 AM
 */
//this file must be at the root dir of the application

// make sure to add the following lines in ~/.bashrc
/**
 * export MEDICPLUS_ROOT=/path/to/medicplus/root :dont add the last slash
 * that would be the root of MedicPlus app
 */

require dirname(__FILE__) . "/Connections/MyDBConnector.php";
require dirname(__FILE__) . "/classes/MessageQueue.php";
require dirname(__FILE__) . "/classes/class.patient.php";


require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/MessageDispatchDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/MessageDispatch.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';

$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__);
prepareDispatchMessages();



function prepareDispatchMessages()
{
    $pat = new Manager();
    try {
        $conn = new MyDBConnector();
        $pdo= $conn->getPDO();
        $sql = "SELECT * FROM message_queue_temp WHERE message_status = 0 GROUP BY patient";
        $stmt1 = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt1->execute();
        while ($row1 = $stmt1->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            $pinfo = $pat->getPatientInfo($row1['patient']);
            $pPhone = in8nPhone($pinfo['phonenumber']);
            $pEmail = $pinfo['email'];
            $sql_ = "SELECT DISTINCT channel_subscribed FROM message_subscription WHERE patient=" . $row1['patient'];
            $stmt2 = $pdo->prepare($sql_, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt2->execute();
            $smsAddress = $emailAddress = '';
            while ($row2 = $stmt2->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                if ($row2[0] == 1) {
                    $smsAddress = $pPhone;
                } elseif ($row2[0] == 2) {
                    $emailAddress = $pEmail;
                }
                //TODO for voice channel ?
            }
            //dispatch the message
//            $mq = new MessageDispatch();
//            $mq->setPatient( (new PatientDemographDAO())->getPatient($row1['patient']) );
//            $mq->setMessage('TODO');
//            (new MessageDispatchDAO())->addItem($mq);
            $sql2 = "INSERT INTO message_dispatch (pid, message, sms_channel_address,  email_channel_address) SELECT patient, GROUP_CONCAT(message_content SEPARATOR '; '), '".$smsAddress."', '".$emailAddress."' FROM message_queue_temp WHERE patient = '".$row1['patient']."' AND message_status = 0" ;
            $stmt3 = $pdo->prepare($sql2, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt3->execute();
            // mark the all selected rows as dispatched.
            markAsDispatched($pdo, $row1['patient']);
            $stmt2 = NULL;
        }
    } catch (PDOException $e) {
        echo 'ERROR: ' . $e->getMessage();
        $pdo= NULL;
        $conn = NULL;
    }
    $pdo= NULL;
    $conn = NULL;
}


function markAsDispatched($pdo, $pid)
{
    $sql = "UPDATE message_queue_temp SET message_status=1 WHERE message_status = 0 AND patient = " . $pid;
    $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
    $stmt->execute();
    echo "dispatched messages for: ".$pid."\n";
    $stmt = NULL;
}