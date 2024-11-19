<?php
class QR_Mailer {
    public function send_qr_code($user_id, $qr_code_url) {
        $user_info = get_userdata($user_id);
        $unique_code = get_user_meta($user_id, 'unique_discount_code', true); // Retrieve the unique code
        $to = $user_info->user_email;
        $subject = '¡Código QR de descuento!';

        $message = "<html><body>";
        $message .= "<p>Buenos días " . esc_html($user_info->first_name) . ",</p>";
        $message .= "<p>Gracias por registrarte. Aquí tienes tu código QR y tu código único de descuento: $unique_code</p>";
        $message .= "<p><img src='" . esc_url($qr_code_url) . "' alt='Discount QR Code'></p>";
        $message .= "<p>Puedes enseñar este código en cualquier comercio vinculado para obtener un descuento.</p>";
        $message .= "</body></html>";

        wp_mail($to, $subject, $message, array('Content-Type: text/html; charset=UTF-8'));
    }
}
?>