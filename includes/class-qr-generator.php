<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Adjust the path as needed to where your Composer autoload file is located

use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;

class QR_Generator {
    public function generate_qr_code($user_id) {

        global $wpdb;

        // Check the current count of generated QR codes
        // $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}epoint_qr_codes");
        // if ($count >= 1500) {
        //     return false;
        // }

        $initial_discount = 20; // Set the initial discount amount
        update_user_meta($user_id, 'remaining_discount', $initial_discount); // Save the discount amount

        // Generación del código numérico
        $unique_code = strtoupper(wp_generate_password(6, false, false)); // Generates a 6-character alphanumeric string
        update_user_meta($user_id, 'unique_discount_code', $unique_code); // Store the code

        // Generación del código QR
        $qrCode = new QrCode(get_home_url() . '/verify-qr/?user_id=' . $user_id);
        $qrCode->setSize(300);
        $qrCode->setEncoding(new Encoding('UTF-8'));
        $qrCode->setForegroundColor(new Color(0, 0, 0));
        $qrCode->setBackgroundColor(new Color(255, 255, 255));

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Se sube a la carpeta qr-codes de WP
        $upload_dir = wp_upload_dir();
        $file_path = 'qr-codes/user-' . $user_id . '.png';
        $full_path = $upload_dir['basedir'] . '/' . $file_path;
        $result->saveToFile($full_path);


        //db-handling
        $user_info = get_userdata($user_id);
        if (!$user_info) {
            return false; // Exit if user data isn't found
        }
        $user_email = $user_info->user_email;
        $display_name = $user_info->display_name;

        $qr_code_url = $upload_dir['baseurl'] . '/' . $file_path;
        update_user_meta($user_id, 'qr_code_url', $qr_code_url);
        
        $db_handler = new DB_Handler();
        $db_handler->insert_qr_code_data($user_id, $qr_code_url, $unique_code, $user_email, $display_name);

        return $qr_code_url;
    }
}

function epoint_custom_qr_shortcode()
{
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $qr_code_url = get_user_meta($user_id, 'qr_code_url', true);
        $remaining_discount = get_user_meta($user_id, 'remaining_discount', true);
        $unique_discount_code = get_user_meta($user_id, 'unique_discount_code', true);

        $output = '';

        if (!empty($qr_code_url)) {
            $unique_code_display = $unique_discount_code ? esc_html($unique_discount_code) : 'N/A';
            $remaining_discount_display = $remaining_discount ? esc_html($remaining_discount) : '0';

            $output = '
            <style>
                .qr-code-container {
                    border: 2px solid #a2be32;
                    border-radius: 10px;
                    padding: 25px;
                    max-width: 360px;
                    margin: 20px auto;
                    text-align: center;
                    font-family: Arial, sans-serif;
                    background-color: #ffffff;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                }
                .qr-code-container h3 {
                    margin-top: 0;
                    margin-bottom: 15px;
                    color: #333;
                    font-size: 22px;
                }
                .qr-code-container img {
                    max-width: 100%;
                    height: auto;
                    border: 1px solid #eee;
                    border-radius: 8px;
                    margin-bottom: 20px;
                }
                .qr-code-details p {
                    margin: 8px 0;
                    font-size: 18px;
                    color: #555;
                }
                .qr-code-details strong {
                    color: #000;
                }
            </style>
            <div class="qr-code-container">
                <h3>Tu Código de Descuento</h3>
                <img src="' . esc_url($qr_code_url) . '" alt="Tu Código QR de Descuento">
                <div class="qr-code-details">
                    <p><strong>Código numérico:</strong> ' . $unique_code_display . '</p>
                    <p><strong>Saldo restante:</strong> €' . $remaining_discount_display . '</p>
                </div>
            </div>';
        } else {
            // $output = 'Los bonos de descuento están reservados exclusivamente para los residentes de Toledo.<br>' .
            // 'Si eres residente y tu código no aparece, es porque ya se ha alcanzado el límite de 1500 bonos emitidos en esta campaña.<br>' .
            // 'Para cualquier inconveniente o asistencia, por favor, contacta con nuestro soporte técnico.';
            $output = 'Aún no tienes un código QR. Haz clic en el botón para generar uno.';
        }
        return $output;
    } else {
        return 'Tienes que iniciar sesión para ver tu código de descuento';
    }
}


add_shortcode('display_qr_code', 'epoint_custom_qr_shortcode');

// function epoint_custom_qr_shortcode_counter() {
//     global $wpdb;
//     $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}epoint_qr_codes");
//
//     if ($count >= 1500) {
//         return 'Todos los 1500 códigos QR para la campaña han sido generados. No se pueden generar más.';
//     } else {
//         return 'Aún hay ' . (1500 - $count) . ' bonos de descuento disponibles para la campaña actual.';
//     }
// }
//
// add_shortcode('qr_code_availability', 'epoint_custom_qr_shortcode_counter');

function epoint_custom_generate_qr_shortcode() {
    if (!is_user_logged_in()) {
        return 'You must be logged in to generate a discount QR code.';
    }

    $user_id = get_current_user_id();
    $qr_code_url = get_user_meta($user_id, 'qr_code_url', true);
    $postal_code = get_user_meta($user_id, 'Código Postal', true);

    if (!empty($qr_code_url)) {
        return '';
    }

    // if ($postal_code < 45000 || $postal_code > 45009) {
    //     return 'Lamentablemente no cumples los requisitos.';
    // }

    // Display a button that triggers QR code generation via AJAX
    $output = '<button id="generateQrCode" style="background-color: #a2be32; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; font-size: 16px; cursor: pointer;">Generar QR</button>';
    $output .= '<script>
        jQuery("#generateQrCode").click(function() {
            jQuery.ajax({
                url: "'. admin_url('admin-ajax.php') .'",
                type: "POST",
                data: {
                    action: "generate_qr_code",
                    user_id: '. $user_id .'
                },
                success: function(response) {
                    alert("¡Cupón generado exitosamente!");
                    location.reload(); // Reload the page to update the status
                },
                error: function() {
                    alert("Ha ocurrido un error, contacte con soporte");
                }
            });
        });
    </script>';

    return $output;
}

add_shortcode('generate_qr_code_button', 'epoint_custom_generate_qr_shortcode');

?>
