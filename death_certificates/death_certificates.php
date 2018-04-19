<?php
exit;
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DeathDAO.php';
if(isset($_GET['pid'])){
    $certificates = (new DeathDAO())->findPatient($_GET['pid']);
} else {
    $certificates = (new DeathDAO())->all();
}
?>
<br>
<div class="document">
<p><button class="btn" type="button" id="_attch">New Certificate</button> </p>
<table class="t3 table table-striped">
    <thead><tr><th style="width: 10%">Date</th><th>Certificate #</th><?php if(!isset($_GET['pid'])){?><th style="width: 30%">Patient</th><?php }?><th style="width: 2%">*</th></tr></thead>
    <tbody>
    <?php foreach ($certificates as $c) {$c=new Death();?>
        <tr><td><?= date("Y/m/d", strtotime($c->getDateAdded()))?></td><td><?= $c->getNote() ?></td><?php if(!isset($_GET['pid'])){?><td><?= $c->getPatient()->getFullname() ?></td><?php }?><td><a class="pdf_viewer" href="/documents/attachment.php?id=<?= $c->getId()?>">View</a> </td></tr>
    <?php }?>
    </tbody>
</table>
</div>
<script>
    $(document).ready(function(){
        $('table.t3').dataTable();
        $("#_attch").click(function (e) {
            if(!e.handled){
                Boxy.load('new.php<?php if(isset($_GET['pid'])){?>?pid=<?= $_GET['pid']?><?php }?>', {title:"New Attachment", afterHide: function(){
                    <?php if(isset($_GET['pid'])){?>showTabs(15);<?php }?>
                    <?php if(!isset($_GET['pid'])){?>location.reload();<?php }?>
                }});
                e.handled = true;
            }
        })
    });
</script>
