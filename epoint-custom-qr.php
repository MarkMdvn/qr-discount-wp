<?php
/*
 * Plugin Name:       Código QR Único para Descuentos
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Código QR Único para Descuentos es un plugin de WordPress y WooCommerce diseñado para generar códigos QR únicos para cada usuario al registrarse. Estos códigos QR sirven para aplicar descuentos únicos en tiendas físicas. Una vez que el usuario se registra, recibe un código QR por correo electrónico, el cual también puede visualizar en su área de cuenta dentro del sitio web. El personal de la tienda puede escanear el código QR para verificar su autenticidad y marcarlo como usado, asegurando que cada QR solo se utilice una vez. 
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Mark Mordvin
 * Author URI:        https://markmd.netlify.app/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       epoint-custom-qr
 * Domain Path:       /languages

*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Plugin Activation
function epoint_custom_qr_activate() {
// Perform any setups here, such as setting up default options
}
register_activation_hook( __FILE__, 'epoint_custom_qr_activate' );

// Plugin Deactivation
function epoint_custom_qr_deactivate() {
// Clean up data, if necessary, like removing custom roles or capabilities
}
register_deactivation_hook( __FILE__, 'epoint_custom_qr_deactivate' );

// Include necessary files
function epoint_custom_qr_includes() {
include_once('includes/class-qr-generator.php');
include_once('includes/class-mailer.php');
include_once('includes/class-qr-verifier.php');
include_once('includes/class-qr-frontend.php');
include_once('includes/class-db-handler.php');
}
add_action('plugins_loaded', 'epoint_custom_qr_includes');

// Hook into user registration to generate QR code
function epoint_custom_qr_user_register($user_id) {
    $qr_generator = new QR_Generator();
    $qr_code_url = $qr_generator->generate_qr_code($user_id);

    $qr_mailer = new QR_Mailer();
    $qr_mailer->send_qr_code($user_id, $qr_code_url);
}
add_action('user_register', 'epoint_custom_qr_user_register');
?>