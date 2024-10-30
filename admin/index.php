<?php
function lvips_visitor_ips_admin_menu() {
    add_menu_page(
        'Visitor IPs', // Page title
        'Visitor IPs', // Menu title
        'manage_options', // Capability
        'lvips_visitor-ips', // Menu slug
        'lvips_visitor_ips_page', // Function to display content
        'dashicons-visibility', // Icon
        20 // Position
    );
}
add_action('admin_menu', 'lvips_visitor_ips_admin_menu');

function lvips_visitor_ips_page() {
    ?>
    <div class="wrap">
        <h1>Visitor IPs</h1>
        <table id="lvips-visitor-ips-table" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>IP Address</th>
                    <th>Visit Time</th>
                </tr>
            </thead>
        </table>
    </div>
    <?php
}