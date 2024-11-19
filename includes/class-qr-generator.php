<?php
require_once __DIR__ . '../vendor/autoload.php'; // Adjust the path as needed to where your Composer autoload file is located

use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;

class QR_Generator {
    /**
     * Generates a QR code and returns the URL to the QR code image.
     *
     * @param int $user_id User ID for whom the QR code is generated.
     * @return string URL of the generated QR code image.
     */
    public function generate_qr_code($user_id) {
        $qrCode = new QrCode('https://bonocomerciotoledo.es/user/'.$user_id);
        $qrCode->setSize(300);
        $qrCode->setEncoding(new Encoding('UTF-8'));
        $qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::HIGH));
        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0]);
        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255]);

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
?>