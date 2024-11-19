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
}
?>