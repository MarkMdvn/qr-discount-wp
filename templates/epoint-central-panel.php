<?php
/*
Template Name: Panel Central - transacciones
*/
get_header();

if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

global $wpdb;
?>
<style>
    /* Overall body styling */
    body {
        color: #333; /* Dark grey text for better readability */
    }

    /* Style adjustments for the form */
    form#date-filter {
        background-color: #ffffff; /* White background for the form area */
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for a pop-out effect */
        display: flex;
        height: auto;
        gap: 10px; /* Spacing between form elements */
        max-width: 80%; /* Limiting form width */
        margin: 20px auto; /* Centering form */
    }

    label {
        margin-bottom: 5px;
        font-weight: bold;
        margin-top: 15px;
    }

    input[type="date"] {
        padding: 5px;
        border-radius: 5px;
        border: 1px solid #ccc; /* Subtle border for inputs */
    }

    button {
        background-color: rgb(22, 28, 38); /* A pleasant shade of blue for the button */
        color: #ffffff;
        padding: 5px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer; /* Pointer cursor on hover for better user interaction feedback */
        transition: background-color 0.3s;
    }

    button:hover {
        background-color: rgba(0, 54, 124, 0.91); /* Darken button on hover for interactive feedback */
    }

    /* Table styling for improved readability */
    table {
        width: 80%;
        margin: 20px auto;
        border-collapse: collapse;
        background-color: #fff; /* White background for the table */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Consistent with form shadow */
    }

    th, td {
        border: 1px solid #1f1f1f;
        padding: 12px 15px; /* Slightly larger padding for a more spacious layout */
        text-align: left;
    }

    th {
        background-color: #76b1ff; /* Blue headers for a modern look */
        color: #ffffff; /* White text for contrast */
    }

    tr:nth-child(even) {
        background-color: #f2f2f2; /* Zebra striping for rows */
    }

    h1 {
        margin-left: 10%;
        color: #333;
        margin-top: 30px; /* Adding more space above the title */
    }
</style>

<div class="button-container" style="margin: 20px;">
    <form id="date-filter" style="margin-bottom: 20px;">
        <label for="from-date">Desde:</label>
        <input type="date" id="from-date" name="from_date" required>
        <label for="to-date">Hasta:</label>
        <input type="date" id="to-date" name="to_date" required>
        <button type="button" id="showTable1" onclick="updateTable('fetch_coupons')">Mostrar todos los cupones creados</button>
        <button type="button" id="showTable2" onclick="updateTable('fetch_transactions')">Mostrar todas las transacciones realizadas</button>
    </form>
</div>

<div id="table1" style="display:none;">
    <?php
    $results = $wpdb->get_results("SELECT user_id, user_email, display_name, qr_code_url, unique_discount_code, creation_date FROM rgsn_epoint_qr_codes", OBJECT);
    if (!empty($results)) {
        echo '<table></table>';
    } else {
        echo '<p>No data found.</p>';
    }
    ?>
</div>

<div id="table2" style="display:none;">
    <?php
    $transactionQuery = "SELECT transaction_id, verifier_user_id, verifier_user_name, client_user_id, client_user_name, client_user_email, numeric_discount_code, qr_code_url, total_amount, discount_applied, amount_charged, transaction_date FROM rgsn_epoint_qr_transactions";
    $transactions = $wpdb->get_results($transactionQuery, OBJECT);

    if (!empty($transactions)) {
        echo '<table></table>';
    } else {
        echo '<p>No transactions found.</p>';
    }
    ?>
</div>


<script>
    function updateTable(actionType) {
        var fromDate = document.getElementById('from-date').value;
        var toDate = document.getElementById('to-date').value;

        // Determine which table to update based on actionType
        var tableId = actionType === 'fetch_coupons' ? 'table1' : 'table2';

        jQuery.ajax({
            url: "<?php echo admin_url('admin-ajax.php'); ?>",
            type: "POST",
            data: {
                action: actionType,
                from_date: fromDate,
                to_date: toDate
            },
            success: function(response) {
                document.getElementById(tableId).innerHTML = response;
                document.getElementById(tableId).style.display = 'block';
                document.getElementById(tableId === 'table1' ? 'table2' : 'table1').style.display = 'none';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('date-filter').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission
        });
    });
</script>
