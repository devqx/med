<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/22/16
 * Time: 4:41 PM
 */

require_once $_SERVER['DOCUMENT_ROOT']. '/ivf/classes/DAOs/IVFNoteTemplateDAO.php';

$data = (new IVFNoteTemplateDAO())->all();
exit(json_encode($data));