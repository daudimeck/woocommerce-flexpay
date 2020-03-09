(function ($) {
	'use strict';

	$(function () {

		if ($('input[name="variation_id"]').val) {
			$('input[name="variation_id"]').parent().find('button.flex_lpp_btn').attr('variation_id', $(this).val())
		}

		$('body').on('change', 'input[name="variation_id"]', function (event) {
			$(this).parent().find('button.flex_lpp_btn').attr('variation_id', $(this).val())
		})
		$('body').on('click', 'button.flex_lpp_btn', function (event) {
			event.preventDefault();
			this.blur(); // Manually remove focus from clicked link.
			var e = $(this);
			var productId = $(this).attr('product_id');
			var variationId = $(this).attr('variation_id');
			e.attr('disabled', 'disabled');
			$.ajax({
				url: vars.ajax_url,
				type: 'GET',
				data: { action: 'load_product', 'product': productId, 'variation': variationId },
				success: function (data) {
					$(data).appendTo('body').jqModal();
					e.removeAttr('disabled');
					/* $(data).find('#reviews .card').appendTo('#reviews'); */
				},
				error: function (data) {
					var response = $.parseJSON(data.responseText)
					e.removeAttr('disabled');
					alert(response.data); //or whatever
				}
			});

		})
		$('body').on('submit', 'form.lpp_booking', function (e) {
			e.preventDefault();

			var data = $(this).serialize() + '&action=lpp_make_booking';
			var f = $(this);
			f.find('button').attr('disabled', 'disabled');

			f.find('links').children('a').removeAttr('href');

			f.find('div.error').remove();


			$.ajax({
				url: vars.ajax_url,
				type: 'POST',
				data: data,
				headers: { "Access-Control-Allow-Headers": "x-requested-with" },
				success: function (data) {
					//send ajax request to flex
					data = $.parseJSON(data);
					$.ajax({
						url: vars.end_point,
						type: 'POST',
						data: data,
						dataType: 'json',
						success: function (resp) {

							f.find('button').removeAttr('disabled');
							f.find('links').children('a').attr('href', '#close-modal');

							$('<div class="success_modal"><img src="' + vars.plugin_url + '/public/css/assets/success.gif?2">' + resp.data.message + '<a href="#close-modal" class="close" rel="modal:close">close</a></div>').jqModal({
								closeExisting: true,
								showClose: false
							})

						},
						error: function (error) {
							var message = error.responseJSON.message_text || 'An error occurred. Please try again later'
							// var response = $.parseJSON(error.responseText)
							// alert(response);
							f.find('button').removeAttr('disabled');
							f.find('links').children('a').attr('href', '#close-modal');

							f.prepend('<div class="error">' + message + '</div>');
						}
					})

				},
				error: function (data) {
					var response = $.parseJSON(data.responseText)
					f.find('button').removeAttr('disabled');
					f.find('links').children('a').attr('href', '#close-modal');

					f.prepend('<div class="error">' + response.data + '</div>');
				}
			});
		})
	})

})(jQuery);
