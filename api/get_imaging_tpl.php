<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/12/15
 * Time: 9:37 AM
 */

require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/ImagingTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/PatientScanNoteDAO.php';

$scanTpl = (new ImagingTemplateDAO())->getTemplate($_GET['id']);
if($scanTpl==null){
    exit(json_encode(array('bodyPart'=>' ')));
}
exit(json_encode($scanTpl));