<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/12/15
 * Time: 9:37 AM
 */

require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/ImagingTemplateDAO.php';

$scanTpls = (new ImagingTemplateDAO())->getTemplates();
exit(json_encode($scanTpls, JSON_PARTIAL_OUTPUT_ON_ERROR));
