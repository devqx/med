<?php
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/PatientHistoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/HistoryDAO.php';
$histories = (new PatientHistoryDAO())->forPatient($_GET['pid']);

$hH = (new HistoryDAO())->all();
?>
<section style="width: 880px">
    <div class="menu-head">Patient Medical History <span class="add1 pull-right"><a href="javascript:;">Add new Record</a></span></div>

    <?php if(count($histories)==0){?><p class="notify-bar">Sorry, no data data exists for this patient</p><?php } else {?>
    <table class="table table-bordered">
        <thead>

        </thead>
        <?php foreach($hH as $h){?>
            <tr>
                <th colspan="4" class="menu-head"><?=$h->getTemplate()->getLabel()?></th>
            </tr>
            <tr>
                <th>Date</th>
                <!--            <th>Category</th>-->
                <th>Data</th>
                <th>Comment</th>
                <th>By</th>
            </tr>
            <?php foreach($histories as $H){//$H=new PatientHistory();
            if($H->getHistory()->getTemplate()->getId()===$h->getTemplate()->getId()){?>
                <tr>
                    <td><?= date("Y/m/d h:iA", strtotime($H->getDate()))?></td>
                    <!--<td><?= $H->getHistory()->getTemplate()->getLabel()?></td>-->
                    <td><ul><?php foreach ($H->getData() as $data) { //$data=new PatientHistoryData();?>
                                <li><?= $data->getHistoryTemplateData()->getLabel()?></li>
                            <?php }?></ul></td>
                    <td>
                        <ul><?php foreach ($H->getData() as $data) { //$data=new PatientHistoryData();?>
                                <li><?= $data->getValue()?></li>
                            <?php }?></ul>
                    </td>
                    <td><?= $H->getCreator()->getFullname()?></td>
                </tr>
            <?php } }?>
        <?php }?>
    </table>
    <?php }?>

    <!--<a href="javascript:;" onclick="Boxy.get(this).hideAndUnload()" class="btn pull-right">Close</a>-->
</section>
<script>
    $(document).on('click', '.add1 a', function (e) {
        if(!e.handled){
            Boxy.load('/boxy.medical_history.new.php?pid=<?= $_GET['pid'] ?>', {title:'New Medical History Data'});
            e.handled = true;
        }
    })
</script>