jQuery(document).ready(function($) {
	$.ajax({
		url: lvips_ajax_object_log.ajax_url,
		type: 'POST',
		data: {
			action: 'lvips_log_ip',
			nonce: lvips_ajax_object_log.nonce
		},
		success: function(response) {
			console.log(response.data);
		}
	});
});