<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/5/14
 * Time: 10:46 AM
 */

class LabTestsGroupDAO {
    private $conn = null;
    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/LabTestsGroup.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/LabDAO.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            error_log( 'PDO ERROR: ' . $e->getMessage() );
        }
    }

    public function  getGroup($id, $pdo=NULL){
        $group = new LabTestsGroup();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM WHERE id = $id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $group->setId($row['id']);
                $group->setName($row['name']);

                $group_tests = array();
                $test_ids = explode(",",$row['test_ids']);
                foreach($test_ids as $id){
                    $group_tests[] = (new LabDAO())->getLab($id, TRUE);//get it in full?
                }
                $group->getContainedTests($group_tests);
            }else{
                $group=NULL;
            }

            $stmt = null;
        }catch(PDOException $e) {
            $stmt=NULL;
            $group=NULL;
        }
        return $group;
    }

    public function addLabGroup($lb, $pdo=NULL){
        $lb = new LabTestsGroup();
        $groups = implode(",",$lb->getContainedTests());
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "INSERT INTO lab_tests_group (name, test_ids) VALUES ('".$lb->getName()."', '".$groups."')";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

        }catch (PDOException $e){
            $stmt = NULL;
            $lb = NULL;
        }
        return $lb;

    }
}