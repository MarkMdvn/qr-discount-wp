<?php
require_once('../../../../wp-load.php'); // Adjust the path to the wp-load.php file as needed to ensure proper WordPress environment loading

if (!current_user_can('manage_options')) { // Ensure that only users who can manage options (i.e., store managers) can access this page
    wp_die('Unauthorized user');
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$qr_code_used = get_user_meta($user_id, 'qr_code_used', true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_used'])) {
    if (!$qr_code_used) {
        update_user_meta($user_id, 'qr_code_used', 'yes');
        $qr_code_used = 'yes';
        $message = 'QR code marked as used.';
    } else {
        $message = 'QR code was already used.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify QR Code</title>
</head>
<body>
<h1>Verify QR Code</h1>
<?php if (isset($message)): ?>
    <p><?php echo $message; ?></p>
<?php endif; ?>
<p>Status: <?php echo $qr_code_used ? 'Used' : 'Not Used'; ?></p>
<?php if (!$qr_code_used): ?>
    <form method="post">
        <button type="submit" name="mark_used">Mark as Used</button>
    </form>
<?php endif; ?>
</body>
</html>