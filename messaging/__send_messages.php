<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/21/14
 * Time: 10:16 AM
 *
 * this file accepts a post array of message ids to be sent as sms and ...
 */
require $_SERVER['DOCUMENT_ROOT'] . "/Connections/MyDBConnector.php";
require $_SERVER['DOCUMENT_ROOT'] . "/classes/class.patient.php";

exit(exportMessages($_POST['messages']));

function exportMessages($selected){
    $m_ids = implode(",", $selected);

    try {
        $fp = fopen('../cron/messages_to_send/sms.csv', 'a');
        $conn = new MyDBConnector();
        $pdo= $conn->getPDO();
        //TODO select the fields that we need
        $sql = "SELECT id, sms_channel_address, message FROM message_dispatch WHERE export_status = 0 AND id IN (".$m_ids.")";
        $stmt1 = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt1->execute();
        $count = 0;
        while ($row1 = $stmt1->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            sleep(0.1);
            //TODO: if the cancel signal is received, how do you cancel this processing
            // since execution continues even if you have navigated away from this page
            fputcsv($fp, $row1,',','"');
            markAsExported($row1['id'], $pdo);
            $stmt2 = NULL;
            $count += 1;
        }
        //close the file
        fclose($fp);
        return 'success:'.$count .' messages queued for delivery';
    } catch (PDOException $e) {
        echo 'ERROR: ' . $e->getMessage();
        $pdo= NULL;
        $conn = NULL;
    }
    $pdo= NULL;
    $conn = NULL;
    return 'error:failed to dispatch messages';
}

function markAsExported($mid, $pdo=NULL){
    $sql = "UPDATE message_dispatch SET export_status=1 WHERE export_status = 0 AND id = " . $mid;
    $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
    $stmt->execute();
    $stmt = NULL;
}