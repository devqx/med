<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/12/15
 * Time: 5:22 PM
 */

if($_POST){
    require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/class.staff.php';
    require_once $_SERVER['DOCUMENT_ROOT'] .'/functions/utils.php';
    if(is_blank($_POST['password0']) ||is_blank($_POST['password1']) ){
        exit("error:Enter new password and confirm it");
    } else if($_POST['password0'] !== $_POST['password1']){
        exit("error:Passwords do not match");
    }else {
        $staff = (new StaffManager());

        require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
        $pdo = (new MyDBConnector())->getPDO();
        $newPassword = password_hash($_POST['password0'], PASSWORD_BCRYPT);
        $sql = "UPDATE staff_directory SET pswd='$newPassword', `status`='active' WHERE (username = '" . base64_decode($_POST['username']) . "' OR email = '" . base64_decode($_POST['username']) . "')";
        $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt->execute();

        exit($staff->doLogin(base64_decode($_POST['username']), $_POST['password0']));
    }

    //if password set is successful, call `doLogin($username, $pswd, $next = NULL)` again
}

?>
<div>
<!--    <span class="warning-bar"></span>-->
    <span class="output">You must change your password to continue</span>

    <form method="post" action="login_change_password.php" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : finished})">
        <label>New Password<input type="password" name="password0"></label>
        <label>Confirm New Password<input type="password" name="password1"></label>
        <input type="hidden" name="username" value="<?= $_GET['1']?>">
        <input type="hidden" name="password" value="<?= $_GET['2']?>">
        <div class="btn-block">
            <button type="submit" class="btn">Update Password</button>
            <button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Skip</button>
        </div>
    </form>
</div>
