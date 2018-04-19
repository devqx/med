<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/11/16
 * Time: 9:11 AM
 */

require_once("../protect.php");
$script_block = <<<EOF

$(document).ready(function(){
   
});
EOF;


$page = "pages/ivf/index.php";
$title = "IVF";
$extra_style = array('/style/vitals.css', '/style/range.css','/font/medical/medical.css');
$extra_scripts = array('/js/highcharts.js', '/js/jquery.tools.min.js');

include "../template.inc.in.php";