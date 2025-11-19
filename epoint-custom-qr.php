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
 * Text Domain:       epoint-custom-qr
 * Domain Path:       /languages
 */

defined('ABSPATH') or die('No script kiddies please!');

function epoint_custom_qr_activate()
{
    flush_rewrite_rules();
    require_once plugin_dir_path(__FILE__) . 'includes/class-db-handler.php';
    $db_handler = new DB_Handler();
    $db_handler->setup_database_table();
}

register_activation_hook(__FILE__, 'epoint_custom_qr_activate');

function epoint_custom_qr_deactivate()
{
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'epoint_custom_qr_deactivate');


function epoint_custom_qr_includes()
{
    include_once('includes/class-qr-generator.php');
    include_once('includes/class-mailer.php');
    include_once('includes/class-qr-verifier.php');
    include_once('includes/class-db-handler.php');
}

add_action('plugins_loaded', 'epoint_custom_qr_includes');


add_action('wp_ajax_generate_qr_code', 'handle_qr_code_generation');

function handle_qr_code_generation() {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    if (!$user_id) {
        wp_send_json_error('Invalid user ID.');
        wp_die();
    }

    $qr_generator = new QR_Generator();
    $qr_code_url = $qr_generator->generate_qr_code($user_id);

    if (!$qr_code_url) {
        wp_send_json_error('Failed to generate QR code.');
        wp_die();
    }

    $qr_mailer = new QR_Mailer();
    $qr_mailer->send_qr_code($user_id, $qr_code_url);
    update_user_meta($user_id, 'qr_code_url', $qr_code_url);

    wp_send_json_success('QR Code generated successfully.');
    wp_die();
}


function epoint_custom_add_user_endpoint()
{
    add_rewrite_tag('%user_id%', '([^&]+)');
    add_rewrite_rule('^verify-qr/?user_id=([0-9]+)$', 'index.php?user_id=$matches[1]', 'top');

}

add_action('init', 'epoint_custom_add_user_endpoint');

function epoint_custom_query_vars($vars)
{
    $vars[] = 'user_id';
    return $vars;
}

add_filter('query_vars', 'epoint_custom_query_vars');

function epoint_custom_template_redirect()
{
    $user_id = get_query_var('user_id');
    if ($user_id && !current_user_can('verify_qr')) {
        wp_redirect(home_url());
        exit;
    }
}

add_action('template_redirect', 'epoint_custom_template_redirect');


// Template redirections
function epoint_custom_template_include($template)
{
    if (is_page('transaction-history')) {
        // Check for the business transactions page template
        $theme_template = locate_template('epoint-business-transactions.php');
        if ($theme_template) {
            return $theme_template;
        } else {
            $plugin_template = plugin_dir_path(__FILE__) . 'templates/epoint-business-transactions.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
    } elseif (is_page('central-panel')) {
        // Check for the central panel page template
        $theme_template = locate_template('epoint-central-panel.php');
        if ($theme_template) {
            return $theme_template;
        } else {
            $plugin_template = plugin_dir_path(__FILE__) . 'templates/epoint-central-panel.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
    }
    return $template;
}

add_filter('template_include', 'epoint_custom_template_include', 99);

// custom action for the pagination in the transaction-history page:

add_action('wp_ajax_filter_transactions', 'filter_transactions_function');
function filter_transactions_function()
{
    global $wpdb;
    $from_date = sanitize_text_field($_POST['from_date']);
    $to_date = sanitize_text_field($_POST['to_date']);

    $query = $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}epoint_qr_transactions 
         WHERE verifier_user_id = %d AND transaction_date BETWEEN %s AND %s ORDER BY transaction_date DESC",
        get_current_user_id(), $from_date, $to_date
    );

    $transactions = $wpdb->get_results($query);

    if (!empty($transactions)) {
        echo '<tr><th>ID de la transacción</th><th>Nombre</th><th>Cantidad inicial (€)</th><th>Descuento Aplicado (€)</th><th>Total cobrado (€)</th><th>Fecha y hora</th></tr>';
        $totalAmount = 0;
        $totalDiscount = 0;
        $totalCharged = 0;

        foreach ($transactions as $transaction) {
            $totalAmount += $transaction->total_amount;
            $totalDiscount += $transaction->discount_applied;
            $totalCharged += $transaction->amount_charged;

            echo '<tr>';
            echo '<td>' . esc_html($transaction->transaction_id) . '</td>';
            echo '<td>' . esc_html($transaction->client_user_name) . '</td>';
            echo '<td>' . esc_html(number_format($transaction->total_amount, 2)) . '</td>';
            echo '<td>' . esc_html(number_format($transaction->discount_applied, 2)) . '</td>';
            echo '<td>' . esc_html(number_format($transaction->amount_charged, 2)) . '</td>';
            echo '<td>' . esc_html($transaction->transaction_date) . '</td>';
            echo '</tr>';
        }

        // Append a totals row
        echo '<tr style="font-weight:bold; background-color:#e9ecef;">';
        echo '<td colspan="2">Totales:</td>';
        echo '<td>' . esc_html(number_format($totalAmount, 2)) . ' €</td>';
        echo '<td>' . esc_html(number_format($totalDiscount, 2)) . ' €</td>';
        echo '<td>' . esc_html(number_format($totalCharged, 2)) . ' €</td>';
        echo '<td></td>'; // Empty cell for the date column
        echo '</tr>';

    } else {
        echo '<tr><td colspan="6">No existen transacciones entre estas fechas.</td></tr>';
    }
    wp_die();
}

