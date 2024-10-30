<?php
function lvips_get_visitor_ips() {
	if (!current_user_can('administrator')) {
		wp_send_json_error('You do not have permission to access this feature.');
		return;
	}
	
	check_ajax_referer('lvips_fetch_visitor_nonce', 'nonce');

    global $wpdb;

    // Table name
    $table_name = $wpdb->prefix . 'lvips_visitor_ips';

    // Pagination parameters
    $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $offset = isset($_POST['start']) ? intval($_POST['start']) : 0;

    // Get the total number of records
    $total_records = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");

    // Query to get the visitor IPs with pagination
    $results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id, ip_address, visit_time FROM {$table_name} ORDER BY visit_time DESC LIMIT %d OFFSET %d",
			$limit, $offset
		)
	);

    // Convert the visit_time to the timezone set in WordPress settings
	$data = array();
	$wp_timezone = wp_timezone(); // Get the WordPress timezone object
	$server_timezone = new DateTimeZone(date_default_timezone_get()); // Get the server timezone

	foreach ($results as $row) {
		// Create a DateTime object using the server's timezone (since the time may be stored in the server's timezone)
		$visit_time = new DateTime($row->visit_time, $server_timezone);

		// Convert the visit_time to the WordPress timezone
		$visit_time->setTimezone($wp_timezone);

		// Format the time in 12-hour format with AM/PM
		$data[] = array(
			'id' => $row->id,
			'ip_address' => $row->ip_address,
			'visit_time' => $visit_time->format('m/d/Y g:i A') // Format to 12-hour with AM/PM
		);
	}

    // Prepare the data for DataTables
    $response = array(
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal" => $total_records,
        "recordsFiltered" => $total_records, // You can add search logic here to filter the records
        "data" => $data
    );

    // Return the response as JSON
    wp_send_json($response);
}
add_action('wp_ajax_lvips_get_visitor_ips', 'lvips_get_visitor_ips');