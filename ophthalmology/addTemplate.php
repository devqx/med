<?php
if ($_POST) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/OphthalmologyTemplate.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/OphthalmologyTemplateData.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyTemplateDAO.php';

    $temp = new OphthalmologyTemplate();
    if (!empty($_POST['labTemplate'])) {
        $temp->setLabel($_POST['labTemplate']);
    } else {
        exit("error:Lab Template is required");
    }

    if (!empty($_POST['tempData'])) {
        $data = [];
        foreach (explode(",", $_POST['tempData']) as $d) {
            $dd = new OphthalmologyTemplateData();
            $t = explode("[", $d);
            $dd->setLabel(trim($t[0]));
            $dd->setReference(str_replace("]", "", trim($t[1])));
            $data[] = $dd;
        }
        $temp->setData($data);
    } else {
        exit("error:Ophthalmology Template is required");
    }
    $temp = (new OphthalmologyTemplateDAO())->add($temp);
    if ($temp !== NULL) {
        exit("ok:Save");
    } else {
        exit("error:Oops! Something went wrong");
    }
}
?>
<div style="width: 600px;">
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart': start, 'onComplete': done});">
        <label>New Ophthalmology Template
            <input type="text" name="labTemplate" id='labTemplateLabel' placeholder="Malaria Template" value="<?= $_GET['label'] ?>" /></label>
        <label>Template Fields
            <span class="right fadedText" style="margin-left: 20px"><em>eg Potassium [3.6 - 5.5 mmol/l], Triglycerides [< 1.71 mmol/l] </em></span>
        </label>
        <label><input type="text" name="tempData" id='tempData' placeholder="Comma separated list of fields " /></label>

        <div class="btn-block">
            <button type="submit" onclick="setAddedValue()" class="btn">Add</button>
            <button type="button" data-name="cancel" onclick="Boxy.get(this).hide()" class="btn-link">Cancel &raquo;</button>
        </div>
        <div id="mgniu"></div>

    </form>
</div>


<script type="text/javascript">
    function start() {
        $('#mgniu').html('<img src="/img/loading.gif"> please wait');
    }
    function done(s) {
        if (s.split(":")[0] == 'ok') {
            $('#mgniu').html('<span class="alert alert-info">Saved</span>');
            Boxy.get($(".close")).hideAndUnload();
        } else {
            var data = s.split(":");
            $('#mgniu').html('<span style="color:#C00;font-weight:bold;">' + data[1] + '</span>');
        }
    }
    $(document).ready(function () {
        $("#tempData").select2({
            tags: [],
            tokenSeparators: [","],
            width: '100%'}).change(function (e) {
            var val = e.val[e.val.length - 1];
            if (typeof val !== "undefined") {
                if (/.+\[.+\]/.test(val)) {
                    console.log(this.tags)
                } else {
                    $('#mgniu').html('<span class="alert alert-info">' + val + ' is not a valid entry <br /><strong>Format:</strong> Field [<em>Reference</em>] <br /><strong>eg</strong> Potassium [3.6 - 5.5 mmol/l]</span>');
                    $("#tempData").select2("val", e.val.slice(0, e.val.length - 1), true);
                    setTimeout(function () {
                        $('#mgniu').html('');
                    }, 6000);

                }
            }
        });
    });

    function setAddedValue() {
        newlyAdded = $('#labTemplateLabel').val();
    }
</script>