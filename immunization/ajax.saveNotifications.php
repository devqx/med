<?php
require $_SERVER['DOCUMENT_ROOT'] . '/classes/NotificationOptions.php';

if ($_REQUEST['pid']) {
    $ids = array();
    for ($i = 0; $i < $_REQUEST['counter']; $i++) {
        $ids[$i] = $_REQUEST['ch_' . $i];
    }
    $not = new NotificationOptions();
    if ($not->saveChanges($_REQUEST['pid'], $ids)) {
        echo "Changes Saved";
    }
} else {
    echo "Error!!!";
}

exit;