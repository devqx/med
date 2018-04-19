<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/3/16
 * Time: 9:59 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FormDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FormPatientQuestionDAO.php';
$form = (new FormDAO())->get($_GET['form_id']);
$filledForm = (new FormPatientQuestionDAO())->forPatient($_GET['pid'], $form->getId(), TRUE);
// todo group by date so that we can group all the questions into one per the time they were answered
?>
<a href="/arvMobile/web/tab/dialogs/forms.php?pid=<?= $_GET['pid'] ?>&form_id=<?= $form->getId()?>" target="_blank">Fill New</a>
<table class="table table-striped">
    <thead>
    <tr class="menu-head">
        <th>Date</th><th>Status</th><th>*</th>
    </tr>
    </thead>
    <tbody>
    <?php if( count($filledForm->data) > 0 ){
        $item = $filledForm->data[0];//$item=new FormPatientQuestion();
        $progress = count($filledForm->data) / count($form->getComponents()) * 100;?>
        <tr>
            <td width="20%" nowrap="nowrap"><?= date("Y/m/d h:ia", strtotime($item->getDate()))?></td>
            <td>
                <div class="progress progress-striped<?= ($progress < 100) ? ' active':''?>">
                    <div class="bar" style="width:<?= $progress ?>%;">(<?= $progress ?>% based on the number of questions answered)</div>
                </div></td>
            <td width="20%">Actions applicable</td>
        </tr>

    <?php } else {?>
        <tr>
            <td width="20%">N/A</td>
            <td>
                <div class="progress progress-striped active">
                    <div class="bar" style="width:0;">(0% based on the number of questions answered)</div>
                </div>
            </td>
            <td width="20%">Actions applicable</td>
        </tr>
    <?php }?>

    </tbody>
</table>
