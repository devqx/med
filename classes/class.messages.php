<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/20/14
 * Time: 1:34 PM
 */

class Message {

    function __construct(){
    }

    public function getMessages($status, $sms_status){
        require $_SERVER['DOCUMENT_ROOT'] . '/Connections/dbconnection.php';
        if (isset($database_dbconnection, $dbconnection)) {
            mysql_select_db($database_dbconnection, $dbconnection);
        }
        $sql = "SELECT * FROM message_dispatch WHERE export_status = ".mysql_real_escape_string($status). " AND sms_delivery_status = ".$sms_status;

        $rst = mysql_query($sql);
        $data = array();
        while($row = mysql_fetch_assoc($rst)){
            $data[] = $row;
        }
        return json_encode($data);
    }

    public function getSentMessages(){
        require $_SERVER['DOCUMENT_ROOT'] . '/Connections/dbconnection.php';
        if (isset($database_dbconnection, $dbconnection)) {
            mysql_select_db($database_dbconnection, $dbconnection);
        }
        $sql = "SELECT * FROM message_dispatch WHERE sms_delivery_status = TRUE OR email_delivery_status = TRUE OR voice_delivery_status = TRUE ";

        $rst = mysql_query($sql);
        $data = array();
        while($row = mysql_fetch_assoc($rst)){
            $data[] = $row;
        }
        return json_encode($data);
    }

    public function getUnsentMessages(){
        require $_SERVER['DOCUMENT_ROOT'] . '/Connections/dbconnection.php';
        if (isset($database_dbconnection, $dbconnection)) {
            mysql_select_db($database_dbconnection, $dbconnection);
        }
        $sql = "SELECT * FROM message_dispatch WHERE sms_delivery_status = FALSE #OR email_delivery_status = FALSE OR voice_delivery_status = FALSE ";

        $rst = mysql_query($sql);
        $data = array();
        while($row = mysql_fetch_assoc($rst)){
            $data[] = $row;
        }
        return json_encode($data);
    }
}