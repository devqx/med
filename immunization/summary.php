<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/7/14
 * Time: 1:00 PM
 */
include '../protect.php';
$script_block = <<<EOF
\$(document).ready(function(){
    \$('table.table').tableScroll({height:550});
});
EOF;
$page = "pages/vaccine/summary.php";
$extra_style = ['/style/vaccine.css'];
include "../template.inc.in.php";