<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/7/15
 * Time: 3:09 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/AlertDAO.php';

$dao = new AlertDAO();

if($_POST){
    if($_POST['action']=="read"){
        exit(json_encode( $dao->dismiss($_POST['id']) ));
    }
}else {
    $alerts = $dao->getForPatient($_GET['pid'], FALSE);
    $patient = (new PatientDemographDAO())->getPatient($_GET['pid'], FALSE, NULL, NULL);
?>
<div style="width:500px">
    <h6><span><?= count($alerts)?></span> alerts for <?= $patient->getFullname() ?></h6>

    <?php if(count($alerts) > 0){?>

        <?php foreach($alerts as $alert){//$alert=new Alert();?>
            <div class="alert-box warning">
                <span><?=$alert->getType() ?> </span>
                <code class="fadedText"><?= date("d M, Y", strtotime($alert->getTime())) ?></code>
                <?= $alert->getMessage() ?>
<!--                <p class="fadedText">--><?= !is_null($alert->getReadBy()) ? 'Marked by '.$alert->getReadBy()->getFullname() : ''?><!--</p>-->
                <a href="javascript:;" class="dismiss pull-right" data-alert-id="<?= $alert->getId()?>">Clear</a>
            </div>
        <?php }?>
    <?php }?>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $(".dismiss").click(function(e){
            $this = $(this);
            if(!e.handled){
                $.post('/alerts.php',{action:'read', id:$this.data("alert-id")}).success(function (data) {
                    if(data == "true"){
                        $this.parent().remove();
                        $('h6 span').html($('.alert-box.warning').length);
                        if($('.alert-box.warning').length == 0){
                            $('.abnormal').parents('.pull-right').remove();
                        }
                    } else {
                        Boxy.alert("An error occurred while acknowledging alert");
                    }
                }, 'json').error(function (data) {
                    Boxy.alert("Error acknowledging alert");
                });
                e.handled = true;
            }
        });
    })
</script>
<?php }?>