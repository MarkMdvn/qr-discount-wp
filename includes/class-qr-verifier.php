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

if (!session_id()) {
    session_start();
}

// Display flash messages
if (isset($_SESSION['flash_message'])) {
    echo '<script>alert("' . addslashes($_SESSION['flash_message']) . '");</script>';
    unset($_SESSION['flash_message']); // Clear the message after displaying
}

add_action('init', 'add_custom_role_capabilities');

class QR_Verify {

    public function __construct() {
        add_shortcode('verify_qr_code', array($this, 'verify_qr_code_shortcode'));
    }


    public function verify_qr_code_shortcode() {
        if (!current_user_can('verify_qr')) {
            return '<p class="error-message">Acceso no autorizado. Solo el personal de la tienda puede realizar esta acción.</p>';
        }

        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        $output = '
        <style>
            .qr-verifier-container {
                border: 2px solid #a2be32;
                border-radius: 10px;
                padding: 25px;
                max-width: 480px;
                margin: 20px auto;
                font-family: Arial, sans-serif;
                background-color: #ffffff;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            .qr-verifier-container h3 {
                margin-top: 0;
                margin-bottom: 20px;
                color: #333;
                font-size: 24px;
                text-align: center;
            }
            .qr-verifier-form, .user-details {
                text-align: center;
            }
            .qr-verifier-form input[type="text"], .qr-verifier-form input[type="number"] {
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 5px;
                width: calc(100% - 22px);
                margin-bottom: 15px;
                font-size: 16px;
            }
            .qr-verifier-form .button, .qr-verifier-form button {
                background-color: #a2be32;
                color: #fff;
                border: none;
                padding: 12px 25px;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
            }
            .user-details img {
                width: 150px;
                height: 150px;
                border: 1px solid #eee;
                border-radius: 8px;
                margin: 10px 0;
            }
            .user-details p {
                font-size: 18px;
                color: #555;
                margin: 10px 0;
            }
            .user-details strong {
                color: #000;
            }
            .success-message, .error-message {
                padding: 15px;
                border-radius: 5px;
                margin: 15px 0;
                text-align: center;
            }
            .success-message {
                background-color: #e9f5e9;
                color: #348a34;
                border: 1px solid #c2e5c2;
            }
            .error-message {
                background-color: #fce8e6;
                color: #c5221f;
                border: 1px solid #f7c5c0;
            }
            #discount-form {
                margin-top: 20px;
                border-top: 1px solid #eee;
                padding-top: 20px;
            }
        </style>';
        
        // State 3: Numeric code verification result
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['numeric_code'])) {
            $numeric_code = sanitize_text_field($_POST['numeric_code']);
            $user_query = new WP_User_Query(array('meta_key' => 'unique_discount_code', 'meta_value' => $numeric_code));
            $users = $user_query->get_results();
            if (!empty($users)) {
                $user_id = $users[0]->ID;
                $user_info = get_userdata($user_id);
                $user_name = $user_info ? $user_info->display_name : 'Usuario';
                $output .= '<div class="qr-verifier-container"><div class="success-message">Código encontrado para el cliente: <strong>' . esc_html($user_name) . '</strong>.</div>';
                $output .= '<div style="text-align:center;"><a href="' . esc_url(add_query_arg('user_id', $user_id, get_permalink())) . '" class="qr-verifier-form button">Continuar</a></div></div>';
                return $output;
            } else {
                return $output . '<div class="qr-verifier-container"><div class="error-message">No se encontró ningún cliente con ese código. Por favor, inténtalo de nuevo.</div>
                                 <form method="post" class="qr-verifier-form">
                                     <input type="text" name="numeric_code" placeholder="Introduce el código numérico" required>
                                     <button type="submit">Verificar Código</button>
                                 </form></div>';
            }
        }

        // State 2: Numeric code input form
        if (!$user_id) {
            return $output . '<div class="qr-verifier-container">
                                <h3>Verificar Código de Cliente</h3>
                                <form method="post" class="qr-verifier-form">
                                    <input type="text" name="numeric_code" placeholder="Introduce el código numérico" required>
                                    <button type="submit">Verificar Código</button>
                                </form>
                             </div>';
        }

        // State 4: User details and discount form
        $qr_code_url = get_user_meta($user_id, 'qr_code_url', true);
        $qr_code_used = get_user_meta($user_id, 'qr_code_used', true);
        $unique_discount_code = get_user_meta($user_id, 'unique_discount_code', true);
        $remaining_discount = get_user_meta($user_id, 'remaining_discount', true);
        $user_info = get_userdata($user_id);
        $user_name = $user_info ? $user_info->display_name : 'Unknown User';

        $output .= '<div class="qr-verifier-container">';
        $output .= '<h3>Detalles del Cliente</h3>';
        $output .= '<div class="user-details">';
        $output .= '<p><strong>Nombre:</strong> ' . esc_html($user_name) . '</p>';
        $output .= '<img src="' . esc_url($qr_code_url) . '" alt="QR Code"><br>';
        $output .= '<p><strong>Código:</strong> ' . esc_html($unique_discount_code) . '</p>';
        $output .= '<p id="remainingDiscount"><strong>Saldo restante:</strong> ' . esc_html($remaining_discount) . ' €</p>';
        $output .= '</div>';

        if ($qr_code_used) {
            $output .= '<div class="error-message">Este código de descuento ya se ha agotado.</div></div>';
            return $output;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_discount'])) {
            $amount_used = isset($_POST['amount_used']) ? floatval($_POST['amount_used']) : 0;
            $total_amount = isset($_POST['totalAmount']) ? floatval($_POST['totalAmount']) : 0;
            $charged_amount = isset($_POST['chargedAmount']) ? floatval($_POST['chargedAmount']) : 0;

            if ($total_amount < 0) {
                 $_SESSION['flash_message'] = 'Error: La cantidad total no puede ser negativa.';
            } elseif ($amount_used > $remaining_discount) {
                $_SESSION['flash_message'] = 'La cantidad ingresada excede el límite del descuento.';
            } else {
                $new_amount = $remaining_discount - $amount_used;
                update_user_meta($user_id, 'remaining_discount', $new_amount);
                if ($new_amount <= 0) {
                    update_user_meta($user_id, 'qr_code_used', 'yes');
                }
                $_SESSION['flash_message'] = 'Descuento actualizado. Nuevo saldo: €' . number_format($new_amount, 2);

                $db_handler = new DB_Handler();
                $db_handler->log_transaction([
                    'client_user_id' => $user_id,
                    'client_user_name' => $user_info->display_name,
                    'client_user_email' => $user_info->user_email,
                    'numeric_discount_code' => $unique_discount_code,
                    'qr_code_url' => $qr_code_url,
                    'total_amount' => $total_amount,
                    'discount_applied' => $amount_used,
                    'amount_charged' => $charged_amount
                ]);

                $redirect_url = add_query_arg('user_id', $user_id, get_permalink());
                echo '<script>window.location.href = "' . esc_js($redirect_url) . '";</script>';
                exit;
            }
        }
        
        // Note: This form seems to be controlled by external JS. I'm styling it but keeping it hidden.
        $output .= '<form method="post" id="discount-form" class="qr-verifier-form" style="display:none;">
                        <input type="number" name="amount_used" step="0.01" min="0.01" max="' . esc_attr($remaining_discount) . '" placeholder="Valor a descontar" required>
                        <input type="hidden" name="user_id" value="' . esc_attr($user_id) . '">
                        <button type="submit" name="update_discount">Aplicar Descuento</button>
                    </form>';
        
        $output .= '</div>';
        return $output;
    }
}

new QR_Verify();

?>