// Handler for coupons
add_action('wp_ajax_fetch_coupons', 'fetch_coupons_function');
function fetch_coupons_function()
{
    global $wpdb;
    $from_date = sanitize_text_field($_POST['from_date']);
    $to_date = sanitize_text_field($_POST['to_date']);

    // Ensure dates are not empty
    if (empty($from_date) || empty($to_date)) {
        echo "Please provide both start and end dates.";
        wp_die();
    }

    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM rgsn_epoint_qr_codes WHERE creation_date BETWEEN %s AND %s ORDER BY creation_date DESC", $from_date, $to_date));

    if (!empty($results)) {
        echo '<table>';
        echo '<tr><th>ID del usuario</th><th>Email</th><th>Nombre</th><th>Imagen del QR</th><th>Código del descuento</th><th>Fecha de creación</th></tr>';
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->user_id) . '</td>';
            echo '<td>' . esc_html($row->user_email) . '</td>';
            echo '<td>' . esc_html($row->display_name) . '</td>';
            echo '<td><a href="' . esc_url($row->qr_code_url) . '" target="_blank">Ver QR</a></td>';
            echo '<td>' . esc_html($row->unique_discount_code) . '</td>';
            echo '<td>' . esc_html($row->creation_date) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p>No coupons found within the specified date range.</p>';
    }
    wp_die();
}

// Handler for transactions
add_action('wp_ajax_fetch_transactions', 'fetch_transactions_function');
function fetch_transactions_function()
{
    global $wpdb;
    $from_date = sanitize_text_field($_POST['from_date']);
    $to_date = sanitize_text_field($_POST['to_date']);
    $verifier_name = sanitize_text_field($_POST['verifier_name']);

    if (empty($from_date) || empty($to_date)) {
        echo "Please provide both start and end dates.";
        wp_die();
    }

    $query = "SELECT * FROM rgsn_epoint_qr_transactions 
              WHERE transaction_date BETWEEN %s AND %s";
    $params = array($from_date, $to_date);

    // Adding verifier name to the query if provided
    if (!empty($verifier_name)) {
        $query .= " AND verifier_user_name LIKE %s";
        $params[] = '%' . $wpdb->esc_like($verifier_name) . '%'; // Adds wildcard search to the verifier name
    }

    $query .= " ORDER BY transaction_date DESC";
    $results = $wpdb->get_results($wpdb->prepare($query, $params));

    if (!empty($results)) {
        $totalAmount = 0;
        $totalDiscount = 0;
        $totalCharged = 0;

        echo '<table>';
        echo '<tr><th>ID de transacción</th><th>ID del comercio verificador</th><th>Nombre del comercio</th><th>ID de usuario cliente</th><th>Nombre del cliente</th><th>Email del cliente</th><th>Código de descuento numérico</th><th>URL del código QR</th><th>Monto total (€)</th><th>Descuento aplicado (€)</th><th>Monto cobrado (€)</th><th>Fecha de transacción</th></tr>';
        foreach ($results as $transaction) {
            $totalAmount += $transaction->total_amount;
            $totalDiscount += $transaction->discount_applied;
            $totalCharged += $transaction->amount_charged;

            echo '<tr>';
            echo '<td>' . esc_html($transaction->transaction_id) . '</td>';
            echo '<td>' . esc_html($transaction->verifier_user_id) . '</td>';
            echo '<td>' . esc_html($transaction->verifier_user_name) . '</td>';
            echo '<td>' . esc_html($transaction->client_user_id) . '</td>';
            echo '<td>' . esc_html($transaction->client_user_name) . '</td>';
            echo '<td>' . esc_html($transaction->client_user_email) . '</td>';
            echo '<td>' . esc_html($transaction->numeric_discount_code) . '</td>';
            echo '<td><a href="' . esc_url($transaction->qr_code_url) . '" target="_blank">Ver QR</a></td>';
            echo '<td>' . esc_html(number_format($transaction->total_amount, 2)) . '</td>';
            echo '<td>' . esc_html(number_format($transaction->discount_applied, 2)) . '</td>';
            echo '<td>' . esc_html(number_format($transaction->amount_charged, 2)) . '</td>';
            echo '<td>' . esc_html($transaction->transaction_date) . '</td>';
            echo '</tr>';
        }
        // Displaying totals
        echo '<tr style="font-weight: bold;">';
        echo '<td colspan="8">Total</td>';
        echo '<td>' . esc_html(number_format($totalAmount, 2)) . ' €</td>';
        echo '<td>' . esc_html(number_format($totalDiscount, 2)) . ' €</td>';
        echo '<td>' . esc_html(number_format($totalCharged, 2)) . ' €</td>';
        echo '<td></td>'; // empty cell for the date column
        echo '</tr>';

        echo '</table>';
    } else {
        echo '<p>No transactions found within the specified date range.</p>';
    }
    wp_die();
}



?>

