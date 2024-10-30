<?php
function lvips_log_ip_via_ajax() {
	check_ajax_referer('lvips_log_visitor_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . 'lvips_visitor_ips';

    // Check if the cookie 'ip_logged' exists
    if (isset($_COOKIE['ip_logged'])) {
        wp_send_json_success('IP already logged');
        return; // IP already logged, exit early
    }

    // Get the visitor's IP address
    $ip_address = '';
	if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip_address = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
	} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip_address = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		$ip_address = explode( ',', $ip_address )[0]; // Take the first IP in the list
	} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) && ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip_address = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}

    // Validate IP address
    if ( filter_var( $ip_address, FILTER_VALIDATE_IP ) ) {
        // Insert the IP address into the custom table
        $wpdb->insert(
            $table_name,
            array(
                'ip_address' => $ip_address,
                'visit_time' => current_time('mysql', true), // Store time in UTC
            )
        );

        // Set a cookie to mark the IP as logged (expires when the browser is closed)
        setcookie('ip_logged', true, 0, '/', '', is_ssl(), true);
    }

    // Send a response
    wp_send_json_success('IP logged');
}
add_action( 'wp_ajax_nopriv_lvips_log_ip', 'lvips_log_ip_via_ajax' );
add_action( 'wp_ajax_lvips_log_ip', 'lvips_log_ip_via_ajax' );