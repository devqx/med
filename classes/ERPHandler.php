<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/4/15
 * Time: 12:19 PM
 */

class ERPHandler implements JsonSerializable {

    private $server_url;
    private $user;
    private $password;
    private $db;

    function __construct()
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/libs/xmlrpc-2.2.2/lib/xmlrpc.inc";
        $this->server_url  = "http://172.16.1.248:8069";
        $this->db = "iexpertsng";
        $this->user = "admin";
        $this->password = "password";
    }

    function login(){
        $connexion = new xmlrpc_client($this->server_url . "/xmlrpc/common");
        $connexion->setSSLVerifyPeer(0);

        $c_msg = new xmlrpcmsg('login');
        $c_msg->addParam(new xmlrpcval($this->db, "string"));
        $c_msg->addParam(new xmlrpcval($this->user, "string"));
        $c_msg->addParam(new xmlrpcval($this->password, "string"));

        $c_response =  $connexion->send($c_msg);

        if($c_response->errno === 0){
            $obj = (object)null;
            $obj->status = "success";
            $obj->userId = $c_response->value()->scalarval();
            return $obj;
        } else {
            $obj = (object)null;
            $obj->status = "error";
            $obj->message = $c_response->faultString();
            return $obj;
        }
    }

    function createPartner($partner){

    }

    function search($model, $fields = [], $filter = [])
    {
        try {
            //$f = array();
            $s = array();
            foreach ($filter as $f_) {
                $s = explode(" ", $f_);
            }

            $domain_filter = array(
                new xmlrpcval(
                    array(
                        new xmlrpcval($s[0], "string"),
                        new xmlrpcval($s[1], "string"),
                        new xmlrpcval($s[2], "string")
                    ), "array"
                ),
            );

            $client = new xmlrpc_client($this->server_url . "/xmlrpc/object");
            $client->setSSLVerifyPeer(0);

            $msg = new xmlrpcmsg('execute');
            $msg->addParam(new xmlrpcval($this->db, "string"));
            $msg->addParam(new xmlrpcval($this->login()->userId, "int"));
            $msg->addParam(new xmlrpcval($this->password, "string"));
            $msg->addParam(new xmlrpcval($model, "string"));
            $msg->addParam(new xmlrpcval("search", "string"));
            $msg->addParam(new xmlrpcval($domain_filter, "array"));
            $response = $client->send($msg);

            if($response->errno !== 0){
                return NULL;
            }

            $result = $response->value();

            $ids = $result->scalarval();

            $id_list = array();

            for ($i = 0; $i < count($ids); $i++) {
                $id_list[] = new xmlrpcval($ids[$i]->me['int'], 'int');
            }

            $field_list = array();

            foreach ($fields as $f_) {
                $field_list[] = new xmlrpcval($f_, "string");
            }

            $msg = new xmlrpcmsg('execute');
            $msg->addParam(new xmlrpcval($this->db, "string"));
            $msg->addParam(new xmlrpcval($this->login()->userId, "int"));
            $msg->addParam(new xmlrpcval($this->password, "string"));
            $msg->addParam(new xmlrpcval($model, "string"));
            $msg->addParam(new xmlrpcval("read", "string"));
            $msg->addParam(new xmlrpcval($id_list, "array"));
            $msg->addParam(new xmlrpcval($field_list, "array"));

            $resp = $client->send($msg);

            if ($resp->faultCode()) {
                echo $resp->faultString();
            }


            $result = $resp->value()->scalarval();
            return $result;
        } catch (Exception $e) {
            return NULL;
        }


    }


    function jsonSerialize()
    {
        return (object)get_object_vars($this);
    }


}