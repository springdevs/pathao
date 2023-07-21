jQuery(document).ready(function ($) {
	$('#pathao-setup').on('submit', async function (e) {
		e.preventDefault();

		$('#submit').prop('disabled', true);
		$('.pathao-shipping-spinner').addClass('is-active');
		$('.notice').remove();

		await $.ajax({
			type: 'post',
			dataType: 'json',
			url: pathao_admin_obj.ajax_url,
			data: {
				action: 'setup_pathao',
				client_id: $('#pathao_client_id').val(),
				client_secret: $('#pathao_client_secret').val(),
				client_username: $('#pathao_client_username').val(),
				client_password: $('#pathao_client_password').val(),
				sandbox_mode: $('#pathao_sandbox_mode').is(':checked'),
			},
			success: function (res) {
				if (res.success) {
					$('.pathao-notice').after(
						'<div class="notice notice-success is-dismissible"><p><b>Token generated successfully !!</b></p><button id="dismiss-message" class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>'
					);
					$('#dismiss-message').click(function (event) {
						event.preventDefault();
						$('.' + 'notice-success').fadeTo(100, 0, function () {
							$('.' + 'notice-success').slideUp(100, function () {
								$('.' + 'notice-success').remove();
							});
						});
					});
					$('#pathao_access_token').val(res.data.access_token);
					$('#pathao_refresh_token').val(res.data.refresh_token);
				} else {
					$('.pathao-notice').after(
						'<div class="notice notice-error is-dismissible"><p><b>' +
							res.data.message +
							'</b></p><button id="dismiss-message" class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>'
					);
					$('#dismiss-message').click(function (event) {
						event.preventDefault();
						$('.' + 'notice-error').fadeTo(100, 0, function () {
							$('.' + 'notice-error').slideUp(100, function () {
								$('.' + 'notice-error').remove();
							});
						});
					});
				}
			},
			error: function (error) {
				console.log(error);
			},
		});

		$('#submit').prop('disabled', false);
		$('.pathao-shipping-spinner').removeClass('is-active');
	});

	if ($('#pathao_city').val() !== '') {
		getZones($('#pathao_city').val());
	}

	$('#pathao_city').on('change', function () {
		const city = $(this).val();

		getZones(city);
	});

	$('#pathao_zone').on('change', function () {
		const zone = $(this).val();

		if (zone == '') {
			$('#pathao_area_select').hide();
			return false;
		}

		$('#pathao_submit_shipping').prop('disabled', true);
		$('.pathao-shipping-spinner').addClass('is-active');

		$.ajax({
			type: 'post',
			dataType: 'json',
			url: pathao_admin_obj.ajax_url,
			data: {
				action: 'get_zone_areas',
				zone: zone,
				order_id: pathao_admin_obj.order_id,
			},
			success: function (res) {
				$('#pathao_area').html('');
				$.each(res.areas, function (key, value) {
					$('#pathao_area').append(
						'<option value="' +
							value.area_id +
							'"' +
							`${value.area_id == res.value ? ' selected' : ''}` +
							'>' +
							value.area_name +
							'</option>'
					);
				});

				$('#pathao_area_select').show();
				$('#pathao_submit_shipping').prop('disabled', false);
				$('.pathao-shipping-spinner').removeClass('is-active');
			},
			error: function (error) {
				console.log(error);
			},
		});
	});

	$('#pathao_submit_shipping').on('click', function () {
		const order_id = $('#pathao_order_id').val();
		const nonce = $('#pathao_send_order_nonce').val();
		const store = $('#pathao_store').val();
		const delivery_type = $('#pathao_delivery_type').val();
		const city = $('#pathao_city').val();
		const zone = $('#pathao_zone').val();
		const area = $('#pathao_area').val();
		const special_instruction = $('#pathao_special_instruction').val();
		const amount = parseFloat($('#pathao_amount').val());
		const item_weight = parseFloat($('#pathao_weight').val());
		const item_type = $('#pathao_item_type').val();

		if (order_id == '') {
			$.toast({
				position: 'bottom-center',
				text: 'Please select order',
				icon: 'error',
				hideAfter: 6000,
			});
			return false;
		}

		if (store == '') {
			$.toast({
				position: 'bottom-center',
				text: 'Please select store',
				icon: 'error',
				hideAfter: 6000,
			});
			return false;
		}

		if (delivery_type == '') {
			$.toast({
				position: 'bottom-center',
				text: 'Please select delivery type',
				icon: 'error',
				hideAfter: 6000,
			});
			return false;
		}

		if (item_type == '') {
			$.toast({
				position: 'bottom-center',
				text: 'Please select item type',
				icon: 'error',
				hideAfter: 6000,
			});
			return false;
		}

		if (city == '') {
			$.toast({
				position: 'bottom-center',
				text: 'Please select city',
				icon: 'error',
				hideAfter: 6000,
			});
			return false;
		}

		if (zone == '') {
			$.toast({
				position: 'bottom-center',
				text: 'Please select zone',
				icon: 'error',
				hideAfter: 6000,
			});
			return false;
		}

		if (area == '') {
			$.toast({
				position: 'bottom-center',
				text: 'Please select area',
				icon: 'error',
				hideAfter: 6000,
			});
			return false;
		}

		if (item_weight == '') {
			$.toast({
				position: 'bottom-center',
				text: 'Please select total weight',
				icon: 'error',
				hideAfter: 6000,
			});
			return false;
		}

		if (!isInteger(item_weight) && !isFloat(item_weight)) {
			$.toast({
				position: 'bottom-center',
				text: 'Total weight should be numeric',
				icon: 'error',
				hideAfter: 6000,
			});
			return false;
		}

		if (amount == '') {
			$.toast({
				position: 'bottom-center',
				text: 'Please select amount',
				icon: 'error',
				hideAfter: 6000,
			});
			return false;
		}

		if (!isInteger(amount)) {
			$.toast({
				position: 'bottom-center',
				text: 'Amount should be numeric',
				icon: 'error',
				hideAfter: 6000,
			});
			return false;
		}

		$('#pathao_submit_shipping').prop('disabled', true);
		$('.pathao-shipping-spinner').addClass('is-active');

		$.ajax({
			type: 'post',
			dataType: 'json',
			url: pathao_admin_obj.ajax_url,
			data: {
				action: 'send_order_to_pathao',
				order_id: order_id,
				nonce: nonce,
				store: store,
				delivery_type: delivery_type,
				city: city,
				zone: zone,
				area: area,
				special_instruction: special_instruction,
				item_weight: item_weight,
				item_type: item_type,
				amount: amount,
			},
			success: function (res) {
				$('#pathao_submit_shipping').prop('disabled', false);
				$('.pathao-shipping-spinner').removeClass('is-active');
				if (res.success) {
					$.toast({
						position: 'bottom-center',
						text: res.message,
						icon: 'success',
						hideAfter: 6000,
					});
					setTimeout(function () {
						window.location.reload();
					}, 3000);
				} else {
					const errors = res.errors;
					$.each(errors, function (key, value) {
						$.toast({
							position: 'bottom-center',
							text: value[0],
							icon: 'error',
							hideAfter: 6000,
						});
					});
				}
			},
			error: function (error) {
				console.log(error);
			},
		});
	});

	function getZones(city) {
		$('#pathao_area_select').hide();
		$('#pathao_area').find('option').remove();
		if (city == '') {
			$('#pathao_zone_select').hide();
			return false;
		}

		$('#pathao_submit_shipping').prop('disabled', true);
		$('.pathao-shipping-spinner').addClass('is-active');

		$.ajax({
			type: 'post',
			dataType: 'json',
			url: pathao_admin_obj.ajax_url,
			data: {
				action: 'get_city_zones',
				city: city,
				order_id: pathao_admin_obj.order_id,
			},
			success: function (res) {
				$('#pathao_zone').html('');
				$.each(res.zones, function (key, value) {
					$('#pathao_zone').append(
						'<option value="' +
							value.zone_id +
							'"' +
							`${value.zone_id == res.value ? ' selected' : ''}` +
							'>' +
							value.zone_name +
							'</option>'
					);
				});

				$('#pathao_zone_select').show();
				$('#pathao_submit_shipping').prop('disabled', false);
				$('.pathao-shipping-spinner').removeClass('is-active');
				$('#pathao_zone')
					.val(res.value ?? res.zones[0].zone_id)
					.change();
			},
			error: function (error) {
				console.log(error);
			},
		});
	}
});

function isInteger(n) {
	return n === +n && n === (n | 0);
}

function isFloat(n) {
	return Number(n) === n && n % 1 !== 0;
}
