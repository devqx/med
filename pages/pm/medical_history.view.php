<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/22/15
 * Time: 11:37 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/HistoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/HistoryTemplateDataDAO.php';
$history = (new HistoryDAO())->get($_GET['id']);
?>
<section>
    <table class="table table-striped">
        <tr>
            <th>Template Label</th><td><?= $history->getTemplate()->getLabel()?></td>
        </tr>
        <tr><th colspan="2">Data Elements</th></tr>
        <?php foreach((new HistoryTemplateDataDAO())->byTemplate($history->getTemplate()->getId()) as $data){//$data = new HistoryTemplateData();?>
            <tr><td><?=$data->getLabel() ?></td><td><?= ucwords($data->getDataType()) ?></td></tr>
        <?php }?>
    </table>
</section>

