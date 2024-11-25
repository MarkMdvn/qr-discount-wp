<?php

if (!function_exists('add_custom_role_capabilities')) {
    function add_custom_role_capabilities() {
        $roles = ['administrator', 'employer', 'empleador'];

        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                $role->add_cap('verify_qr', true);
            }
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
            return 'Acceso no autorizado. Solo el personal de la tienda puede realizar esta acción.';
        }

        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        $output = '';
        
        // Verificación del código numérico
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['numeric_code'])) {
            $numeric_code = sanitize_text_field($_POST['numeric_code']);
            $user_query = new WP_User_Query(array('meta_key' => 'unique_discount_code', 'meta_value' => $numeric_code));
            $users = $user_query->get_results();
            if (!empty($users)) {
                $user_id = $users[0]->ID;
                $user_info = get_userdata($user_id);
                $user_name = $user_info ? $user_info->display_name : 'Usuario';
                $output .= "<p>Código encontrado. Nombre del cliente: {$user_name}.</p>";
                $output .= '<a href="' . esc_url(add_query_arg('user_id', $user_id, get_permalink())) . '" class="button">Continuar</a>';
                return $output;
            } else {
                $output .= '<p>No se encontró el código. Vuelve a intentarlo..</p>';
                return $output;
            }
        }

        if (!$user_id) {
            return $output . '<form method="post">
                                 Introduce el código: <input type="text" name="numeric_code" required>
                                 <button type="submit">Verificar</button>
                             </form>';
        }

        $qr_code_url = get_user_meta($user_id, 'qr_code_url', true);
        $qr_code_used = get_user_meta($user_id, 'qr_code_used', true);
        $unique_discount_code = get_user_meta($user_id, 'unique_discount_code', true);
        $remaining_discount = get_user_meta($user_id, 'remaining_discount', true);

        $user_info = get_userdata($user_id);
        $user_name = $user_info ? $user_info->display_name : 'Unknown User';

        // Lo que se muestra en el shortcode.
        $output .= '<img src="' . esc_url($qr_code_url) . '" alt="QR Code" style="width:125px;height:125px;"><br>';
        $output .= '<p>Código: ' . esc_html($unique_discount_code) . '</p>';
        $output .= '<p>Nombre: ' . esc_html($user_name) . '</p>';
        $output .= '<p id="remainingDiscount">Cantidad restante: <strong>' . esc_html($remaining_discount) . ' €</strong></p>';

        if ($qr_code_used) {
            return $output . '<p>Este código de descuento ya se ha agotado.</p>';
        }

        // Actualización del descuento, funciona y todo
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_discount'])) {
            $amount_used = isset($_POST['amount_used']) ? floatval($_POST['amount_used']) : 0;
            $total_amount = isset($_POST['totalAmount']) ? floatval($_POST['totalAmount']) : 0;
            $charged_amount = isset($_POST['chargedAmount']) ? floatval($_POST['chargedAmount']) : 0;

            if ($total_amount < 0) {
                $output .= '<p>Error: La cantidad total no puede ser negativa.</p>';
                return; // Stop execution if the total amount is invalid
            }

            if ($amount_used > $remaining_discount) {
                $output .= '<p>La cantidad ingresada excede el límite del descuento.</p>';
            } else {
                $new_amount = $remaining_discount - $amount_used;
                update_user_meta($user_id, 'remaining_discount', $new_amount);
                if ($new_amount <= 0) {
                    update_user_meta($user_id, 'qr_code_used', 'yes');
                }
                $output .= '<p>Descuento actualizado con éxito. Nuevo descuento restante: €' . $new_amount . '</p>';
                $output .= '<p>Cantidad reducida: €' . number_format($amount_used, 2) . '</p>';

                // Get user info
                $user_info = get_userdata($user_id);
                if (!$user_info) {
                    error_log('Failed to retrieve user data for user ID: ' . $user_id);
                    return;
                }

                // Log transaction data
                $db_handler = new DB_Handler();
                $db_handler->log_transaction([
                    'client_user_id' => $user_id,
                    'client_user_name' => $user_info->display_name,
                    'client_user_email' => $user_info->user_email,  // Correctly getting the email
                    'numeric_discount_code' => $unique_discount_code,
                    'qr_code_url' => $qr_code_url,
                    'total_amount' => $total_amount,
                    'discount_applied' => $amount_used,
                    'amount_charged' => $charged_amount
                ]);

                // Refresh the page to reflect the updated discount without resubmitting the form
                $redirect_url = add_query_arg('user_id', $user_id, get_permalink());
                echo '<script>window.location.href = "' . esc_js($redirect_url) . '";</script>';
                return;
            }
        }


        $output .= '<form method="post" style="display:none;">
                        Valor a descontar: <input type="number" name="amount_used" step="0.01" min="0.01" max="' . esc_attr($remaining_discount) . '" required>
                        <input type="hidden" name="user_id" value="' . esc_attr($user_id) . '"> <br><br>
                        <button  type="submit" name="update_discount">Aplicar descuento</button>
                    </form>';

        return $output;
    }
}

new QR_Verify();

?>