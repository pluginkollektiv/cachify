jQuery(document).ready(
	function($) {

		$('.edit-cachify-status', '#misc-publishing-actions').click(
			function(e) {
				$(this)
					.next(':hidden')
					.slideDown('fast')
					.end()
					.hide();

				e.preventDefault();
			}
		);

		$('.save-cachify-status', '#misc-publishing-actions').click(
			function(e) {
				$(this)
					.parent()
					.slideUp('fast')
					.prev(':hidden')
					.show();

				$('#output-cachify-status').text(
					$('#cachify_status').children('option:selected').text()
				);

				e.preventDefault();
			}
		);

		$('.cancel-cachify-status', '#misc-publishing-actions').click(
			function(e) {
				$(this)
					.parent()
					.slideUp('fast')
					.prev(':hidden')
					.show();

				e.preventDefault();
			}
		);
	}
);