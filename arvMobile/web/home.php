<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/22/16
 * Time: 4:26 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FormDAO.php';
$data = (new FormDAO())->all();
?>
<div id="arvLinks" class="mini-tab small">
    <a class="tab small" href="javascript:" data-href="/arvMobile/web/tab/notes.php?pid=<?=$_GET['pid']?>">Notes</a>
    <a class="tab small" href="javascript:" data-href="/arvMobile/web/tab/drugs.php?pid=<?=$_GET['pid']?>">Drugs</a>
    <a class="tab small" href="javascript:" data-href="/arvMobile/web/tab/substitutions.php?pid=<?=$_GET['pid']?>">Substitutions/Switches</a>
    <?php foreach($data as $form){?>
    <a class="tab small" href="javascript:" data-href="/arvMobile/web/tab/form.php?pid=<?=$_GET['pid']?>&form_id=<?= $form->getId()?>"><?= $form->getName()?></a>
    <?php }?>
</div>
<div class="content_content"></div>

<script>
    $(document).ready(function () {
        tab(1, new Event('click'));
    });
    $(document).on('click', '#arvLinks a', function (e) {
        if(!e.handled){
            tab($('#arvLinks a').index(e.target)+1, e);
            e.handled = true;
        }
    });

    function tab(i, e){
        $('#arvLinks a').removeClass('on');
        if(!e.handled){
            var $this = $("#arvLinks a:nth-child("+i+")");
            $this.addClass('on');
            $('.content_content').load($this.data("href"), function (evt, status) {
                if(status !== "success"){
                    $('.content_content').html('<div class="warning-bar">Sorry, the requested resource failed to load</div>');
                }
            });
            e.preventDefault();
            e.handled = true;
            return false;
        }
    }
</script>