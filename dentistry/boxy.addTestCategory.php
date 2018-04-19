<?php
if ($_POST) {
    sleep(0.1);
    require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DentistryCategoryDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DentistryCategory.php';
    $cat = new DentistryCategory();
    if(!is_blank($_POST['category_name'])){
        $cat->setName($_POST['category_name']);
    }else {
        exit('error:Name is required');
    }
    if( (new DentistryCategoryDAO())->add( $cat ) !== NULL){
        exit("ok");
    }
    exit("error:Failed to save new category");
}
?>
<div>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {onStart: start, onComplete: done});">
        <label>New Test Category <span class="right">e.g. Category 1</span><input type="text" name="category_name" id='category_name_' /></label>

        <div class="btn-block">
            <button type="submit" class="btn" onclick="setAddedCat()">Add</button>
            <button type="button" data-name="cancel" onclick="Boxy.get(this).hide()" class="btn-link">Cancel &raquo;</button>
        </div>
        <div id="mgniu__"></div>

    </form>
</div>


<script type="text/javascript">
    function start() {
        $('#mgniu__').html('<img src="/img/loading.gif"> please wait');
    }
    function done(s) {
        if (s == 'ok') {
            $('#mgniu__').html('<span class="alert alert-info">Saved</span>');

        } else {
            var data = s.split(":");
            $('#mgniu__').html('<span style="color:#C00;font-weight:bold;">' + data[1] + '</span>');
        }
    }
    function setAddedCat() {
        newlyAdded = $('#category_name_').val();
    }
</script>