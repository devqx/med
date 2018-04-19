<form method="post" action="search_results.php" onsubmit="return AIM.submit(this, {onComplete:render})">
	<div class="input-prepend">
		<label><input type="search" name="q" class="bigSearchField" style="width: 92%" placeholder="find procedure by patient details">
			<button type="submit" class="btn">Search</button>
		</label>
	</div>
</form>

<div id="searchArea"></div>

<script type="text/javascript">
	function render(s) {
		$('#searchArea').html($(s).filter('#area').html());
		$(document).trigger('ajaxStop');
	}

	$(document).on('click', '.list5.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked && !$(this).hasClass("disabled")) {
			var page = $(this).data("page");

			$.post('/procedures/search_results.php', {page: page, q: $('[name="q"]').val()}, function (response) {
				$('#searchArea').html($(response).filter('#area').html());
			});
			e.clicked = true;
		}
	});
</script>
