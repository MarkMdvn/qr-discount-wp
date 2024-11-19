<?php

if (!function_exists('add_custom_role_capabilities')) {
    function add_custom_role_capabilities() {
        $role = get_role('administrator');
        if ($role) {
            $role->add_cap('verify_qr', true);
        }
    }
}

add_action('init', 'add_custom_role_capabilities');

class QR_Verify {

    public function __construct() {
        add_shortcode('verify_qr_code', array($this, 'verify_qr_code_shortcode'));
    }

    public function verify_qr_code_shortcode() {
        if (!current_user_can('verify_qr')) {
            return 'Unauthorized access. Only authorized personnel can perform this action.';
        }

        $output = '<h1>Verify Discount Code</h1>';
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

        // Handle form submission for numeric code verification
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['numeric_code'])) {
            $numeric_code = sanitize_text_field($_POST['numeric_code']);
            $user_query = new WP_User_Query(array('meta_key' => 'unique_discount_code', 'meta_value' => $numeric_code));
            $users = $user_query->get_results();
            if (!empty($users)) {
                $user_id = $users[0]->ID;
                $user_info = get_userdata($user_id);
                $user_name = $user_info->display_name;
                $output .= "<p>The code exists. The client is: {$user_name}.</p>";
                $output .= '<a href="' . esc_url(add_query_arg('user_id', $user_id, get_permalink())) . '" class="button">Apply Discount</a>';
                return $output;
            } else {
                $output .= '<p>No matching code found. Please try again.</p>';
                return $output;
            }
        }

        // If there's no user_id, show the form to input the numerical code
        if (!$user_id) {
            return $output . '<form method="post">
                                 Enter Numerical Code: <input type="text" name="numeric_code" required>
                                 <button type="submit">Verify Code</button>
                             </form>';
        }

        // Fetch user meta data only if user_id is valid
        $qr_code_url = get_user_meta($user_id, 'qr_code_url', true);
        $qr_code_used = get_user_meta($user_id, 'qr_code_used', true);
        $unique_discount_code = get_user_meta($user_id, 'unique_discount_code', true);
        $remaining_discount = get_user_meta($user_id, 'remaining_discount', true);

        // Display QR Code and current discount details
        $output .= '<img src="' . esc_url($qr_code_url) . '" alt="QR Code" style="width:200px;height:200px;"><br>';
        $output .= '<p>Unique Discount Code: ' . esc_html($unique_discount_code) . '</p>';
        $output .= '<p>Remaining Discount: €' . esc_html($remaining_discount) . '</p>';

        if ($qr_code_used) {
            return $output . '<p>This discount code has already been fully used.</p>';
        }

        // Handle form submission for updating discount after confirming there's a user_id
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_discount'])) {
            $amount_used = floatval($_POST['amount_used']);
            if ($amount_used > $remaining_discount) {
                $output .= '<p>Amount exceeds available discount.</p>';
            } else {
                $new_amount = $remaining_discount - $amount_used;
                update_user_meta($user_id, 'remaining_discount', $new_amount);
                if ($new_amount <= 0) {
                    update_user_meta($user_id, 'qr_code_used', 'yes');
                }
                $output .= '<p>Discount updated successfully. New Remaining Discount: €' . $new_amount . '</p>';
                // Refresh the page to reflect the updated discount without resubmitting the form
                $redirect_url = add_query_arg('user_id', $user_id, get_permalink());
                echo '<script>window.location.href = "' . esc_js($redirect_url) . '";</script>';
                return;
            }
        }

        // Form for updating discount
        $output .= '<form method="post">
                        Reduce Amount: <input type="number" name="amount_used" step="0.01" min="0.01" max="' . esc_attr($remaining_discount) . '" required>
                        <input type="hidden" name="user_id" value="' . esc_attr($user_id) . '">
                        <button type="submit" name="update_discount">Update Discount</button>
                    </form>';

        return $output;
    }
}

new QR_Verify();




?>