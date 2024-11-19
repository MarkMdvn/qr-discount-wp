<?php
require_once dirname(__DIR__, 4) . '/wp-load.php'; // Ensure this line is correctly adjusted or possibly even removed, as WordPress should already be loaded.


function add_custom_role_capabilities() {
    $role = get_role('gestor_de_la_tienda'); // Adjust the role ID as necessary
    if ($role) {
        $role->add_cap('verify_qr', true);
    }
}
add_action('init', 'add_custom_role_capabilities');

class QR_Verifier {


    public function __construct() {
        add_shortcode('verify_qr_code', array($this, 'verify_qr_code_shortcode'));
    }



    public function verify_qr_code_shortcode() {
        if (!current_user_can('manage_options')) {
            return 'Unauthorized access. Only administrators can perform this action.';
        }

        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        $qr_code_used = get_user_meta($user_id, 'qr_code_used', true);
        $remaining_discount = get_user_meta($user_id, 'remaining_discount', true);
        $output = '<h1>Verify QR Code</h1>';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount_used'])) {
            $amount_used = floatval($_POST['amount_used']);
            $new_amount = max($remaining_discount - $amount_used, 0);
            update_user_meta($user_id, 'remaining_discount', $new_amount);
            $message = 'Discount updated. Remaining Discount: €' . $new_amount;
        }

        if (isset($message)) {
            $output .= '<p>' . $message . '</p>';
        }
        $output .= '<p>Status: ' . ($qr_code_used ? 'Used' : 'Not Used') . '</p>';
        $output .= '<p>Remaining Discount: €' . esc_html($remaining_discount) . '</p>';

        if (!$qr_code_used) {
            $output .= '<form method="post">
                        Enter Discount Amount Used: <input type="number" name="amount_used" step="0.01"><br>
                        <button type="submit">Update Discount</button>
                    </form>';
        }

        return $output;
    }


}


new QR_Verifier();
?>
