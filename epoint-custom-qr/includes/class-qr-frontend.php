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
        $user_id = get_current_user_id();
        $qr_code_url = get_user_meta($user_id, 'qr_code_url', true);  // Assume this is where you store the URL of the QR code
        echo '<h3>Your QR Code</h3>';
        if ($qr_code_url) {
            echo '<img src="' . esc_url($qr_code_url) . '" alt="Your QR Code">';
            // Display remaining discount amount if needed
            $remaining_discount = get_user_meta($user_id, 'remaining_discount', true); // Assume this is stored in user meta
            echo '<p>Remaining Discount: €' . esc_html($remaining_discount) . '</p>';
        } else {
            echo '<p>No QR Code found. Please contact support if this is an error.</p>';
        }
    }
}

new QR_Frontend();
?>