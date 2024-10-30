<?php
/**
 * Plugin Name: Log Visitor IPs
 * Description: Log IP addresses of website vistors and display them in your WordPress dashboard
 * Version: 1.0.0
 * Text Domain: log-visitor-ips
 * Author: Yekusiel Eckstein
 * Author URI: https://www.kornerstonemedia.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

/*
  Copyright (C) 2024 Kornerstone Media

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) { die( 'Forbidden' ); }

 include 'admin/index.php';
 include 'ajax/log-visitor-ips.php';
 include 'ajax/fetch-visitor-ips.php';

 function lvips_visitor_ips_admin_scripts() {
    // Enqueue jQuery and DataTables
    if (!wp_script_is('jquery', 'enqueued')) {
        wp_enqueue_script('jquery');
    }
	wp_enqueue_script('datatables-js', plugin_dir_url(__FILE__) . 'lib/datatables/js/dataTables.min.js', array('jquery'), null, true);
    wp_enqueue_style('datatables-css', plugin_dir_url(__FILE__) . 'lib/datatables/css/dataTables.min.css');

    // Enqueue custom scripts
    wp_enqueue_script('fetch-visitor-ips-js', plugin_dir_url(__FILE__) . 'js/fetch-visitor-ips.js', array('jquery', 'datatables-js'), time(), true);

    // Localize scripts with data
    wp_localize_script('fetch-visitor-ips-js', 'lvips_ajax_object_fetch', array(
        'ajax_url' => admin_url('admin-ajax.php'),
		'nonce'    => wp_create_nonce('lvips_fetch_visitor_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'lvips_visitor_ips_admin_scripts');

function lvips_visitor_ips_frontend_scripts() {
    // Enqueue custom scripts
    if (!wp_script_is('jquery', 'enqueued')) {
        wp_enqueue_script('jquery');
    }
    wp_enqueue_script('log-visitor-ips-js', plugin_dir_url(__FILE__) . 'js/log-visitor-ips.js', array('jquery'), time(), true);
	
    // Localize scripts with data
    wp_localize_script('log-visitor-ips-js', 'lvips_ajax_object_log', array(
        'ajax_url' => admin_url('admin-ajax.php'),
		'nonce'    => wp_create_nonce('lvips_log_visitor_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'lvips_visitor_ips_frontend_scripts');

function lvips_create_ip_log_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'lvips_visitor_ips';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        ip_address varchar(100) NOT NULL,
        visit_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
	
	// Save the database version for future updates
    add_option('lvips_visitor_ips_db_version', '1.0.0');
}
register_activation_hook(__FILE__, 'lvips_create_ip_log_table');

function lvips_update_ip_log_table() {
    global $wpdb;
    $installed_ver = get_option('lvips_visitor_ips_db_version');

    // Update the table if plugin version changes
    if ($installed_ver != '1.0.0') {
        lvips_create_ip_log_table();  // Recreate the table if necessary
        update_option('lvips_visitor_ips_db_version', '1.0.0');
    }
}
add_action('plugins_loaded', 'lvips_update_ip_log_table');