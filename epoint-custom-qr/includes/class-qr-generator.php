<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Adjust the path as needed to where your Composer autoload file is located

use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color; // Import the Color class

class QR_Generator {
    /**
     * Generates a QR code and returns the URL to the QR code image.
     *
     * @param int $user_id User ID for whom the QR code is generated.
     * @return string URL of the generated QR code image.
     */
    public function generate_qr_code($user_id) {

        $initial_discount = 10; // Set the initial discount amount to €10
        update_user_meta($user_id, 'remaining_discount', $initial_discount); // Save the discount amount

        $qrCode = new QrCode(get_home_url() . '/verify-qr/?user_id=' . $user_id);
        $qrCode->setSize(300);
        $qrCode->setEncoding(new Encoding('UTF-8'));

        // Use the Color class for setting foreground and background colors
        $qrCode->setForegroundColor(new Color(0, 0, 0)); // Black foreground
        $qrCode->setBackgroundColor(new Color(255, 255, 255)); // White background

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Path within the WordPress uploads directory
        $upload_dir = wp_upload_dir(); // Get WordPress upload directory paths and URL
        $file_path = 'qr-codes/user-' . $user_id . '.png';
        $full_path = $upload_dir['basedir'] . '/' . $file_path;
        $result->saveToFile($full_path); // Save the QR code image

        // URL accessible to users
        $qr_code_url = $upload_dir['baseurl'] . '/' . $file_path;

        return $qr_code_url;
    }


}

function epoint_custom_qr_shortcode() {
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $qr_code_url = get_user_meta($user_id, 'qr_code_url', true);
        $remaining_discount = get_user_meta($user_id, 'remaining_discount', true); // Make sure this key matches what's used in saving

        $output = '';
        if (!empty($qr_code_url)) {
            $output .= '<img src="' . esc_url($qr_code_url) . '" alt="Your QR Code"><br>';
            $output .= 'Cantidad restante: €' . ($remaining_discount ? esc_html($remaining_discount) : '0'); // Display 0 if not set
        } else {
            $output = 'No QR code found. Please contact support.';
        }
        return $output;
    } else {
        return 'You need to be logged in to view your QR code.';
    }
}
add_shortcode('display_qr_code', 'epoint_custom_qr_shortcode');

?>
