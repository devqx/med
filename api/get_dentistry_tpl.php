<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/12/15
 * Time: 9:37 AM
 */

require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/DentistryTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/PatientDentistryNoteDAO.php';

$scanTpl = (new DentistryTemplateDAO())->get($_GET['id']);
if($scanTpl==null){
    exit(json_encode(array('bodyPart'=>' ')));
}
exit(json_encode($scanTpl));