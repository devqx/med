<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 2/19/15
 * Time: 2:54 PM
 */
require_once $_SERVER['DOCUMENT_ROOT']."/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DistributionList.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/DistributionListDAO.php';

function __format($output){
    $ret = array(
        'status'=>$output
    );
    return json_encode($ret);
}

if(isset($_REQUEST['q'])){
    $distList = (new DistributionListDAO())->getDistributionList($_REQUEST['q']);
}

if($_POST){
    $distlist = ucwords($_POST['distlist']);
    if(empty($distlist)){
        exit(__format("error|Enter distribution list name"));
    }
    if($_POST['sqlquery'] == ''){
        exit(__format("error|No sql query specified"));
    }
    $query = $_POST['sqlquery'];

    if(isset($_POST['_e']) && $_POST['_e'] == 'create'){
        $distlist_ = new DistributionList();
        $distlist_->setName($distlist);
        $distlist_->setSqlQuery($query);
        $distListId = (new DistributionListDAO())->add($distlist_);

        if($distListId != NULL) {
            exit(__format("success|Create passed"));
        }
        exit(__format("error|Create failed"));
    }
    elseif(isset($_POST['_e']) && $_POST['_e']=='update'){
        $distlist_ = new DistributionList($_REQUEST['q']);
        $distlist_->setName($distlist);
        $distlist_->setSqlQuery($query);
        $distListId = (new DistributionListDAO())->update($distlist_);
        if($distListId != NULL) {
            exit(__format("success|Update passed"));
        }

        exit(__format("error|Save failed"));
    }
}

?>

<div style="width: 660px">
    <form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart:start, onComplete:done})">
        <label>List Name: <input name="distlist" type="text" placeholder="Distribution List Name" value="<?=(isset($distList))? $distList->getName():'' ?>"> </label>
        <label>SQL Query: </label>
        <label><input type="text" name="sqlquery" id="sqlquery" placeholder="SQL Query" value="<?= ((isset($distList)) ? $distList->getSqlQuery() : '') ?>"></label>
        <div class="btn-block">
            <input type="hidden" name="_e" value="<?=(isset($_REQUEST['q'])) ? 'update':'create' ?>">
            <button class="btn" type="submit">Save List</button>
            <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>
    </form>
</div>
<script>
function start(){}
function done(a){
    var s = $.parseJSON(a);
    if(s.status && s.status.indexOf('error')!=-1){
        var data = s.status.split("|");
        Boxy.alert(data[1])
    }else {
        Boxy.get($('.close')).hideAndUnload(function () {
            setTimeout(function () {
                Boxy.get($('.close')).hideAndUnload();
                Boxy.load('/pages/messaging/boxy.distlist.dialog.php',{title:'Distribution List'});
            }, 5);
        });
    }
}
</script>