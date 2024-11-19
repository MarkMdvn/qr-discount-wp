<?php
class QR_Mailer {
    /**
     * Sends an email to the user with the QR code.
     *
     * @param int $user_id User ID of the recipient.
     * @param string $qr_code_url URL of the QR code image.
     */
    public function send_qr_code($user_id, $qr_code_url) {
        $user_info = get_userdata($user_id);
        $to = $user_info->user_email;
        $subject = 'Your Exclusive Discount QR Code';
        $message = "Hello " . $user_info->first_name . ",\n\n";
        $message .= "Thank you for registering. Here is your personal discount QR code:\n\n";
        $message .= "<img src='" . esc_url($qr_code_url) . "' alt='Discount QR Code'>\n\n";
        $message .= "Please show this code at our store to receive your discount.";

        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($to, $subject, $message, $headers);
    }
}
?>