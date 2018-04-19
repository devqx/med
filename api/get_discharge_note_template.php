<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 8/3/16
 * Time: 12:17 AM
 */
 require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DischargedNoteTemplateDAO.php';
exit(json_encode($templates = (new DischargedNoteTemplateDAO())->all()));
?>