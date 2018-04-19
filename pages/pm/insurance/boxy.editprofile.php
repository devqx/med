<?php

if (!isset($_SESSION)) {
    session_start();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsurerDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$provider = (new InsurerDAO())->getInsurer($_GET['id'], TRUE);

if ($_POST) {
    require_once $_SERVER ['DOCUMENT_ROOT'] . "/functions/utils.php";
    $provider = (new InsurerDAO())->getInsurer($_POST['id'], TRUE);
    if (!is_blank($_POST['companyname'])) {
        $provider->setName($_POST['companyname']);
    } else {
        exit("error:Company name is required");
    }
    if (!is_blank($_POST['address'])) {
        $provider->setAddress($_POST['address']);
    } else {
        exit("error:Address is required");
    }
    if (!is_blank($_POST['phone'])) {
        $provider->setPhone($_POST['phone']);
    } else {
        exit("error:phone is required");
    }
    if (!is_blank($_POST['mail'])) {
        $provider->setEmail($_POST['mail']);
    } else {
        exit("error:mail is required");
    }
    
    if (!is_blank($_POST['erp_product_id'])) {
        $provider->setErpProduct($_POST['erp_product_id']);
    }

    $updated = (new InsurerDAO())->update($provider);
    if ($updated !== NULL) {
        exit("success: Insurance Profile updated");
    }
    exit("error:Failed to update provider");
}



?>

<div id="addprofile">
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>"
          onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done});">
        <label><span id="disp"></span></label>
        <label>Company Name:<input name="companyname" type="text" value="<?= $provider->getName() ?>">
            <input name="id" type="hidden" value="<?= $provider->getId() ?>"></label>
        <label>Address:<input name="address" type="text" value="<?= $provider->getAddress() ?>"></label>
        <label>Contact Phone:<input name="phone" type="text" value="<?= $provider->getPhone() ?>"></label>
        <label>E-mail:<input name="mail" type="text" value="<?= $provider->getEmail() ?>"></label>
        <label>Erp Product:<input name="erp_product_id" type="text" value="<?= $provider->getErpProduct() ?>"></label>

        <div class="btn-block">
            <button type="submit" class="btn" name="update">Update Profile &raquo;</button>
            <button type="button" class="btn-link cancelBtn" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
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
            $('#disp').html('<span class="alert alert-info">' + status_[1] + '</span>');
            showTabs(1);
            setTimeout(function () {
                Boxy.get($('.cancelBtn')).hideAndUnload();
            }, 500);
        }
        else {
            $('#disp').html('<span class="alert alert-error">' + status_[1] + '</span>');
        }
    }
</script>