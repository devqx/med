<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/3/16
 * Time: 9:37 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FormDAO.php';
$data = (new FormDAO())->get($_GET['form_id']);
if(!isset($_SESSION)){@session_start();}

if($_POST){
    $pdo = (new MyDBConnector())->getPDO();
    $pdo->beginTransaction();
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Form.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/FormPatientQuestionAnswer.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/FormQuestionOption.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/FormPatientQuestion.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FormQuestionDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FormQuestionOptionDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FormPatientQuestionDAO.php';
    foreach ($_POST['data'] as $questionId => $Value) {
        $pQuestion = new FormPatientQuestion();
        $pQuestion->setForm( new Form($_POST['form_id']) );
        $pQuestion->setPatient(new PatientDemograph($_POST['pid']));
        $pQuestion->setCreator(new StaffDirectory($_SESSION['staffID']));
        $pAnswers = [];
        $question = (new FormQuestionDAO())->get($questionId, $pdo);

        $pQuestion->setQuestion($question);
        $response = [];
        if (is_array($Value) && count($Value) > 0){
            foreach ($Value as $tplId => $comment) {
//                if (!is_blank($comment)) {
                    $pAnswer = new FormPatientQuestionAnswer();
                    $pAnswer->setFormQuestionOption( (new FormQuestionOptionDAO())->get($tplId, $pdo));
                    $pAnswer->setValue($comment);

                    $pAnswers[] = $pAnswer;
//                }
            }
        } else {
//            if (!is_blank($comment)) {
                $pAnswer = new FormPatientQuestionAnswer();
                $pAnswer->setFormQuestionOption( (new FormQuestionOptionDAO())->get($Value, $pdo));
                $pAnswer->setValue("on"); //it'll simply be the "on", because it's a radio/checkbox button

                $pAnswers[] = $pAnswer;
//            }
        }


        $pQuestion->setAnswers($pAnswers);

        if(count($pAnswers) > 0){
            $resp = (new FormPatientQuestionDAO())->add($pQuestion, $pdo);
            if (!$resp != NULL) {
                $pdo->rollBack();
                exit("error:Failed to save Data");
            }
//            if ( count($_POST['data']) !== count($pAnswers)){}
        }
    }

    $pdo->commit();
    exit("success:Saved Form Data");
}
?>
<!DOCTYPE html>
<html moznomarginboxes mozdisallowselectionprint>
<head>
    <meta charset="UTF-8">

    <script src="/js/jquery-2.1.1.min.js"></script>
    <script src="/js/jquery-migrate-1.2.1.min.js"></script>
    <script src="/assets/jquery-print/jQuery.print.js" type="text/javascript"></script>
    <script src="/js/webtoolkit.aim.js" type="text/javascript"></script>
    <link href="/style/def.css" rel="stylesheet" type="text/css"/>
    <link href="/style/bootstrap.css" rel="stylesheet" type="text/css"/>
    <link href="/style/font-awesome.css" rel="stylesheet" type="text/css"/>
    <meta name="viewport" content="width=device-width">
    <link href="/assets/sweetalert/dist/sweetalert.css" rel="stylesheet" type="text/css">
    <style>
        .document {
            padding: 20px;
            margin-top: 10px;
        }
        .question {
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }
        .question .radio, .question .checkbox {
            display: inline-block !important;
            margin-right: 20px;
        }
        body.stop-scrolling {
            overflow:inherit;
        }
    </style>
</head>
<body>
<div class="container">
    <section class="document">
        <h2><?= $data->getName() ?></h2>
        <hr>
        <?php
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FormQuestionDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FormQuestionOptionDAO.php';
        $HISTORY = $data->getComponents();

        foreach ($HISTORY as $H) { // $H = new FormQuestion(); ?>
        <form method="post" onsubmit="AIM.submit(this, {onStart: start, onComplete: complete})">
            <div class="question">
                <p><?= $H->getId()?>.(<?= $H->getQuestionTemplate()->getId()?>) <?= $H->getQuestionTemplate()->getLabel() ?></p>
                <?php foreach ((new FormQuestionOptionDAO())->byTemplate($H->getQuestionTemplate()->getId()) as $item) {
                     // $item=new FormQuestionOption();?>
                    <label class="<?=$item->getDataType()?>">
                    <?= (!in_array($item->getDataType(), ["radio", "boolean"])) ? $item->getId().'.'.$item->getLabel() : null ?>

                    <?php if(in_array($item->getDataType(), ["radio"])){?>
                        <?= $item->renderType("data[" . $H->getId() . "]", $item->getId()) ?>
                    <?php } else { ?>
                        <?= $item->renderType("data[" . $H->getId() . "][".$item->getId()."]", $item->getId()) ?>
                    <?php }?>

                    <?= (in_array($item->getDataType(), ["radio", "boolean"])) ? $item->getId().'.'.$item->getLabel() : null ?>
                    </label><?php } ?>
            </div>
            <?php } ?>
            <div style="margin-top: 10px;"></div>
            <button type="submit" class="btn">Submit</button>
            <input type="hidden" name="pid" value="<?= (int)$_GET['pid']?>">
        </form>
    </section>
</div>
<script src="/assets/sweetalert/dist/sweetalert.min.js"></script>
<script>
    function start() {
        swal({
            title:"",
            text: "Please wait...",
            imageUrl: "/img/loading.gif",
            showConfirmButton: false
        });
    }
    function complete(s) {
        var data = s.split(":");
        if(data[0]=="error"){
            swal("", data[1], "error");
        } else if(data[0]=="success") {
            swal({
                title: "",
                text: data[1],
                type: "success",
                showCancelButton: false,
                confirmButtonText: "OK",
                closeOnConfirm: false
            }, function(isConfirm){
                if (isConfirm) {
                    swal({
                        title: "",
                        text: "Please refresh the [<?=escape($data->getName())?>] tab on the patient's profile",
                        confirmButtonText: "OK",
                        closeOnConfirm: false
                    }, function(isConfirm){
                        if (isConfirm) {
                            window.close();
                        }
                    });
                }
            });
        } else {
            swal("", "A server error has occurred. Please check back later", "error");
        }
    }
</script>
</body>
</html>
