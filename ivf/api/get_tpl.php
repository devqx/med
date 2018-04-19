<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/23/16
 * Time: 11:18 AM
 */
require_once $_SERVER['DOCUMENT_ROOT']. '/ivf/classes/DAOs/IVFNoteTemplateDAO.php';

$data = (new IVFNoteTemplateDAO())->get($_GET['id']);
exit(json_encode($data));