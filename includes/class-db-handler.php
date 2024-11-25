<?php
class DB_Handler {
    /**
     * Updates the status of a user's QR code to 'used'.
     *
     * @param int $user_id User's ID.
     */
    public function mark_qr_code_as_used($user_id) {
        update_user_meta($user_id, 'qr_code_used', 'yes');
    }

    /**
     * Retrieves the usage status of a user's QR code.
     *
     * @param int $user_id User's ID.
     * @return string Status of the QR code ('yes' if used, empty string if not used).
     */
    public function get_qr_code_status($user_id) {
        return get_user_meta($user_id, 'qr_code_used', true);
    }

    /**
     * Updates the remaining discount amount for a user's QR code.
     *
     * @param int $user_id User's ID.
     * @param float $amount Remaining discount amount.
     */
    public function update_remaining_discount($user_id, $amount) {
        update_user_meta($user_id, 'remaining_discount', $amount);
    }

    /**
     * Retrieves the remaining discount amount of a user's QR code.
     *
     * @param int $user_id User's ID.
     * @return float Remaining discount amount.
     */
    public function get_remaining_discount($user_id) {
        return get_user_meta($user_id, 'remaining_discount', true);
    }


    // Creating the table for the transactions and inserting the data in it
     public function insert_qr_code_data($user_id, $qr_code_url, $unique_code, $user_email, $display_name) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'epoint_qr_codes';

        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'user_email' => $user_email,
                'display_name' => $display_name,
                'qr_code_url' => $qr_code_url,
                'unique_discount_code' => $unique_code,
                'creation_date' => current_time('mysql', true) // Using GMT time
            ),
            array(
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );
    }

    // database managment for the code transactions

    public function log_transaction($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'epoint_qr_transactions';

        // Get the current user's information
        $current_user = wp_get_current_user();
        $verifier_user_name = $current_user->display_name; 

        $wpdb->insert(
            $table_name,
            array(
                'verifier_user_id' => get_current_user_id(),
                'verifier_user_name' => $verifier_user_name,
                'client_user_id' => $data['client_user_id'],
                'client_user_name' => $data['client_user_name'],
                'client_user_email' => $data['client_user_email'],
                'numeric_discount_code' => $data['numeric_discount_code'],
                'qr_code_url' => $data['qr_code_url'],
                'total_amount' => $data['total_amount'],
                'discount_applied' => $data['discount_applied'],
                'amount_charged' => $data['amount_charged'],
                'transaction_date' => current_time('mysql', 1)
            ),
            array(
                '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%s'
            )
        );
    }
}
?>