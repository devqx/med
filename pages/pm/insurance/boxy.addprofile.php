<?php
if (!isset($_SESSION)) {
    session_start();
}
if ($_POST) {
//    sleep(1);
    require_once $_SERVER ['DOCUMENT_ROOT'] . "/functions/utils.php";
    require_once $_SERVER ['DOCUMENT_ROOT'] . "/classes/Insurer.php";
    require_once $_SERVER ['DOCUMENT_ROOT'] . "/classes/DAOs/InsurerDAO.php";

    $provider = new Insurer();
    if (!is_blank($_POST['companyname'])) {
        $provider->setName($_POST['companyname']);
    } else {
        exit("error:Company name is required");
    }
    if (!is_blank($_POST['phone'])) {
        $provider->setPhone($_POST['phone']);
    } else {
        exit("error:Contact phone is required");
    }
    if (!is_blank($_POST['address'])) {
        $provider->setAddress($_POST['address']);
    } else {
        exit("error:Address is required");
    }

    if (!is_blank($_POST['mail'])) {
        $provider->setEmail($_POST['mail']);
    } else {
        exit("error:Contact email is required");
    }
    
    if (!is_blank($_POST['erp_product_id'])){
    	$provider->setErpProduct($_POST['erp_product_id']);
    }
    
    $newCoy = (new InsurerDAO())->add($provider);

    if ($newCoy !== NULL) {
        exit("success: Insurance Profile added");
    }
    exit("error:Failed to create provider");
}

?>

<div id="addprofile">
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>"
          onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done});">
        <label>
            <span id="msg"></span>
        </label>
        <label>
            Company Name: <span class="required-text">*</span>
            <input name="companyname" type="text">
        </label>
        <label>
            Contact Phone: <span class="required-text">*</span>
            <input name="phone" type="text">
        </label>
        <label>
            Address: <span class="required-text">*</span>
            <input name="address" type="text">
        </label>
        <label>
            E-mail: <span class="required-text">*</span>
            <input name="mail" type="email">
        </label>
	    <label>
            Erp Product:
            <input name="erp_product_id" type="text">
        </label>

        <div class="btn-block">
            <button class="btn" type="submit" name="update">Create Profile &raquo;</button>
            <button class="btn-link cancelBtn" type="button" onclick="Boxy.get(this).hide()">Cancel</button>
        </div>
    </form>
</div>
<script type="text/javascript">
    function start() {
        $('#msg').html('<img src="/img/loading.gif"/> <em>please wait ...</em>');
    }
    function done(s) {
        var status_ = s.split(":");
        if (status_[0] === 'success') {
            $('#msg').html('<span class="alert alert-info">' + status_[1] + '</span>');
            showTabs(1);
            setTimeout(function () {
                Boxy.get($('.cancelBtn')).hideAndUnload();
            }, 500);
        } else {
            $('#msg').html('<span class="alert alert-error">' + status_[1] + '</span>');
        }
    }
</script>