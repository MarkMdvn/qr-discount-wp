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
        $subject = '¡Código QR de descuento!';

        // Create the HTML email content
        $message = "<html><body>";
        $message .= "<p>Buenos días " . esc_html($user_info->first_name) . ",</p>";
        $message .= "<p>Gracias por registrarte en bonocomerciotoledo.es. Aquí tienes tu código QR:</p>";
        $message .= "<p><img src='" . esc_url($qr_code_url) . "' alt='Discount QR Code'></p>";
        $message .= "<p>Puedes enseñar este código en cualquier comercio vinculado y... ¡obtener un descuento de 10€!</p>";
        $message .= "</body></html>";

        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($to, $subject, $message, $headers);
    }
}
?>