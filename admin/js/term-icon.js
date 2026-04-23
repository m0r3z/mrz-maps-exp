(function ($) {
	'use strict';

	function wireRow($row) {
		var $url     = $row.find('#gmaps_aa_icon_url');
		var $id      = $row.find('#gmaps_aa_icon_id');
		var $preview = $row.find('.gmaps-aa-term-icon-preview');
		var frame    = null;

		$row.find('.gmaps-aa-term-icon-choose').on('click', function (e) {
			e.preventDefault();
			if (frame) { frame.open(); return; }

			frame = wp.media({
				title: 'Choisir une image',
				multiple: false,
				library: { type: 'image' },
				button: { text: 'Utiliser cette image' }
			});

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();
				$id.val(attachment.id);
				$url.val(attachment.url);
				$preview.html('<img src="' + attachment.url + '" alt="" style="max-width:60px;height:auto;" />');
			});

			frame.open();
		});

		$row.find('.gmaps-aa-term-icon-clear').on('click', function (e) {
			e.preventDefault();
			$id.val('');
			$url.val('');
			$preview.empty();
		});
	}

	$(function () {
		$('.gmaps-aa-term-icon').each(function () {
			wireRow($(this));
		});
	});
})(jQuery);
