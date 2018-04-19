<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/14/16
 * Time: 1:44 PM
 */
if ($_POST) {
    //todo
    require_once $_SERVER['DOCUMENT_ROOT']
        . '/ivf/classes/DAOs/GeneticRequestDAO.php';
    $requests = (new GeneticRequestDAO())->all();
    include_once "template.php";
    exit;
}
?>
<div class="row-fluid">
    <label class="span11"><input type="search" name="q" id="q"
                                 autocomplete="off"></label>
    <button class="btn span1" type="button" id="labSearchBtn">Find</button>
</div>
<div class="document"></div>
<script type="text/javascript">
    $(document).on('click', '#labSearchBtn', function (evt) {
        if (!evt.handled) {
            $.post('/pages/ivf/labs/labs_search.php', {q: $('#q').val()}, function (data) {
                $('.row-fluid + .document').html(data);
            });
            evt.handled = true;
        }
    });
</script>
