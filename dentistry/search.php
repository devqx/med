<?php if($_POST){
    @session_start();
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientDentistryDAO.php';
    $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
    $pageSize = 10;
    $temp = (new PatientDentistryDAO())->search($_POST['q'], $page, $pageSize);
    $Requests = $temp->data;

    $totalSearch = $temp->total;
    include_once "template.php";
    ?>
    <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page+1?> of <?= ceil($totalSearch / $pageSize)?>)</div>
<div class="resultsPagerSearch no-footer dataTables_paginate">
    <div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
        <a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page +1 ) == 1)? "disabled":""?>">First <?= $pageSize ?> records</a>
        <a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page+1) <= 1)? "disabled":""?>">Previous <?= $pageSize ?> records</a>

        <a id="DataTables_Table_1_last" class="paginate_button next <?=(($page +1 ) == ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ceil($totalSearch / $pageSize) -1 ?>">Last <?= $pageSize ?> records</a>
        <a id="DataTables_Table_1_next" class="paginate_button next <?=(($page +1) >= ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ($page) +1 ?>">Next <?= $pageSize ?> records</a>
    </div>
</div>
<!-- yes i know: the opening div is in template.php -->
</div><?php
    exit;}
?>
<form method="post" action="<?=$_SERVER['SCRIPT_NAME']?>" onsubmit="return AIM.submit(this, {onStart:start, onComplete:done})">
    <div class="input-prepend">
        <input type="search" name="q" class="bigSearchField" style="width: 90%" autocomplete="off" placeholder="Search by patient details or Service Request Number">
        <button type="submit" class="btn" style="width: 9%">Search</button>
    </div>
</form>
<div id="results"></div>

<script type="text/javascript">
    $(document).on('click', '.resultsPagerSearch.dataTables_paginate a.paginate_button', function(e){
        var page = $(this).data("page");
        if(!$(this).hasClass("disabled") && !e.handled){
            $.post('/dentistry/search.php', {'page':page, 'q':$('input[name="q"][type="search"]').val()}, function(s){
                done(s);
            });
            e.handled = true;
        }
    });

    function start(){
        $("#results").html('<img src="/img/ajax-loader.gif"> Loading... ');
    }
    function done(s){
        $("#results").html(s);
        $("*[title]").tooltipster();
    }
</script>