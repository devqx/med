<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/22/16
 * Time: 4:32 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/IVFNoteTemplateDAO.php';

$data = (new IVFNoteTemplateDAO())->all();
?>
<section style="width: 900px">
	<div class="three-column">
		<?php foreach ($data as $s) { ?>
			<div class="column tag"><?= $s->getTitle() ?>
				<span class="pull-right">| <a href="javascript:;" data-href="/ivf/profile/tabs/template.edit.php?id=<?= $s->getId() ?>" data-id="<?= $s->getId() ?>" class="editTemplateLink">Edit</a></span>
			</div>
		<?php } ?>
	</div>
</section>
<script type="text/javascript">
	$('.editTemplateLink').click(function (e) {
		Boxy.load($(e.target).data('href'), {
			afterHide: function () {
				setTimeout(function () {
					Boxy.get($('.close')).hideAndUnload(function () {
						Boxy.load('/ivf/profile/tabs/template.showall.php', {
							afterHide: function () {
								reloadTemplates();
							}
						});
					});
				}, 50);

			}
		});
	});
</script>
