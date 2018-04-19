<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/15/16
 * Time: 10:20 AM
 */
require_once ($_SERVER['DOCUMENT_ROOT']. "/protect.php");
$script_block = <<<EOF

$(document).ready(function(){
   
});
EOF;


$page = "pages/ivf/patients/index.php";
$title = "IVF Patients";
$extra_link = array('link'=>"../", 'title'=> 'IVF');
$extra_style = array('/style/vitals.css', '/font/medical/medical.css');
$extra_scripts = array('/js/highcharts.js', '/js/jquery.tools.min.js');

include $_SERVER['DOCUMENT_ROOT']."/template.inc.in.php";