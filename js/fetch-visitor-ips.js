jQuery(document).ready(function($) {
    if ($('#lvips-visitor-ips-table').length) {
        $('#lvips-visitor-ips-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": lvips_ajax_object_fetch.ajax_url,
                "type": "POST",
                "data": {
                    "action": "lvips_get_visitor_ips",
					"nonce": lvips_ajax_object_fetch.nonce,
                }
            },
            "columns": [
                { "data": "ip_address" },
                { "data": "visit_time" }
            ]
        });
    }
});