<?php
class QR_Frontend {
    public function __construct() {
        add_action('init', array($this, 'add_qr_endpoint'));
        add_filter('query_vars', array($this, 'qr_query_vars'), 0);
        add_filter('woocommerce_account_menu_items', array($this, 'add_qr_link_my_account'));
        add_action('woocommerce_account_qr-code_endpoint', array($this, 'qr_content'));
    }

    public function add_qr_endpoint() {
        add_rewrite_endpoint('mi-qr', EP_ROOT | EP_PAGES);
    }

    public function qr_query_vars($vars) {
        $vars[] = 'qr-code';
        return $vars;
    }

    public function add_qr_link_my_account($items) {
        $items['qr-code'] = 'My QR Code';
        return $items;
    }

public function qr_content() {
    error_log('Entered qr_content function');  // Check log in wp-content/debug.log
    $user_id = get_current_user_id();
    error_log('User ID: ' . $user_id);  // Log the user ID to debug

    $qr_code_url = get_user_meta($user_id, 'qr_code_url', true);
    $unique_discount_code = get_user_meta($user_id, 'unique_discount_code', true);

    if ($qr_code_url && $unique_discount_code) {
        echo '<h3>Your QR Code and Discount Code</h3>';
        echo '<img src="' . esc_url($qr_code_url) . '" alt="Your QR Code"><br>';
        echo '<h3>Your Unique Discount Code: <strong>' . esc_html($unique_code) . '</strong></h3>';
    } else {
        echo '<p>No QR code or discount code found. Please contact support if this is an error.</p>';
        error_log('No QR or Discount code found for user ID: ' . $user_id);
    }
}
}

new QR_Frontend();
?>
