<?php if(!isset($_SESSION)){session_start();}
include  $_SERVER['DOCUMENT_ROOT']."/protect.php";
if(!isset($_SESSION)){session_start();}


$script_block = <<<EOF

EOF;

$page =  $_SERVER['DOCUMENT_ROOT']."/pages/pm/reporting/alldruginventory.php";

include  $_SERVER['DOCUMENT_ROOT']."/template.inc.in.php";

