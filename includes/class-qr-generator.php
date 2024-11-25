<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Adjust the path as needed to where your Composer autoload file is located

use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;


class QR_Generator {
    public function generate_qr_code($user_id) {
        $initial_discount = 10; // Set the initial discount amount
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


        $qr_code_url = $upload_dir['baseurl'] . '/' . $file_path;
        update_user_meta($user_id, 'qr_code_url', $qr_code_url);
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
            $output .= '<img src="' . esc_url($qr_code_url) . '" alt="Your QR Code"><br>';
            $output .= 'Código numérico: ' . ($unique_discount_code ? esc_html($unique_discount_code) : '0') . '<br>';  // Display the unique discount code or 0 if not set
            $output .= 'Cantidad restante: €' . ($remaining_discount ? esc_html($remaining_discount) : '0') . '<br>'; // Display 0 if not set
        } else {
            $output = 'No se ha encontrado ningún código QR. Contacte con soporte por favor.';
        }
        return $output;
    } else {
        return 'Tienes que iniciar sesión para ver tu código de descuento';
    }
}


add_shortcode('display_qr_code', 'epoint_custom_qr_shortcode');

?>
