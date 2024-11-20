<?php
/*
 * Plugin Name:       Códigos QR promocionales para Descuentos - epoint
 * Plugin URI:        https://www.epoint.es/
 * Description:       Este plugin de WordPress y WooCommerce permite generar códigos QR únicos para cada usuario registrado. Estos códigos QR se utilizan para aplicar descuentos exclusivos en tiendas físicas. Al registrarse, el usuario recibe un código QR por correo electrónico, que también puede visualizar en su área de cuenta en el sitio web. Los empleados de la tienda pueden escanear el código QR para verificar su autenticidad y aplicar el descuento correspondiente, asegurando que cada código solo se utilice una vez.
 * Version:           1.1.0
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

defined('ABSPATH') or die('No script kiddies please!');

function epoint_custom_qr_activate() {
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'epoint_custom_qr_activate');

function epoint_custom_qr_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'epoint_custom_qr_deactivate');

function epoint_custom_qr_includes() {
    include_once('includes/class-qr-generator.php');
    include_once('includes/class-mailer.php');
    include_once('includes/class-qr-verifier.php');
    include_once('includes/class-db-handler.php');
}
add_action('plugins_loaded', 'epoint_custom_qr_includes');

function epoint_custom_qr_user_register($user_id) {
    $qr_generator = new QR_Generator();
    $qr_code_url = $qr_generator->generate_qr_code($user_id);

    $qr_mailer = new QR_Mailer();
    $qr_mailer->send_qr_code($user_id, $qr_code_url);
    update_user_meta($user_id, 'qr_code_url', $qr_code_url);
}
add_action('user_register', 'epoint_custom_qr_user_register');

function epoint_custom_add_user_endpoint() {
    add_rewrite_tag('%user_id%', '([^&]+)');
    add_rewrite_rule('^verify-qr/?user_id=([0-9]+)$', 'index.php?user_id=$matches[1]', 'top');

}
add_action('init', 'epoint_custom_add_user_endpoint');

function epoint_custom_query_vars($vars) {
    $vars[] = 'user_id';
    return $vars;
}
add_filter('query_vars', 'epoint_custom_query_vars');

function epoint_custom_template_redirect() {
    $user_id = get_query_var('user_id');
    if ($user_id && !current_user_can('verify_qr')) {
        wp_redirect(home_url());
        exit;
    }
}
add_action('template_redirect', 'epoint_custom_template_redirect');
?>

