<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 6/21/17
 * Time: 2:55 PM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    $cats = null;
    header("Access-Control-Allow-Origin:*");
    if(isset($_POST['staffId']) && !isset($_POST['system_id'])){
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/SystemsReviewCategoryDAO.php";
        $cats = (new SystemsReviewCategoryDAO())->allByType(null);
    }elseif ( isset($_POST['staffId']) && isset($_POST['system_id'])){
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/SystemsReviewDAO.php";
        $cats = (new SystemsReviewDAO())->byCat($_POST['system_id']);
    }
    echo json_encode($cats, JSON_PARTIAL_OUTPUT_ON_ERROR);
}